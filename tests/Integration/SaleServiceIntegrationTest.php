<?php

namespace Tests\Integration;

use App\Services\SaleService;
use CodeIgniter\Test\CIUnitTestCase;
use RuntimeException;

class SaleServiceIntegrationTest extends CIUnitTestCase
{
    private int $productId = 0;
    private int $userId = 0;
    private int $customerId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        if (! getenv('DB_TEST_DATABASE')) {
            $this->markTestSkipped('Set DB_TEST_DATABASE to a dedicated migrated MariaDB/MySQL test database.');
        }
        $this->db = db_connect('tests', false);
        if (! $this->db->tableExists('sales')) {
            $this->markTestSkipped('Run the POS migrations against the dedicated test database first.');
        }
        $now = date('Y-m-d H:i:s');
        $this->db->table('users')->insert([
            'username' => 'verify_' . bin2hex(random_bytes(5)),
            'full_name' => 'Verification Staff',
            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'created_at' => $now,
        ]);
        $this->userId = (int) $this->db->insertID();
        $this->db->table('products')->insert([
            'name' => 'Verification Product ' . bin2hex(random_bytes(4)),
            'price' => '9.99',
            'stock_quantity' => 3,
            'created_at' => $now,
        ]);
        $this->productId = (int) $this->db->insertID();
    }

    protected function tearDown(): void
    {
        if (isset($this->db) && $this->productId > 0) {
            $this->db->table('sales')->where('product_id', $this->productId)->delete();
            $this->db->table('products')->where('id', $this->productId)->delete();
        }
        if (isset($this->db) && $this->customerId > 0) {
            $this->db->table('customers')->where('id', $this->customerId)->delete();
        }
        if (isset($this->db) && $this->userId > 0) {
            $this->db->table('users')->where('id', $this->userId)->delete();
        }
        parent::tearDown();
    }

    public function testSaleDecrementsStockStoresPriceAndDeduplicatesAResubmission(): void
    {
        $service = new SaleService($this->db);
        $token = bin2hex(random_bytes(16));
        $first = $service->record($this->productId, null, $this->userId, 2, $token);
        $this->assertSame(['total_price' => '19.98', 'duplicate' => false], $first);
        $this->assertSame(1, (int) $this->db->table('products')->select('stock_quantity')->where('id', $this->productId)->get()->getRow('stock_quantity'));

        $second = $service->record($this->productId, null, $this->userId, 2, $token);
        $this->assertSame(['total_price' => '19.98', 'duplicate' => true], $second);
        $this->assertSame(1, $this->db->table('sales')->where('product_id', $this->productId)->countAllResults());
        $this->assertSame(1, (int) $this->db->table('products')->select('stock_quantity')->where('id', $this->productId)->get()->getRow('stock_quantity'));

        $this->db->table('products')->where('id', $this->productId)->update(['price' => '15.00']);
        $this->assertSame('19.98', (string) $this->db->table('sales')->select('total_price')->where('product_id', $this->productId)->get()->getRow('total_price'));
    }

    public function testInvalidQuantityCustomerAndInsufficientStockLeaveNoSale(): void
    {
        $service = new SaleService($this->db);
        foreach ([
            [$this->productId, null, 0],
            [$this->productId, null, 4],
            [$this->productId, 999999999, 1],
            [999999999, null, 1],
        ] as [$productId, $customerId, $quantity]) {
            try {
                $service->record($productId, $customerId, $this->userId, $quantity, bin2hex(random_bytes(16)));
                $this->fail('Invalid sale unexpectedly succeeded.');
            } catch (RuntimeException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
        $this->assertSame(3, (int) $this->db->table('products')->select('stock_quantity')->where('id', $this->productId)->get()->getRow('stock_quantity'));
        $this->assertSame(0, $this->db->table('sales')->where('product_id', $this->productId)->countAllResults());
    }

    public function testFailedSaleInsertRollsBackTheStockUpdate(): void
    {
        $service = new SaleService($this->db);
        $failed = false;
        try {
            $service->record($this->productId, null, 999999999, 1, bin2hex(random_bytes(16)));
        } catch (\Throwable $e) {
            $failed = true;
            $this->assertNotSame('', $e->getMessage());
        }
        $this->assertTrue($failed, 'A sale with a missing staff account unexpectedly succeeded.');
        $this->assertSame(3, (int) $this->db->table('products')->select('stock_quantity')->where('id', $this->productId)->get()->getRow('stock_quantity'));
        $this->assertSame(0, $this->db->table('sales')->where('product_id', $this->productId)->countAllResults());
    }

    public function testConcurrentRequestsCannotOversellTheLastUnit(): void
    {
        if (! function_exists('proc_open')) {
            $this->markTestSkipped('Concurrent process creation is unavailable.');
        }
        $this->db->table('products')->where('id', $this->productId)->update(['stock_quantity' => 1]);
        $workers = [];
        for ($i = 0; $i < 2; $i++) {
            $command = [PHP_BINARY, __DIR__ . '/concurrent_sale_worker.php', (string) $this->productId, (string) $this->userId, bin2hex(random_bytes(16))];
            $pipes = [];
            $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, ROOTPATH);
            $this->assertIsResource($process);
            fclose($pipes[0]);
            $workers[] = [$process, $pipes];
        }

        $results = [];
        foreach ($workers as [$process, $pipes]) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $error);
            $results[] = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        }

        $this->assertSame(1, count(array_filter($results, static fn (array $result): bool => $result['ok'])));
        $this->assertSame(1, count(array_filter($results, static fn (array $result): bool => ! $result['ok'])));
        $this->assertSame(0, (int) $this->db->table('products')->select('stock_quantity')->where('id', $this->productId)->get()->getRow('stock_quantity'));
        $this->assertSame(1, $this->db->table('sales')->where('product_id', $this->productId)->countAllResults());
    }
}
