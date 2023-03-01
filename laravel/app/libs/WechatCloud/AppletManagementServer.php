<?php

namespace App\libs\WechatCloud;

// 代商家管理小程序
// https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/login/thirdpartyCode2Session.html

use App\Traits\Instance;
use GuzzleHttp\Client;

class AppletManagementServer
{
    use Instance;

    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.weixin.qq.com'
        ]);
    }

    // 小程序登录
    // https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/login/thirdpartyCode2Session.html
    public function thirdpartyCode2Session($component_access_token, $component_appid, $appid, $js_code)
    {
        $response = $this->client->get('/sns/component/jscode2session?component_access_token=' . $component_access_token, [
            'query' => [
                'appid' => $appid,
                'grant_type' => 'authorization_code',
                'component_appid' => $component_appid,
                'js_code' => $js_code,
            ]
        ]);
        return $response;
    }

    // 获取基本信息
    // https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/basic-info-management/getAccountBasicInfo.html
    public function getAccountBasicInfo($authorizer_access_token, &$error = '')
    {
        $result = false;
        try{
            $response = $this->client->post('/cgi-bin/account/getaccountbasicinfo?access_token=' . $authorizer_access_token);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode) && $result->errcode != WechatCode::SUCCESS){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }
        return $result;
    }

    // 设置第三方平台服务器域名
    public function modifyThirdpartyServerDomain($component_access_token, $action, $wxa_server_domain = '', $is_modify_published_together = false, &$error = '')
    {
        $result = false;
        try{
            $response = $this->client->post('/cgi-bin/component/modify_wxa_server_domain?authorizer_access_token=' . $component_access_token, [
                'body' => json_encode([
                    'action' => $action,
                    'wxa_server_domain' => $wxa_server_domain,
                    'is_modify_published_together' => $is_modify_published_together
                ], JSON_UNESCAPED_UNICODE),
            ]);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode) && $result->errcode != WechatCode::SUCCESS){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }
        return $result;
    }

    // 配置小程序服务器域名
    // https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/domain-management/modifyServerDomain.html
    public function modifyServerDomain($authorizer_access_token, $action, $params = [])
    {
        $result = false;
        try{
            $response = $this->client->post('/wxa/modify_domain?access_token=' . $authorizer_access_token, [
                'body' => json_encode(array_merge([
                    'action' => $action
                ], $params), JSON_UNESCAPED_UNICODE),
            ]);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode) && $result->errcode != WechatCode::SUCCESS){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }
        return $result;
    }

    // 添加小程序类目
    // https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/category-management/addCategory.html
    public function addCategory($authorizer_access_token, array $categories = [], &$error = '')
    {
        $result = false;
        try{
            $response = $this->client->post('/cgi-bin/wxopen/addcategory?access_token=' . $authorizer_access_token, [
                'body' => json_encode([
                    'categories' => $categories
                ], JSON_UNESCAPED_UNICODE),
            ]);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode) && $result->errcode != WechatCode::SUCCESS){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }
        return $result;
    }

    // 设置小程序用户隐私保护指引
    // https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/privacy-management/setPrivacySetting.html
    public function setPrivacySetting($authorizer_access_token, $owner_setting, $privacy_ver = null, $setting_list = [], $sdk_privacy_info_list = null, &$error = '')
    {
        $params = [
            // 收集方（开发者）信息配置
            'owner_setting' => $owner_setting,
            // 要收集的用户信息配置，可选择的用户信息类型参考下方详情。当privacy_ver传2或者不传时，setting_list是必填；当privacy_ver传1时，该参数不可传，否则会报错
            'setting_list' => $setting_list
        ];
        // 用户隐私保护指引的版本，1表示现网版本；2表示开发版。默认是2开发版。
        if (!is_null($privacy_ver)) $params['privacy_ver'] = $privacy_ver;

        if (isset($params['privacy_ver']) && $params['privacy_ver'] == 1){
            unset($params['setting_list']);
        }
        if (!is_null($sdk_privacy_info_list)) $params['sdk_privacy_info_list'] = $sdk_privacy_info_list;
        $result = false;
        try{
            $response = $this->client->post('/cgi-bin/component/setprivacysetting?access_token=' . $authorizer_access_token, [
                'body' => json_encode($params, JSON_UNESCAPED_UNICODE),
            ]);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode) && $result->errcode != WechatCode::SUCCESS){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }
        return $result;
    }

    // 申请地理位置接口
    // https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/privacy-api-management/applyPrivacyInterface.html
    public function applyPrivacyInterface($authorizer_access_token, $api_name, $content, $params = [])
    {
        $result = false;
        try{
            $response = $this->client->post('/cgi-bin/wxopen/addcategory?access_token=' . $authorizer_access_token, [
                'body' => json_encode(array_merge([
                    'api_name' => $api_name,
                    'content' => $content,
                ], $params), JSON_UNESCAPED_UNICODE),
            ]);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode) && $result->errcode != WechatCode::SUCCESS){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }
        return $result;
    }

    // 查询小程序版本信息
    // https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/getVersionInfo.html
    public function getVersionInfo($authorizer_access_token, &$error = '')
    {
        $result = false;
        try{
            $response = $this->client->post('/wxa/getversioninfo?access_token=' . $authorizer_access_token);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode) && $result->errcode != WechatCode::SUCCESS){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }
        return $result;
    }

    // 查询最新一次审核单状态
    // https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/getLatestAuditStatus.html
    public function getLatestAuditStatus($authorizer_access_token, &$error = '')
    {
        $result = false;
        try{
            $response = $this->client->get('/wxa/get_latest_auditstatus?access_token=' . $authorizer_access_token);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode) && $result->errcode != WechatCode::SUCCESS){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }
        return $result;
    }

    // 上传代码并生成体验版
    // https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/commit.html
    public function commit($authorizer_access_token, $params, &$error = '')
    {
        $result = false;
        try{
            $response = $this->client->post('/wxa/commit?access_token=' . $authorizer_access_token, [
                'body' => json_encode(array_merge([
                    'template_id' => '',
                    'ext_json' => '',
                    'user_version' => '',
                    'user_desc' => '',
                ], $params), JSON_UNESCAPED_UNICODE),
            ]);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode) && $result->errcode != WechatCode::SUCCESS){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }
        return $result;
    }

    // 获取体验版二维码
    // https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/getTrialQRCode.html
    public function getTrialQRCode($authorizer_access_token, $path = '', &$error = '')
    {
        $result = false;
        try{
            $response = $this->client->get('/wxa/get_qrcode?access_token=' . $authorizer_access_token, [
                'query' => [
                    'path' => $path,
                ]
            ]);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode) && $result->errcode != WechatCode::SUCCESS){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }
        return $result;
    }

    // 提交代码审核
    // https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/miniprogram-management/code-management/submitAudit.html
    public function submitAudit($authorizer_access_token, $params, &$error = '')
    {
        $result = false;
        try{
            $response = $this->client->post('/wxa/submit_audit?access_token=' . $authorizer_access_token, [
                'body' => json_encode(array_merge([
                    'item_list' => [],
                    'feedback_info' => '',
                    'version_desc' => '',
                    // 还有其它参数，实际业务待定~
                ], $params), JSON_UNESCAPED_UNICODE),
            ]);

            // 验证是否存在异常
            $result = json_decode($response->getBody()->getContents());
            if (isset($result->errcode) && $result->errcode != WechatCode::SUCCESS){
                $error = $result->errmsg ?? $result->errcode;
            }
        }catch (\Exception $e){
            $error = $e->getMessage();
        }
        return $result;
    }
}
