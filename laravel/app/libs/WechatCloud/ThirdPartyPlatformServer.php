<?php

namespace App\libs\WechatCloud;

// 微信第三方平台接口封装
// https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/

use App\Traits\Instance;
use GuzzleHttp\Client;

class ThirdPartyPlatformServer
{
    use Instance;

    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.weixin.qq.com'
        ]);
    }

    // 获取预授权码
    // https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/token/authorization_info.html
    public function getPreauthcode($component_data, &$error = '')
    {
        $result = false;
        // 获取第三方平台的token与app_id
        try{
            // $component_data = CloudServer::getInstance()->getComponentAccessToken();

            $response = $this->client->post('/cgi-bin/component/api_create_preauthcode?component_access_token=' . $component_data->component_access_token, [
                'body' => json_encode([
                    'component_appid' => $component_data->component_appid,
                ], JSON_UNESCAPED_UNICODE),
            ]);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode)){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }

        // object(stdClass)#2094 (2) {
        //     ["pre_auth_code"]=>
        //   string(100) "preauthcode@@@blzWgoI6xZdn4fqdDH-5HsJfmTiiNrvGHeg22SZMxsmOMlGhCna5C7lgjhR1kTAY7wdyiOlq7Cab-zizPTyoVg"
        //     ["expires_in"]=>
        //   int(1800)
        // }
        return $result;
    }

    // 拼接授权回调URL
    public function getCallbackUrl($component_appid, $pre_auth_code, $redirect_uri, $type = 0, $auth_type = 3)
    {
        // auth_type - 1 表示手机端仅展示公众号；2 表示仅展示小程序，3 表示公众号和小程序都展示。如果为未指定，则默认小程序和公众号都展示。
        switch ($type){
            case 1: // H5新版
                $url = 'https://open.weixin.qq.com/wxaopen/safe/bindcomponent?action=bindcomponent&no_scan=1&component_appid=' . $component_appid . '&pre_auth_code=' . $pre_auth_code . '&redirect_uri=' . $redirect_uri . '&auth_type=' . $auth_type . '&biz_appid=#wechat_redirect';
                break;
            case 2: // H5旧版
                $url = 'https://mp.weixin.qq.com/safe/bindcomponent?action=bindcomponent&no_scan=1&component_appid=' . $component_appid . '&pre_auth_code=' . $pre_auth_code . '&redirect_uri=' . $redirect_uri . '&auth_type=' . $auth_type . '&biz_appid=#wechat_redirect';
                break;
            default: // PC端
                $url = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=' . $component_appid . '&pre_auth_code=' . $pre_auth_code . '&redirect_uri=' . $redirect_uri . '&auth_type=' . $auth_type;
        }
        return $url;
    }

    // 使用授权码获取授权信息
    // https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/token/authorization_info.html
    public function getAuthorizationByPreauthcode($component_data, $authorization_code, &$error = '')
    {
        $result = false;
        // 获取第三方平台的token与app_id
        try{
            // $component_data = CloudServer::getInstance()->getComponentAccessToken();

            $response = $this->client->post('/cgi-bin/component/api_query_auth?component_access_token=' . $component_data->component_access_token, [
                'body' => json_encode([
                    'component_appid' => $component_data->component_appid,
                    'authorization_code' => $authorization_code,
                ], JSON_UNESCAPED_UNICODE),
            ]);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode)){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }

        // object(stdClass)#2094 (2) {
        //     ["pre_auth_code"]=>
        //   string(100) "preauthcode@@@blzWgoI6xZdn4fqdDH-5HsJfmTiiNrvGHeg22SZMxsmOMlGhCna5C7lgjhR1kTAY7wdyiOlq7Cab-zizPTyoVg"
        //     ["expires_in"]=>
        //   int(1800)
        // }
        return $result;
    }
}
