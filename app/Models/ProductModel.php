<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'price', 'stock_quantity', 'image', 'created_at', 'archived_at'];
    protected $useTimestamps = false;

    public function active(): self
    {
        return $this->where('archived_at IS NULL', null, false)->orderBy('name', 'ASC');
    }
}
