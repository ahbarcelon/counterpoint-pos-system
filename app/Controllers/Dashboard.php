<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $sales = new SaleModel();
        return view('dashboard', [
            'title' => 'Dashboard',
            'productCount' => (new ProductModel())->active()->countAllResults(),
            'customerCount' => (new CustomerModel())->countAllResults(),
            'staffCount' => (new UserModel())->countAllResults(),
            'saleCount' => $sales->countAllResults(),
            'recentSales' => $sales->history(5),
        ]);
    }
}
