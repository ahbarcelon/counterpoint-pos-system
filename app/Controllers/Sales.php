<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Services\SaleService;
use RuntimeException;

class Sales extends BaseController
{
    public function index()
    {
        return view('sales/index', ['title' => 'Sales history', 'sales' => (new SaleModel())->history()]);
    }

    public function new()
    {
        return view('sales/form', ['title' => 'Record sale', 'products' => (new ProductModel())->active()->findAll(), 'customers' => (new CustomerModel())->orderBy('full_name')->findAll(), 'requestToken' => old('request_token', bin2hex(random_bytes(16)))]);
    }

    public function create()
    {
        $rules = ['product_id' => 'required|integer|greater_than[0]', 'customer_id' => 'permit_empty|integer|greater_than[0]', 'quantity' => 'required|integer|regex_match[/^[1-9][0-9]*$/]', 'request_token' => 'required|regex_match[/^[a-f0-9]{32}$/]'];
        if (! $this->validate($rules)) return redirect()->back()->withInput();
        $customerId = $this->request->getPost('customer_id') === '' ? null : (int) $this->request->getPost('customer_id');
        try {
            $result = (new SaleService(db_connect()))->record((int) $this->request->getPost('product_id'), $customerId, (int) session('user_id'), (int) $this->request->getPost('quantity'), (string) $this->request->getPost('request_token'));
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        return redirect()->to('/sales')->with('success', ($result['duplicate'] ? 'This sale was already recorded for ₱' : 'Sale recorded for ₱') . $result['total_price'] . '.');
    }
}
