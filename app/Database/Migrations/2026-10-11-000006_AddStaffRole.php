<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStaffRole extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'is_admin' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);

        // Preserve the existing production administrator without changing credentials.
        $this->db->table('users')->where('username', 'admin')->update(['is_admin' => 1]);

        // Older installations may have named their first account differently.
        if ($this->db->table('users')->where('is_admin', 1)->countAllResults() === 0) {
            $first = $this->db->table('users')->select('id')->orderBy('id', 'ASC')->get(1)->getRowArray();
            if ($first !== null) {
                $this->db->table('users')->where('id', $first['id'])->update(['is_admin' => 1]);
            }
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'is_admin');
    }
}
