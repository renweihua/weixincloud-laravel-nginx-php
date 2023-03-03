<?php

namespace App\Modules\WechatCloud\Http\Controllers;

use App\Constants\HttpStatus;
use App\libs\WechatCloud\ThirdPartyPlatformServer;
use App\Models\Wxtoken;
use App\Modules\WechatCloud\Http\Requests\AppIdRequest;
use App\Modules\WechatCloud\Http\Requests\AuthorizationRequest;
use App\Traits\Json;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class WechatCloudController extends Controller
{
    use Json;

    // 云管理服务的内网端口
    public $server = 'http://127.0.0.1:8081';

    protected function cache($key, $callback, $forced_update = 0)
    {
        $lock_key = $key . ':lock';
        $lock = Cache::lock($lock_key, 10);

        $data = Cache::get($key);
        if (!$data || $forced_update){
            $data = $callback();
            if (is_array($data)){
                // 缓存时长扣除5-10分钟，尽量避免过期token
                Cache::put($key, $data, $data['expire_time'] - time() - rand(5, 10) * 60);
            }
        }

        Cache::restoreLock($lock_key, $lock->owner());
        return $data;
    }

    // 第三方平台的component_access_token
    public function getComponentAccessToken(Request $request): JsonResponse
    {
        $result = [];
        $forced_update = $request->input('forced_update', 0);
        $data = $this->cache('get-component-access-token', function() use (&$result){
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->server
            ]);
            $response = $client->get('/inner/component-access-token');

            $result = json_decode($response->getBody()->getContents());

            if ($response->getStatusCode() != HttpStatus::SUCCESS || $result->code != 0){
                $error = $result->errorMsg . ' => ' . $result->data;
                return $this->errorJson($error, HttpStatus::BAD_REQUEST, [], [
                    // 兼容微信接口异常返回结构
                    'errcode' => $result->code,
                    'errmsg' => $error
                ]);
            }

            // 获取Token的过期时间：数据表未存储默认的过期时间，那么根据`更新时间 + 2小时`计算
            $expires_in = 7200;
            $component_appid = getenv('WX_APPID');
            $wxtoken = Wxtoken::where('appid', $component_appid)->first();
            if ($wxtoken){
                $expires_in = strtotime($wxtoken->updatetime) + $expires_in - time();
            }
            return [
                'component_appid' => $component_appid,
                'component_access_token' => $result->data->token,
                'expire_time' => strtotime(Carbon::now()->addSeconds($expires_in)->toDateTimeString())
            ];
        }, $forced_update);
        if (!is_array($data)){
            return $data;
        }
        // 兼容微信的结构
        $data['access_token'] = $data['component_access_token'];
        $data['expires_in'] = $data['expire_time'] - time();
        return $this->successJson($data, $result->errorMsg ?? 'ok');
    }

    // 获取小程序的授权帐号令牌 authorizer_access_token
    public function getAuthorizerAccessToken(AppIdRequest $request): JsonResponse
    {
        $result = [];
        $app_id = $request->input('app_id');

        $forced_update = $request->input('forced_update', 0);
        $data = $this->cache('get-authorizer-access-token:by:' . $app_id, function() use ($app_id, &$result){
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->server
            ]);
            $response = $client->get('/inner/authorizer-access-token?appid=' . $app_id);

            $result = json_decode($response->getBody()->getContents());

            if ($response->getStatusCode() != HttpStatus::SUCCESS || $result->code != 0){
                $error = $result->errorMsg . ' => ' . $result->data;
                return $this->errorJson($error, HttpStatus::BAD_REQUEST, [], [
                    // 兼容微信接口异常返回结构
                    'errcode' => $result->code,
                    'errmsg' => $error
                ]);
            }

            // 获取Token的过期时间：数据表未存储默认的过期时间，那么根据`更新时间 + 2小时`计算
            $expires_in = 7200;
            $component_appid = getenv('WX_APPID');
            $wxtoken = Wxtoken::where('appid', $component_appid)->first();
            if ($wxtoken){
                $expires_in = strtotime($wxtoken->updatetime) + $expires_in - time();
            }

            return [
                'authorizer_appid' => $app_id,
                'authorizer_access_token' => $result->data->token,
                'expire_time' => strtotime(Carbon::now()->addSeconds($expires_in)->toString())
            ];
        }, $forced_update);
        if (!is_array($data)){
            return $data;
        }
        // 兼容微信的结构
        $data['access_token'] = $data['authorizer_access_token'];
        $data['expires_in'] = $data['expire_time'] - time();
        return $this->successJson($data, $result->errorMsg ?? 'ok');
    }

    // 进入授权页面
    // /wechatcloud/authorization?pre_auth_code=&space_id=&redirect_url
    public function authorization(AuthorizationRequest $request)
    {
        $response = $this->getComponentAccessToken($request);
        $result = $response->getData();
        if ($result->http_status != HttpStatus::SUCCESS){
            throw new \Exception($result->msg, HttpStatus::BAD_REQUEST);
        }
        $component_data = $result->data;

        $thirdPartyPlatformServer = ThirdPartyPlatformServer::getInstance();

        $space_id = $request->input('space_id');

        Cache::put('space_redirect:' . $space_id, urldecode($request->input('redirect_url')), Carbon::now()->addHours(1));

        // 预授权码通过参数传递（如果在此处获取，IP一直变动，白名单异常）
        $pre_auth_code = $request->input('pre_auth_code');
        $callback_url = $thirdPartyPlatformServer->getCallbackUrl(
            $component_data->component_appid,
            $pre_auth_code,
            // 可自定义回调链接（域名必须不可变动）
            getenv('APP_URL') . '/wechatcloud/' . $space_id . '/callback'
        );

        // $callback_url = 'https://bbs.cnpscy.com';
        return view('wechatcloud::authorization', compact('callback_url'));
    }

    // 授权回调
    public function callback($space_id, Request $request)
    {
        // 跳转到实际项目
        $redirect_url = Cache::get('space_redirect:' . $space_id);
        // 切不可使用`http_build_query`，`auth_code`会存在特殊符被转义
        // 授权的code与过期时间
        $params = $request->only(['auth_code', 'expires_in']);
        $str = '';
        foreach ($params as $key =>$value){
            $str .= "&{$key}={$value}";
        }
        $str = trim($str, '&');

        if (strpos($redirect_url, '?') !== false) {
            $redirect_url = $redirect_url . '&' . $str;
        }else{
            $redirect_url = $redirect_url . '?' . $str;
        }
        header('location:' . $redirect_url);
        return;
        return view('wechatcloud::callback', compact('redirect_url'));
    }
}
