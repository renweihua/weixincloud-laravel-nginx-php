<?php

namespace App\Modules\WechatCloudComponent\Http\Controllers;

use App\Constants\HttpStatus;
use App\Modules\WechatCloudComponent\Http\Requests\AppIdRequest;
use App\Traits\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WechatCloudComponentController extends Controller
{
    use Json;

    public $server = 'http://127.0.0.1:8081';

    // 第三方平台的component_access_token
    public function getComponentAccessToken(): JsonResponse
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->server
        ]);
        $response = $client->get('/inner/component-access-token');

        $result = json_decode($response->getBody()->getContents());

        if ($response->getStatusCode() != HttpStatus::SUCCESS){
            return $this->errorJson($result->errorMsg);
        }

        return $this->successJson($result->data->token, $result->errorMsg);
    }

    // 获取小程序的授权帐号令牌 authorizer_access_token
    public function getAuthorizerAccessToken(AppIdRequest $request): JsonResponse
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->server
        ]);
        $response = $client->get('/inner/authorizer-access-token?appid=' . $request->input('app_id'));

        $result = json_decode($response->getBody()->getContents());

        if ($response->getStatusCode() != HttpStatus::SUCCESS){
            return $this->errorJson($result->errorMsg);
        }

        return $this->successJson($result->data->token, $result->errorMsg);
    }
}
