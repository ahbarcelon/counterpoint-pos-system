<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleModel extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['product_id', 'customer_id', 'sold_by', 'quantity', 'total_price', 'created_at'];
    protected $useTimestamps = false;

    public function history(int $limit = 100): array
    {
        return $this->select('sales.*, products.name AS product_name, COALESCE(customers.full_name, "Walk-in") AS customer_name, users.full_name AS staff_name')
            ->join('products', 'products.id = sales.product_id')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->join('users', 'users.id = sales.sold_by')
            ->orderBy('sales.created_at', 'DESC')->orderBy('sales.id', 'DESC')->findAll($limit);
    }
}
