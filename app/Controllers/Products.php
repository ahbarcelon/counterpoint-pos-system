<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Services\UploadService;
use RuntimeException;
use Throwable;

class Products extends BaseController
{
    private ProductModel $products;
    private UploadService $uploads;

    public function __construct()
    {
        $this->products = new ProductModel();
        $this->uploads = new UploadService();
    }

    public function index()
    {
        return view('products/index', ['title' => 'Products', 'products' => $this->products->active()->findAll()]);
    }

    public function new()
    {
        return view('products/form', ['title' => 'Add product', 'product' => null, 'action' => '/products']);
    }

    public function create()
    {
        if (! $this->validate($this->rules())) {
            return view('products/form', ['title' => 'Add product', 'product' => $this->request->getPost(), 'action' => '/products']);
        }
        $image = null;
        try {
            $image = $this->uploads->store($this->request->getFile('image'), 'products', 1400, 1000);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        try {
            $created = $this->products->insert([
                'name' => trim((string) $this->request->getPost('name')),
                'price' => $this->request->getPost('price'),
                'stock_quantity' => (int) $this->request->getPost('stock_quantity'),
                'image' => $image,
                'created_at' => $this->now(),
            ]);
        } catch (Throwable $e) {
            $this->uploads->delete('products', $image);
            throw $e;
        }
        if ($created === false) {
            $this->uploads->delete('products', $image);
            return redirect()->back()->withInput()->with('error', 'The product could not be saved.');
        }
        return redirect()->to('/products')->with('success', 'Product added.');
    }

    public function edit(int $id)
    {
        $product = $this->products->active()->find($id);
        if (! $product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }
        return view('products/form', ['title' => 'Edit product', 'product' => $product, 'action' => '/products/' . $id]);
    }

    public function update(int $id)
    {
        $product = $this->products->active()->find($id);
        if (! $product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }
        if (! $this->validate($this->rules())) {
            return view('products/form', ['title' => 'Edit product', 'product' => array_merge($product, $this->request->getPost()), 'action' => '/products/' . $id]);
        }
        $newImage = null;
        try {
            $newImage = $this->uploads->store($this->request->getFile('image'), 'products', 1400, 1000);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
        $data = [
            'name' => trim((string) $this->request->getPost('name')),
            'price' => $this->request->getPost('price'),
            'stock_quantity' => (int) $this->request->getPost('stock_quantity'),
        ];
        if ($newImage !== null) {
            $data['image'] = $newImage;
        }
        try {
            $saved = $this->products->update($id, $data);
        } catch (Throwable $e) {
            $this->uploads->delete('products', $newImage);
            throw $e;
        }
        if (! $saved) {
            $this->uploads->delete('products', $newImage);
            return redirect()->back()->withInput()->with('error', 'The product could not be updated.');
        }
        if ($newImage !== null) {
            $this->uploads->delete('products', $product['image']);
        }
        return redirect()->to('/products')->with('success', 'Product updated.');
    }

    public function delete(int $id)
    {
        $product = $this->products->find($id);
        if (! $product || $product['archived_at'] !== null) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }
        $this->products->update($id, ['archived_at' => $this->now()]);
        return redirect()->to('/products')->with('success', 'Product archived. Sales history remains intact.');
    }

    private function rules(): array
    {
        return [
            'name' => 'required|max_length[100]',
            'price' => 'required|regex_match[/^\\d{1,8}(\\.\\d{1,2})?$/]',
            'stock_quantity' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[2147483647]',
        ];
    }
}
