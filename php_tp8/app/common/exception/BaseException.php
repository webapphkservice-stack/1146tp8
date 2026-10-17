<?php
namespace app\common\exception;

/**
 * 业务异常（移植自 php_my）。
 * 约定：code 0 为普通业务错误；-1 表示登录失效（前端据此跳登录页）。
 */
class BaseException extends \Exception
{
    /**
     * @param array|string $params 可传 ['msg'=>..., 'code'=>...] 或纯字符串
     * @param int $code
     */
    public function __construct($params = [], $code = 0)
    {
        if (is_array($params)) {
            $message = $params['msg'] ?? '';
            $code = $params['code'] ?? $code;
        } else {
            $message = (string) $params;
        }
        parent::__construct($message, (int) $code);
    }
}
