<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\CounterController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/laravel', function () {
    return view('welcome');
});

Route::get('/phpinfo', function () {
    return phpinfo();
});

Route::get('/counter', function () {
    return view('counter');
});

// 获取当前计数
Route::get('/api/count', [CounterController::class, 'getCount']);

// 更新计数，自增或者清零
Route::post('/api/count', [CounterController::class, 'updateCount']);

Route::get('/component-access-token', function () {
    $client = new \GuzzleHttp\Client;
    $response = $client->get('http://127.0.0.1:8081/inner/component-access-token');
    var_dump($response->getStatusCode());
    var_dump($response->getBody()->getContents());
});
