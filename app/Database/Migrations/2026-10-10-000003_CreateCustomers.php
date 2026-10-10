<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomers extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'full_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'email' => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('customers');
    }

    public function down(): void
    {
        $this->forge->dropTable('customers', true);
    }
}
