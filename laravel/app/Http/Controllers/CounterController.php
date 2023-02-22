<?php

namespace App\Http\Controllers;

use App\Models\Counters;
use Error;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CounterController extends Controller
{
    /**
     * 获取todo list
     * @return JsonResponse
     */
    public function getCount()
    {
        try {
            $data = (new Counters)->find(1);
            if ($data == null) {
                $count = 0;
            }else {
                $count = $data["count"];
            }
            $res = [
                "code" => 0,
                "data" =>  $count
            ];
            Log::info('getCount rsp: '.json_encode($res));
            return response()->json($res);
        } catch (Error $e) {
            $res = [
                "code" => -1,
                "data" => [],
                "errorMsg" => ("查询计数异常" . $e->getMessage())
            ];
            Log::info('getCount rsp: '.json_encode($res));
            return response()->json($res);
        }
    }


    /**
     * 根据id查询todo数据
     * @param $action `string` 类型，枚举值，等于 `"inc"` 时，表示计数加一；等于 `"reset"` 时，表示计数重置（清零）
     * @return JsonResponse
     */
    public function updateCount()
    {
        try {
            $action = request()->input('action');
            if ($action == "inc") {
                $data = (new Counters)->find(1);
                if ($data == null) {
                    $count = 1;
                }else {
                    $count = $data["count"] + 1;
                }

                $counters = new Counters;
                $counters->updateOrCreate(['id' => 1], ["count" => $count]);
            }else if ($action == "clear") {
                Counters::destroy(1);
                $count = 0;
            }else {
                throw new \Exception('参数action错误');
            }

            $res = [
                "code" => 0,
                "data" =>  $count
            ];
            Log::info('updateCount rsp: '.json_encode($res));
            return response()->json($res);
        } catch (Exception $e) {
            $res = [
                "code" => -1,
                "data" => [],
                "errorMsg" => ("更新计数异常" . $e->getMessage())
            ];
            Log::info('updateCount rsp: '.json_encode($res));
            return response()->json($res);
        }
    }
}
