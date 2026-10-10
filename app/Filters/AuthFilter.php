<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = (int) session('user_id');
        if ($userId < 1) {
            return redirect()->to('/login')->with('error', 'Please sign in to continue.');
        }

        if (! (new UserModel())->select('id')->find($userId)) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'This staff account is no longer available. Please sign in again.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
