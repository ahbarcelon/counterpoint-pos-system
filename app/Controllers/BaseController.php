<?php

namespace App\Controllers;

use CodeIgniter\Controller;

abstract class BaseController extends Controller
{
    protected $helpers = ['form', 'url', 'text'];

    protected function currentUser(): array
    {
        return [
            'id' => (int) session('user_id'),
            'username' => (string) session('username'),
            'full_name' => (string) session('full_name'),
            'avatar' => session('avatar'),
        ];
    }

    protected function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
