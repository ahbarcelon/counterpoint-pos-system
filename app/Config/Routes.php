<?php

use CodeIgniter\Router\RouteCollection;

$routes->get('/', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->post('logout', 'Auth::logout', ['filter' => 'auth']);

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('products', 'Products::index');
    $routes->get('products/new', 'Products::new');
    $routes->post('products', 'Products::create');
    $routes->get('products/(:num)/edit', 'Products::edit/$1');
    $routes->post('products/(:num)', 'Products::update/$1');
    $routes->post('products/(:num)/delete', 'Products::delete/$1');

    $routes->get('customers', 'Customers::index');
    $routes->get('customers/new', 'Customers::new');
    $routes->post('customers', 'Customers::create');
    $routes->get('customers/(:num)', 'Customers::show/$1');
    $routes->get('customers/(:num)/edit', 'Customers::edit/$1');
    $routes->post('customers/(:num)', 'Customers::update/$1');
    $routes->post('customers/(:num)/delete', 'Customers::delete/$1');

    $routes->get('staff', 'Staff::index');
    $routes->get('staff/new', 'Staff::new');
    $routes->post('staff', 'Staff::create');
    $routes->get('staff/(:num)', 'Staff::show/$1');
    $routes->get('staff/(:num)/edit', 'Staff::edit/$1');
    $routes->post('staff/(:num)', 'Staff::update/$1');
    $routes->post('staff/(:num)/delete', 'Staff::delete/$1');

    $routes->get('sales', 'Sales::index');
    $routes->get('sales/new', 'Sales::new');
    $routes->post('sales', 'Sales::create');
});
