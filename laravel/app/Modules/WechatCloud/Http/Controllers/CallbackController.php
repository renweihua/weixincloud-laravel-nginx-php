<?php

namespace App\Modules\WechatCloud\Http\Controllers;

use App\Constants\HttpStatus;
use App\libs\WechatCloud\ThirdPartyPlatformServer;
use App\Models\Wxtoken;
use App\Modules\WechatCloud\Http\Requests\AppIdRequest;
use App\Modules\WechatCloud\Http\Requests\AuthorizationRequest;
use App\Traits\Json;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Monolog\Handler\IFTTTHandler;

// 回调通知
class CallbackController extends Controller
{
    use Json;

    // 主服务端地址
    const MAIN_SERVER_URL = '';

    // 小程序审核回调
    const EVENT_APPLET_CHECK_CALLBACK_URL = '/storeapi/applet-check/callback';

    // 事件回调
    public function event($app_id, Request $request)
    {
        $xml = $request->getContent();
        $wx_xml = json_decode($xml, true);

        // 请求主服务端
        $client = new Client([
            'base_uri' => self::MAIN_SERVER_URL
        ]);
        $wx_xml['app_id'] = $app_id;
        // 避免抛出异常
        try{
            $response = $client->post(self::EVENT_APPLET_CHECK_CALLBACK_URL, [
                'form_params' => [
                    'wechat_xml' => $wx_xml,
                ],
            ]);
        }catch (\Exception $e){

        }
        // 微信要求返回 true
        return true;
    }
}
