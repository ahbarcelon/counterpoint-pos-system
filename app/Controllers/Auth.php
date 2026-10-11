<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->has('user_id')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login', ['title' => 'Sign in']);
    }

    public function attempt()
    {
        $rules = ['username' => 'required|max_length[50]', 'password' => 'required'];
        if (! $this->validate($rules)) {
            return view('auth/login', ['title' => 'Sign in']);
        }
        $username = trim((string) $this->request->getPost('username'));
        $user = (new UserModel())->where('username', $username)->first();
        if (! $user || ! password_verify((string) $this->request->getPost('password'), $user['password'])) {
            return redirect()->back()->with('error', 'The username or password is incorrect.');
        }
        session()->regenerate(true);
        session()->set([
            'user_id' => (int) $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'avatar' => $user['avatar'],
            'is_admin' => (int) $user['is_admin'],
        ]);
        return redirect()->to('/dashboard')->with('success', 'Welcome back, ' . $user['full_name'] . '.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been signed out.');
    }
}
