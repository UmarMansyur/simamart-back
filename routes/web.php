<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'barang'], function () use ($router){
    $router->get('/', 'BarangController@index');
    $router->get('/{id}', 'BarangController@show');
    $router->post('/', 'BarangController@store');
    $router->put('/{id}', 'BarangController@update');
    $router->delete('/{id}', 'BarangController@destroy');
});

$router->group(['prefix' => 'transaksi'], function () use ($router){
    $router->get('/', 'TransaksiController@index');
    $router->get('/{id}', 'TransaksiController@show');
    $router->post('/', 'TransaksiController@store');
    $router->put('/{id}', 'TransaksiController@update');
    $router->delete('/{id}', 'TransaksiController@destroy');
});

$router->group(['prefix' => 'detail-transaksi'], function () use ($router){
    $router->get('/', 'DetailTransaksiController@index');
    $router->get('/{id}', 'DetailTransaksiController@show');
    $router->post('/', 'DetailTransaksiController@store');
    $router->put('/{id}', 'DetailTransaksiController@update');
    $router->delete('/{id}', 'DetailTransaksiController@destroy');
    $router->get('/export/{start_date}/{end_date}', 'DetailTransaksiController@report');
});
