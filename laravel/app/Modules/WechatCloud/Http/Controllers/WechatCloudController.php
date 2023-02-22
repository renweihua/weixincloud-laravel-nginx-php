<?php

namespace App\Modules\WechatCloud\Http\Controllers;

use App\Constants\HttpStatus;
use App\Modules\WechatCloud\Http\Requests\AppIdRequest;
use App\Traits\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WechatCloudController extends Controller
{
    use Json;

    // 云管理服务的内网端口
    public $server = 'http://127.0.0.1:8081';

    // 第三方平台的component_access_token
    public function getComponentAccessToken(): JsonResponse
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->server
        ]);
        $response = $client->get('/inner/component-access-token');

        $result = json_decode($response->getBody()->getContents());

        if ($response->getStatusCode() != HttpStatus::SUCCESS || $result->code != 0){
            return $this->errorJson($result->errorMsg);
        }

        return $this->successJson([
            'component_appid' => getenv('WX_APPID'),
            'component_access_token' => $result->data->token,
        ], $result->errorMsg);
    }

    // 获取小程序的授权帐号令牌 authorizer_access_token
    public function getAuthorizerAccessToken(AppIdRequest $request): JsonResponse
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->server
        ]);
        $app_id = $request->input('app_id');
        $response = $client->get('/inner/authorizer-access-token?appid=' . $app_id);

        $result = json_decode($response->getBody()->getContents());

        if ($response->getStatusCode() != HttpStatus::SUCCESS || $result->code != 0){
            return $this->errorJson($result->errorMsg);
        }

        return $this->successJson([
            'authorizer_appid' => $app_id,
            'authorizer_access_token' => $result->data->token,
        ], $result->errorMsg);
    }
}
