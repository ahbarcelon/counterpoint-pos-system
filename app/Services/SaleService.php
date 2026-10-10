<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class SaleService
{
    public function __construct(private readonly BaseConnection $db)
    {
    }

    public function record(int $productId, ?int $customerId, int $staffId, int $quantity, string $requestToken): array
    {
        if ($quantity < 1 || ! preg_match('/\A[a-f0-9]{32}\z/', $requestToken)) {
            throw new RuntimeException('Quantity and sale request token must be valid.');
        }
        if (! $this->db->transBegin()) {
            throw new RuntimeException('The sale transaction could not be started.');
        }
        try {
            $existing = $this->findExisting($requestToken, $productId, $customerId, $staffId, $quantity);
            if ($existing !== null) {
                $this->db->transRollback();
                return $existing;
            }
            $product = $this->db->query('SELECT id, price, stock_quantity FROM products WHERE id = ? AND archived_at IS NULL FOR UPDATE', [$productId])->getRowArray();
            if (! $product) {
                throw new RuntimeException('That product is unavailable.');
            }
            $existing = $this->findExisting($requestToken, $productId, $customerId, $staffId, $quantity);
            if ($existing !== null) {
                $this->db->transRollback();
                return $existing;
            }
            if ($customerId !== null) {
                $customer = $this->db->query('SELECT id FROM customers WHERE id = ?', [$customerId])->getRowArray();
                if (! $customer) {
                    throw new RuntimeException('The selected customer does not exist.');
                }
            }
            $priceCents = $this->toCents((string) $product['price']);
            if ($priceCents > intdiv(9_999_999_999, $quantity)) {
                throw new RuntimeException('The sale total exceeds the supported amount.');
            }
            $total = $this->fromCents($priceCents * $quantity);
            $updated = $this->db->query('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?', [$quantity, $productId, $quantity]);
            if ($updated === false || $this->db->affectedRows() !== 1) {
                throw new RuntimeException('Insufficient stock for this product.');
            }
            $inserted = $this->db->table('sales')->insert([
                'product_id' => $productId,
                'customer_id' => $customerId,
                'sold_by' => $staffId,
                'quantity' => $quantity,
                'total_price' => $total,
                'created_at' => date('Y-m-d H:i:s'),
                'request_token' => $requestToken,
            ]);
            if ($inserted === false || ! $this->db->transStatus() || $this->db->affectedRows() !== 1) {
                throw new RuntimeException('The sale could not be saved.');
            }
            if (! $this->db->transCommit()) {
                throw new RuntimeException('The sale could not be committed.');
            }
            return ['total_price' => $total, 'duplicate' => false];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            $this->db->resetTransStatus();
            $existing = $this->findExisting($requestToken, $productId, $customerId, $staffId, $quantity);
            if ($existing !== null) {
                return $existing;
            }
            throw $e;
        }
    }

    private function findExisting(string $token, int $productId, ?int $customerId, int $staffId, int $quantity): ?array
    {
        $row = $this->db->table('sales')->select('product_id, customer_id, sold_by, quantity, total_price')
            ->where('request_token', $token)->get()->getRowArray();
        if ($row === null) {
            return null;
        }
        if ((int) $row['product_id'] !== $productId
            || ($row['customer_id'] === null ? null : (int) $row['customer_id']) !== $customerId
            || (int) $row['sold_by'] !== $staffId
            || (int) $row['quantity'] !== $quantity) {
            throw new RuntimeException('This sale form was already used for a different transaction.');
        }
        return ['total_price' => (string) $row['total_price'], 'duplicate' => true];
    }

    private function toCents(string $amount): int
    {
        if (! preg_match('/\A(\d{1,8})\.(\d{2})\z/', trim($amount), $parts)) {
            throw new RuntimeException('The product price is invalid.');
        }
        return ((int) $parts[1]) * 100 + (int) $parts[2];
    }

    private function fromCents(int $cents): string
    {
        return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
