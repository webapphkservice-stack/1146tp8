<?php
namespace app;

use app\common\exception\BaseException;
use think\exception\Handle;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;

/**
 * 应用异常处理类（第 1 批改造：API 异常统一输出 JSON）
 */
class ExceptionHandle extends Handle
{
    public function render($request, \Throwable $e): Response
    {
        // 控制器内部抛出的 HTTP 响应（renderError / throwError）原样返回
        if ($e instanceof HttpResponseException) {
            return parent::render($request, $e);
        }
        // 业务异常：按 code 输出（code=-1 表示登录失效，前端跳登录页）
        if ($e instanceof BaseException) {
            return json(['code' => $e->getCode(), 'msg' => $e->getMessage(), 'data' => []]);
        }
        // 参数校验异常：输出 JSON
        if ($e instanceof ValidateException) {
            return json(['code' => 0, 'msg' => $e->getMessage(), 'data' => []]);
        }
        return parent::render($request, $e);
    }
}
