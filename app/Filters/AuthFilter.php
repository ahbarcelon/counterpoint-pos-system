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

        $user = (new UserModel())->select('id, is_admin')->find($userId);
        if (! $user) {
            session()->destroy();
            return redirect()->to('/login')->with('error', 'This staff account is no longer available. Please sign in again.');
        }

        // Refresh privileges on each request; a session never grants a stale role.
        session()->set('is_admin', (int) $user['is_admin']);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
