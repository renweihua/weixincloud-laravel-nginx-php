<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('wechatcloudcomponent')->group(function() {
    // 第三方平台的component_access_token
    Route::get('/get-component-access-token', 'WechatCloudComponentController@getComponentAccessToken');
    // 获取小程序的授权帐号令牌 authorizer_access_token
    Route::get('/get-authorizer-access-token', 'WechatCloudComponentController@getAuthorizerAccessToken');
});
