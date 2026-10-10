<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSaleRequestToken extends Migration
{
    public function up(): void
    {
        $this->db->query('ALTER TABLE sales ADD COLUMN request_token CHAR(32) NULL, ADD UNIQUE KEY sales_request_token_unique (request_token)');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE sales DROP KEY sales_request_token_unique, DROP COLUMN request_token');
    }
}
