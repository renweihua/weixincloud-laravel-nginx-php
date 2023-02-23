<?php

namespace App\libs\WechatCloud;

// 微信云管家的子服务端对外接口调用

use App\Traits\Instance;
use GuzzleHttp\Client;

class CloudServer
{
    use Instance;

    protected $client;

    // 微信云管家的服务地址
    const CLOUD_SERVER_URL = 'https://wxcomponent-92561-30973-7-1316902866.sh.run.tcloudbase.com';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => self::CLOUD_SERVER_URL
        ]);
    }

    // 第三方平台的component_access_token
    public function getComponentAccessToken()
    {
        $response = $this->client->get('/api/wechatcloud/get-component-access-token');

        $result = json_decode($response->getBody()->getContents());

        if ($response->getStatusCode() != HttpStatus::SUCCESS){
            throw new \Exception($result->errorMsg, 400);
        }

        return $result->data;
    }

    // 获取小程序的授权帐号令牌 authorizer_access_token
    public function getAuthorizerAccessToken($app_id)
    {
        $response = $this->client->get('/api/wechatcloud/get-authorizer-access-token?app_id=' . $app_id);

        $result = json_decode($response->getBody()->getContents());

        if ($response->getStatusCode() != HttpStatus::SUCCESS){
            throw new \Exception($result->errorMsg, 400);
        }

        return $result->data;
    }
}
