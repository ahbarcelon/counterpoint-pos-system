<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProducts extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'stock_quantity' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'archived_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('archived_at');
        $this->forge->createTable('products');
    }

    public function down(): void
    {
        $this->forge->dropTable('products', true);
    }
}
