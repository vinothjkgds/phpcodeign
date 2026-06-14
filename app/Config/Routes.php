<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

//Auth Login
$routes->get('/', 'Auth::login');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::login');
$routes->get('/logout', 'Auth::logout');

// SaaS control-plane auth
$routes->get('/saas/login', 'SaasAuth::login');
$routes->post('/saas/login', 'SaasAuth::login');
$routes->get('/saas/logout', 'SaasAuth::logout');

// SaaS control-plane
$routes->group('saas', ['filter' => 'saasauth'], function($routes) {
    $routes->get('dashboard', 'SaasAuth::dashboard');
    $routes->get('onboarding', 'SaasOnboarding::index');
    $routes->get('onboarding/add', 'SaasOnboarding::add');
    $routes->post('onboarding/save', 'SaasOnboarding::save');
    $routes->post('onboarding/approve/(:segment)', 'SaasOnboarding::approve/$1');
    $routes->post('onboarding/reject/(:segment)', 'SaasOnboarding::reject/$1');
});

$routes->get('lang/(:segment)', 'Auth::setLanguage/$1');
$routes->get('dashboard', 'Auth::dashboard', ['filter' => 'auth']);
$routes->get('cms/(:any)', 'Cms::$1', ['filter' => 'auth']);

//Merchant
$routes->group('merchant', ['filter' => 'auth'], function($routes) {
    $routes->get('view/(:segment)', 'Merchant::view/$1');       // /merchant/view/{reference_code}
    $routes->get('generate-statement/(:segment)', 'Merchant::generateMonthlyStatement/$1');
    $routes->get('download-statement/(:segment)/(:segment)', 'Merchant::downloadMonthlyStatement/$1/$2');
    $routes->get('(:any)', 'Merchant::$1');                      // dynamic method
    $routes->get('', 'Merchant::index');                         // /merchant
    $routes->post('add', 'Merchant::add');                       // /merchant/add
    $routes->post('save', 'Merchant::save');                     // /merchant/save
    $routes->post('save/(:any)', 'Merchant::save/$1');           // /merchant/save/{reference_code}
    $routes->post('getMerchantListJson', 'Merchant::getMerchantListJson');   // /merchant/getMerchantListJson
    $routes->post('delete/(:segment)', 'Merchant::delete/$1');   // /merchant/delete/{reference_code}
});

//Employee
$routes->group('employee', ['filter' => 'auth'], function($routes) {
    $routes->get('', 'Employee::index');
    $routes->get('add', 'Employee::add');
    $routes->get('edit/(:segment)', 'Employee::edit/$1');
    $routes->post('save', 'Employee::save');
    $routes->post('save/(:segment)', 'Employee::save/$1');
    $routes->post('getEmployeeListJson', 'Employee::getEmployeeListJson');
    $routes->post('delete/(:segment)', 'Employee::delete/$1');
});

//Product
$routes->group('product', ['filter' => 'auth'], function($routes) {
    $routes->get('', 'Product::index');
    $routes->get('add', 'Product::add');
    $routes->get('view/(:num)', 'Product::view/$1');
    $routes->get('edit/(:segment)', 'Product::edit/$1');
    $routes->get('stock-history', 'Product::stockHistory');
    $routes->post('save', 'Product::save');
    $routes->post('save/(:segment)', 'Product::save/$1');
    $routes->post('adjustStock/(:num)', 'Product::adjustStock/$1');
    $routes->post('getProductListJson', 'Product::getProductListJson');
    $routes->post('getStockHistoryListJson', 'Product::getStockHistoryListJson');
    $routes->get('getProductInfo/(:num)', 'Product::getProductInfo/$1');
    $routes->post('delete/(:segment)', 'Product::delete/$1');
});

//Category
$routes->group('category', ['filter' => 'auth'], function($routes) {
    $routes->get('', 'Category::index');
    $routes->get('add', 'Category::add');
    $routes->get('edit/(:segment)', 'Category::edit/$1');
    $routes->post('save', 'Category::save');
    $routes->post('save/(:segment)', 'Category::save/$1');
    $routes->post('getCategoryListJson', 'Category::getCategoryListJson');
    $routes->post('delete/(:segment)', 'Category::delete/$1');
});

//Shop
$routes->group('shop', ['filter' => 'auth'], function($routes) {
    $routes->get('', 'Shop::index');
    $routes->post('save', 'Shop::save');
});

//Sale / Purchase
$routes->group('salepurchase', ['filter' => 'auth'], function($routes) {
    $routes->get('', 'Salepurchase::index');
    $routes->get('add', 'Salepurchase::add');
    $routes->get('invoice/(:num)', 'Salepurchase::invoice/$1');
    $routes->get('invoice/download/(:num)', 'Salepurchase::downloadInvoice/$1');
    $routes->get('export/csv', 'Salepurchase::exportCsv');
    $routes->get('export/excel', 'Salepurchase::exportExcel');
    $routes->post('import/csv', 'Salepurchase::importCsv');
    $routes->post('save', 'Salepurchase::save');
    $routes->post('getSalePurchaseListJson', 'Salepurchase::getSalePurchaseListJson');
});
