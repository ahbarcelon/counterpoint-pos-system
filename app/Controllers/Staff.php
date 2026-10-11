<?php

namespace App\Controllers;

use App\Models\SaleModel;
use App\Models\UserModel;
use App\Services\UploadService;
use App\Services\StaffAccess;
use RuntimeException;
use Throwable;

class Staff extends BaseController
{
    private UserModel $users;
    private UploadService $uploads;

    public function __construct()
    {
        $this->users = new UserModel();
        $this->uploads = new UploadService();
    }

    public function index()
    {
        if (! $this->isAdmin()) {
            return redirect()->to('/staff/' . (int) session('user_id'));
        }
        return view('staff/index', ['title' => 'Staff', 'staff' => $this->users->safeList()]);
    }

    public function new()
    {
        if (! StaffAccess::canCreateOrDelete($this->isAdmin())) return $this->forbidden();
        return view('staff/form', ['title' => 'Add staff member', 'staffMember' => null, 'action' => '/staff']);
    }

    public function create()
    {
        if (! StaffAccess::canCreateOrDelete($this->isAdmin())) return $this->forbidden();
        if (! $this->validate($this->rules(true))) return view('staff/form', ['title' => 'Add staff member', 'staffMember' => $this->request->getPost(['username', 'full_name']), 'action' => '/staff']);
        if ($this->users->where('username', trim((string) $this->request->getPost('username')))->first()) return redirect()->back()->with('error', 'That username is already in use.');
        try { $avatar = $this->uploads->store($this->request->getFile('avatar'), 'avatars', 600, 600); } catch (RuntimeException $e) { return redirect()->back()->with('error', $e->getMessage()); }
        try {
            $created = $this->users->insert([
                'username' => trim((string) $this->request->getPost('username')),
                'full_name' => trim((string) $this->request->getPost('full_name')),
                'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
                'is_admin' => 0,
                'avatar' => $avatar,
                'created_at' => $this->now(),
            ]);
        } catch (Throwable $e) {
            $this->uploads->delete('avatars', $avatar);
            throw $e;
        }
        if ($created === false) {
            $this->uploads->delete('avatars', $avatar);
            return redirect()->back()->with('error', 'The staff account could not be saved.');
        }
        return redirect()->to('/staff')->with('success', 'Staff member added.');
    }

    public function show(int $id)
    {
        if (! StaffAccess::canViewOrEdit($this->isAdmin(), (int) session('user_id'), $id)) return $this->forbidden();
        $staffMember = $this->users->safeFind($id);
        if (! $staffMember) return redirect()->to('/staff')->with('error', 'Staff member not found.');
        $sales = (new SaleModel())->select('sales.*, products.name AS product_name')->join('products', 'products.id = sales.product_id')->where('sold_by', $id)->orderBy('sales.created_at', 'DESC')->orderBy('sales.id', 'DESC')->findAll(20);
        return view('staff/show', ['title' => 'Staff details', 'staffMember' => $staffMember, 'sales' => $sales]);
    }

    public function edit(int $id)
    {
        if (! StaffAccess::canViewOrEdit($this->isAdmin(), (int) session('user_id'), $id)) return $this->forbidden();
        $staffMember = $this->users->safeFind($id);
        if (! $staffMember) return redirect()->to('/staff')->with('error', 'Staff member not found.');
        return view('staff/form', ['title' => 'Edit staff member', 'staffMember' => $staffMember, 'action' => '/staff/' . $id]);
    }

    public function update(int $id)
    {
        if (! StaffAccess::canViewOrEdit($this->isAdmin(), (int) session('user_id'), $id)) return $this->forbidden();
        $existing = $this->users->find($id);
        if (! $existing) return redirect()->to('/staff')->with('error', 'Staff member not found.');
        if (! $this->validate($this->rules(false))) return view('staff/form', ['title' => 'Edit staff member', 'staffMember' => array_merge($this->users->safeFind($id), $this->request->getPost(['username', 'full_name'])), 'action' => '/staff/' . $id]);
        $username = trim((string) $this->request->getPost('username'));
        $duplicate = $this->users->where('username', $username)->where('id !=', $id)->first();
        if ($duplicate) return redirect()->back()->with('error', 'That username is already in use.');
        $data = ['username' => $username, 'full_name' => trim((string) $this->request->getPost('full_name'))];
        $password = (string) $this->request->getPost('password');
        if ($password !== '') $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        try { $avatar = $this->uploads->store($this->request->getFile('avatar'), 'avatars', 600, 600); } catch (RuntimeException $e) { return redirect()->back()->with('error', $e->getMessage()); }
        if ($avatar !== null) $data['avatar'] = $avatar;
        try {
            $saved = $this->users->update($id, $data);
        } catch (Throwable $e) {
            $this->uploads->delete('avatars', $avatar);
            throw $e;
        }
        if (! $saved) {
            $this->uploads->delete('avatars', $avatar);
            return redirect()->back()->with('error', 'The staff account could not be updated.');
        }
        if ($avatar !== null) $this->uploads->delete('avatars', $existing['avatar']);
        if ((int) session('user_id') === $id) session()->set(['username' => $data['username'], 'full_name' => $data['full_name'], 'avatar' => $data['avatar'] ?? $existing['avatar']]);
        return redirect()->to('/staff')->with('success', 'Staff member updated.');
    }

    public function delete(int $id)
    {
        if (! StaffAccess::canCreateOrDelete($this->isAdmin())) return $this->forbidden();
        $staffMember = $this->users->find($id);
        if (! $staffMember) return redirect()->to('/staff')->with('error', 'Staff member not found.');
        if ($this->users->countAllResults() <= 1) return redirect()->to('/staff')->with('error', 'The final staff account cannot be removed.');
        if ((int) $staffMember['is_admin'] === 1 && $this->users->where('is_admin', 1)->countAllResults() <= 1) return redirect()->to('/staff')->with('error', 'The final administrator account cannot be removed.');
        if ((new SaleModel())->where('sold_by', $id)->countAllResults() > 0) return redirect()->to('/staff')->with('error', 'This staff member has sales and cannot be deleted; history is protected.');
        if ((int) session('user_id') === $id) return redirect()->to('/staff')->with('error', 'You cannot remove the account currently in use.');
        $this->users->delete($id);
        $this->uploads->delete('avatars', $staffMember['avatar']);
        return redirect()->to('/staff')->with('success', 'Staff member deleted.');
    }

    private function rules(bool $passwordRequired): array
    {
        return ['username' => 'required|alpha_numeric_punct|max_length[50]', 'full_name' => 'required|max_length[100]', 'password' => ($passwordRequired ? 'required|' : 'permit_empty|') . 'min_length[8]'];
    }

    private function forbidden()
    {
        return $this->response->setStatusCode(403)->setBody('You do not have permission to manage this staff account.');
    }
}
