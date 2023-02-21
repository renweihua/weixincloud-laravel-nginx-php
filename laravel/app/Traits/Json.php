<?php

namespace App\Traits;

use App\Constants\HttpStatus;

trait Json
{
    protected $http_status = HttpStatus::SUCCESS;

    public function setHttpCode($http_code){
        $this->http_status = $http_code;
    }

    public function successJson($data = [], $msg = 'success', $other = [], array $header = [])
    {
        return $this->myAjaxReturn(array_merge(['data' => $data, 'msg' => $msg], $other), $header);
    }

    public function errorJson($msg = 'error', $http_status = 400, $data = [], $other = [], array $header = [])
    {
        $this->http_status = $http_status;
        return $this->myAjaxReturn(array_merge(['msg' => $msg, 'data' => $data], $other), $header);
    }

    private function myAjaxReturn($data, array $header = [])
    {
        $data['data'] = $data['data'] ?? [];
        $data['msg'] = $data['msg'] ?? (empty($data['status']) ? '' : 'success');
        $data['execution_time'] = microtime(true) - LARAVEL_START;
        $data['http_status'] = $this->http_status;

        // JSON_UNESCAPED_UNICODE 256：Json不要编码Unicode
        return response()->json($data, $data['http_status'], $header, 256);
    }
}
