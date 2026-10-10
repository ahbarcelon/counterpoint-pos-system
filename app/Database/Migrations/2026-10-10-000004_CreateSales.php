<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSales extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'product_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'customer_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'sold_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'quantity' => ['type' => 'INT', 'constraint' => 11],
            'total_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('product_id');
        $this->forge->addKey('customer_id');
        $this->forge->addKey('sold_by');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->addForeignKey('sold_by', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('sales');
    }

    public function down(): void
    {
        $this->forge->dropTable('sales', true);
    }
}
