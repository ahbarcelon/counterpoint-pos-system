<?php

namespace App\Database\Seeds;

use App\Services\SaleService;
use CodeIgniter\Database\Seeder;
use RuntimeException;

/** Optional, repeatable PC-store showcase data. Never run automatically on deploy. */
class PcStoreSampleSeeder extends Seeder
{
    private const PRODUCTS = [
        ['Aurora Ryzen 5 Gaming Desktop', '45995.00', 14],
        ['Nova Core i7 Creator Desktop', '78995.00', 7],
        ['Titan RTX 4070 Graphics Card', '38995.00', 12],
        ['Pulse RX 7600 Graphics Card', '17995.00', 15],
        ['HyperX 32GB DDR5 Memory Kit', '7295.00', 20],
        ['Kingston 16GB DDR4 Memory Kit', '2695.00', 34],
        ['Samsung 1TB NVMe SSD', '5295.00', 28],
        ['Crucial 2TB SATA SSD', '6395.00', 18],
        ['Seagate 2TB Hard Drive', '3495.00', 22],
        ['ASUS B650M Motherboard', '11995.00', 9],
        ['MSI B760 WiFi Motherboard', '10495.00', 11],
        ['Corsair 750W Gold Power Supply', '6995.00', 19],
        ['NZXT Mid-Tower PC Case', '5995.00', 11],
        ['Arctic 240mm Liquid Cooler', '4795.00', 17],
        ['ViewSonic 27-inch 165Hz Monitor', '14995.00', 13],
        ['AOC 24-inch IPS Monitor', '8995.00', 16],
        ['Logitech Mechanical Keyboard', '4295.00', 24],
        ['Razer Wired Gaming Mouse', '2495.00', 31],
        ['TP-Link WiFi 6 Router', '3895.00', 19],
        ['APC 1000VA UPS', '6995.00', 8],
    ];

    private const CUSTOMERS = [
        'Alexis Navarro', 'Bianca Cruz', 'Carlo Mendoza', 'Daniella Reyes',
        'Ethan Villanueva', 'Faith Lim', 'Gabriel Ramos', 'Hannah Castillo',
        'Ian Bautista', 'Jasmine Tan', 'Kevin Garcia', 'Lara Santos',
        'Marco Flores', 'Nina Aquino', 'Owen Dela Cruz', 'Paula Mercado',
        'Quinn Sy', 'Rafael Torres', 'Sofia Ong', 'Theo Valdez',
    ];

    private const STAFF = [
        'Avery Rivera', 'Bea Fernandez', 'Cyrus Lopez', 'Denise Yu',
        'Elijah Co', 'Fiona Padilla', 'Gio Chua', 'Hazel Domingo',
        'Isaac Robles', 'Jade Pineda', 'Kyle Salvador', 'Lia Chan',
        'Milo Estrada', 'Noelle Diaz', 'Oliver Go', 'Pia Lorenzo',
        'Rico Perez', 'Sam Bautista', 'Tessa Wong', 'Vince Alonzo',
    ];

    public function run(): void
    {
        if ($this->db->table('users')->where('is_admin', 1)->countAllResults() === 0) {
            throw new RuntimeException('Create the first administrator with pos:create-staff before loading sample data.');
        }

        $now = date('Y-m-d H:i:s');
        $products = [];
        $customers = [];
        $staff = [];

        foreach (self::PRODUCTS as [$name, $price, $stock]) {
            $row = $this->db->table('products')->select('id')->where('name', $name)->get(1)->getRowArray();
            if ($row === null) {
                $this->insertOrFail('products', [
                    'name' => $name, 'price' => $price, 'stock_quantity' => $stock,
                    'image' => null, 'created_at' => $now,
                ]);
                $row = ['id' => $this->db->insertID()];
            }
            $products[] = (int) $row['id'];
        }

        foreach (self::CUSTOMERS as $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '@example.test';
            $row = $this->db->table('customers')->select('id')->where('email', $email)->get(1)->getRowArray();
            if ($row === null) {
                $this->insertOrFail('customers', [
                    'full_name' => $name, 'email' => $email, 'phone' => null, 'created_at' => $now,
                ]);
                $row = ['id' => $this->db->insertID()];
            }
            $customers[] = (int) $row['id'];
        }

        foreach (self::STAFF as $index => $name) {
            $username = sprintf('demo_staff_%02d', $index + 1);
            $row = $this->db->table('users')->select('id')->where('username', $username)->get(1)->getRowArray();
            if ($row === null) {
                $this->insertOrFail('users', [
                    'username' => $username, 'full_name' => $name,
                    'password' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                    'avatar' => null, 'is_admin' => 0, 'created_at' => $now,
                ]);
                $row = ['id' => $this->db->insertID()];
            }
            $staff[] = (int) $row['id'];
        }

        $sales = new SaleService($this->db);
        foreach (self::PRODUCTS as $index => $_product) {
            $token = substr(hash('sha256', 'counterpoint-pc-store-sample-v1-sale-' . $index), 0, 32);
            $customerId = $index % 5 === 0 ? null : $customers[$index];
            $quantity = $index % 4 === 0 ? 2 : 1;
            $sales->record($products[$index], $customerId, $staff[$index % 5], $quantity, $token);
        }
    }

    private function insertOrFail(string $table, array $data): void
    {
        if (! $this->db->table($table)->insert($data)) {
            throw new RuntimeException('Could not insert PC-store sample ' . $table . ' row.');
        }
    }
}
