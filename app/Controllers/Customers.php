<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\SaleModel;

class Customers extends BaseController
{
    private CustomerModel $customers;

    public function __construct()
    {
        $this->customers = new CustomerModel();
    }

    public function index()
    {
        return view('customers/index', ['title' => 'Customers', 'customers' => $this->customers->orderBy('full_name')->findAll()]);
    }

    public function new()
    {
        return view('customers/form', ['title' => 'Add customer', 'customer' => null, 'action' => '/customers']);
    }

    public function create()
    {
        if (! $this->validate($this->rules())) {
            return view('customers/form', ['title' => 'Add customer', 'customer' => $this->request->getPost(), 'action' => '/customers']);
        }
        $this->customers->insert($this->data());
        return redirect()->to('/customers')->with('success', 'Customer added.');
    }

    public function show(int $id)
    {
        $customer = $this->customers->find($id);
        if (! $customer) return redirect()->to('/customers')->with('error', 'Customer not found.');
        $sales = (new SaleModel())->where('customer_id', $id)->orderBy('created_at', 'DESC')->findAll();
        return view('customers/show', ['title' => 'Customer details', 'customer' => $customer, 'sales' => $sales]);
    }

    public function edit(int $id)
    {
        $customer = $this->customers->find($id);
        if (! $customer) return redirect()->to('/customers')->with('error', 'Customer not found.');
        return view('customers/form', ['title' => 'Edit customer', 'customer' => $customer, 'action' => '/customers/' . $id]);
    }

    public function update(int $id)
    {
        if (! $this->customers->find($id)) return redirect()->to('/customers')->with('error', 'Customer not found.');
        if (! $this->validate($this->rules())) {
            return view('customers/form', ['title' => 'Edit customer', 'customer' => array_merge($this->customers->find($id), $this->request->getPost()), 'action' => '/customers/' . $id]);
        }
        $data = $this->data();
        unset($data['created_at']);
        $this->customers->update($id, $data);
        return redirect()->to('/customers')->with('success', 'Customer updated.');
    }

    public function delete(int $id)
    {
        if (! $this->customers->find($id)) return redirect()->to('/customers')->with('error', 'Customer not found.');
        if ((new SaleModel())->where('customer_id', $id)->countAllResults() > 0) {
            return redirect()->to('/customers')->with('error', 'This customer has sales and cannot be deleted; sales history is protected.');
        }
        $this->customers->delete($id);
        return redirect()->to('/customers')->with('success', 'Customer deleted.');
    }

    private function rules(): array
    {
        return ['full_name' => 'required|max_length[100]', 'email' => 'required|valid_email|max_length[100]', 'phone' => 'permit_empty|max_length[20]'];
    }

    private function data(): array
    {
        return ['full_name' => trim((string) $this->request->getPost('full_name')), 'email' => trim((string) $this->request->getPost('email')), 'phone' => trim((string) $this->request->getPost('phone')) ?: null, 'created_at' => $this->now()];
    }
}
