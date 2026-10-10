<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['username', 'full_name', 'password', 'avatar', 'created_at'];
    protected $useTimestamps = false;

    public function safeList(): array
    {
        return $this->select('id, username, full_name, avatar, created_at')->orderBy('full_name', 'ASC')->findAll();
    }

    public function safeFind(int $id): ?array
    {
        return $this->select('id, username, full_name, avatar, created_at')->find($id);
    }
}
