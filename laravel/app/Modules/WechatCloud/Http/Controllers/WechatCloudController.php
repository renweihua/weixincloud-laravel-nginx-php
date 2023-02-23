<?php

namespace App\Modules\WechatCloud\Http\Controllers;

use App\Constants\HttpStatus;
use App\Models\Wxtoken;
use App\Modules\WechatCloud\Http\Requests\AppIdRequest;
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

    protected function cache($key, $callback, $force = false)
    {
        $lock_key = $key . ':lock';
        $lock = Cache::lock($lock_key, 10);

        $data = Cache::get($key);
        if (!$data || $force){
            $data = $callback();
            if (is_array($data)){
                Cache::put($key, $data, Carbon::now()->addSeconds($data['expires_in'])->addMinutes(-5));
            }
        }

        Cache::restoreLock($lock_key, $lock->owner());
        return $data;
    }

    // 第三方平台的component_access_token
    public function getComponentAccessToken(Request $request): JsonResponse
    {
        $result = [];
        $force = $request->input('force', false);
        $data = $this->cache('get-component-access-token', function() use (&$result){
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->server
            ]);
            $response = $client->get('/inner/component-access-token');

            $result = json_decode($response->getBody()->getContents());

            if ($response->getStatusCode() != HttpStatus::SUCCESS || $result->code != 0){
                return $this->errorJson($result->errorMsg, HttpStatus::BAD_REQUEST, [], [
                    // 兼容微信接口异常返回结构
                    'errcode' => $result->code,
                    'errmsg' => $result->errorMsg
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
                'expire_time' => strtotime(Carbon::now()->addSeconds($expires_in)->toString())
            ];
        }, $force);
        if (!is_array($data)){
            return $data;
        }
        // 兼容微信的结构
        $data['access_token'] = $data['component_access_token'];
        $data['expires_in'] = $data['expire_time'] - time();
        return $this->successJson($data, $result->errorMsg);
    }

    // 获取小程序的授权帐号令牌 authorizer_access_token
    public function getAuthorizerAccessToken(AppIdRequest $request): JsonResponse
    {
        $result = [];
        $app_id = $request->input('app_id');
        $force = $request->input('force', false);
        $data = $this->cache('get-authorizer-access-token:by:' . $app_id, function() use ($app_id, &$result){
            $client = new \GuzzleHttp\Client([
                'base_uri' => $this->server
            ]);
            $response = $client->get('/inner/authorizer-access-token?appid=' . $app_id);

            $result = json_decode($response->getBody()->getContents());

            if ($response->getStatusCode() != HttpStatus::SUCCESS || $result->code != 0){
                return $this->errorJson($result->errorMsg, HttpStatus::BAD_REQUEST, [], [
                    // 兼容微信接口异常返回结构
                    'errcode' => $result->code,
                    'errmsg' => $result->errorMsg
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
        }, $force);
        if (!is_array($data)){
            return $data;
        }
        // 兼容微信的结构
        $data['access_token'] = $data['authorizer_access_token'];
        $data['expires_in'] = $data['expire_time'] - time();
        return $this->successJson($data, $result->errorMsg);
    }
}
