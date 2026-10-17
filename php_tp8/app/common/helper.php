<?php
/**
 * 全局助手函数（TP8 通过 composer.json 的 autoload.files 注册）。
 * 生产 php_my 中有若干全局函数（base_url / getGuidV4 / curl / log_write 等），
 * 为减少移植改动，这里统一定义。
 */

use think\facade\Log;

if (!function_exists('base_url')) {
    /**
     * 站点域名（用于拼接上传文件/头像完整 URL）
     * @return string
     */
    function base_url()
    {
        return rtrim(env('APP_HOST', ''), '/');
    }
}

if (!function_exists('array_merge_multiple')) {
    /**
     * 递归合并多维数组（移植自 php_my application/common.php）
     * @param $array1
     * @param $array2
     * @return array
     */
    function array_merge_multiple($array1, $array2)
    {
        $merge = $array1 + $array2;
        $data = [];
        foreach ($merge as $key => $val) {
            if (
                isset($array1[$key])
                && is_array($array1[$key])
                && isset($array2[$key])
                && is_array($array2[$key])
            ) {
                $data[$key] = array_merge_multiple($array1[$key], $array2[$key]);
            } else {
                $data[$key] = isset($array2[$key]) ? $array2[$key] : $array1[$key];
            }
        }
        return $data;
    }
}

if (!function_exists('getGuidV4')) {
    /**
     * 生成 UUID v4（移植自 php_my 旧 helper）
     * @return string
     */
    function getGuidV4()
    {
        $data = function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('str_random')) {
    function str_random($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $str;
    }
}

if (!function_exists('curl')) {
    /**
     * 简易 curl 请求（移植自 php_my 旧 helper）
     * @param string $url
     * @param array|string $params
     * @param bool $isPost
     * @param array $header
     * @return mixed
     */
    function curl($url, $params = [], $isPost = false, $header = [])
    {
        $data = is_array($params) ? http_build_query($params) : $params;
        $ch = curl_init();
        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $data;
        }
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        // PHP 8.5 起 curl_close() 已废弃（PHP 8.0 之后无实际作用），调用会触发 deprecated 异常
        $ch = null;
        return $result;
    }
}

if (!function_exists('yoshop_hash')) {
    /**
     * 后台用户密码加密（移植自 php_my application/common.php）
     * 必须与 php_my 保持一致，否则旧库中的商家账号无法登录
     * @param string $password 明文密码
     * @return string
     */
    function yoshop_hash($password)
    {
        return md5(md5($password) . 'yoshop_salt_SmTRx');
    }
}

if (!function_exists('log_write')) {
    /**
     * 记录错误日志（移植自 php_my 旧 helper）
     * @param string|\Stringable $msg
     */
    function log_write($msg)
    {
        try {
            Log::write($msg, 'error');
        } catch (\Throwable $e) {
            error_log((string) $msg);
        }
    }
}

if (!function_exists('toUnderScore')) {
    /**
     * 驼峰转下划线（商家后台路由解析用，移植自 TP5 store 公共函数）
     * 例：StoreUser => store_user；goods.Spec => goods.spec（点号保留，用于控制器分组）
     * @param string $str
     * @return string
     */
    function toUnderScore($str)
    {
        $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
            return '_' . strtolower($matchs[0]);
        }, (string) $str);
        return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
    }
}

if (!function_exists('get_version')) {
    /**
     * 系统版本号（读取 version.json，移植自 TP5）
     * @return string
     */
    function get_version()
    {
        static $version = null;
        if ($version !== null) {
            return $version;
        }
        $file = root_path() . 'version.json';
        if (!is_file($file)) {
            $file = dirname(root_path()) . '/version.json';
        }
        if (is_file($file)) {
            $data = json_decode((string) file_get_contents($file), true);
            if (is_array($data) && !empty($data['version'])) {
                return $version = $data['version'];
            }
        }
        return $version = '1.0.0';
    }
}
