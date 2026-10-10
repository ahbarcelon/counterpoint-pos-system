<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('products')->insertBatch([
            ['name' => 'House Blend Coffee', 'price' => '145.00', 'stock_quantity' => 25, 'image' => null, 'created_at' => $now],
            ['name' => 'Citrus Tea', 'price' => '120.00', 'stock_quantity' => 18, 'image' => null, 'created_at' => $now],
            ['name' => 'Chocolate Cookie', 'price' => '75.00', 'stock_quantity' => 30, 'image' => null, 'created_at' => $now],
        ]);
        $this->db->table('customers')->insertBatch([
            ['full_name' => 'Alex Santos', 'email' => 'alex@example.test', 'phone' => '09170000001', 'created_at' => $now],
            ['full_name' => 'Jamie Cruz', 'email' => 'jamie@example.test', 'phone' => null, 'created_at' => $now],
        ]);
    }
}
