<?php

namespace App\Modules\WechatCloudComponent\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WechatCloudComponentController extends Controller
{
    // 第三方平台的component_access_token
    public function getComponentAccessToken(): JsonResponse
    {
        $client = new \GuzzleHttp\Client;
        $response = $client->get('http://127.0.0.1:8081/inner/component-access-token');
        return response()->json($response);
    }
}
