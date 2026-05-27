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
$routes->get('dashboard', 'Auth::dashboard', ['filter' => 'auth']);
$routes->get('cms/(:any)', 'Cms::$1');

//Merchant
$routes->group('merchant', ['filter' => 'auth'], function($routes) {
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
