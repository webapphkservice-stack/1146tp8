<?php
// store 应用公共函数库（对应 TP5 application/store/common.php）

use app\store\service\Auth;

if (!function_exists('build_csv')) {
    /**
     * 生成 CSV 内容（带 UTF-8 BOM，避免 Excel 打开乱码）
     * 对应 TP5 application/common.php 的 export_excel()，但改为「返回字符串」而非直接输出，
     * 以便 TP8 控制器用 response() 统一返回响应。
     * @param array $tileArray 表头
     * @param array $dataArray 行数据（ associative array，键对应表头）
     * @return string
     */
    function build_csv(array $tileArray = [], array $dataArray = [])
    {
        $fp = fopen('php://temp', 'r+');
        // BOM：转码防止乱码（比如微信昵称）
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        if ($tileArray) {
            fputcsv($fp, $tileArray);
        }
        foreach ($dataArray as $item) {
            fputcsv($fp, $item);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);
        return $csv;
    }
}

if (!function_exists('checkPrivilege')) {
    /**
     * 验证指定 url 是否有访问权限（后台模板中广泛使用）
     * @param string|array $url
     * @param bool         $strict 严格模式
     * @return bool
     */
    function checkPrivilege($url, $strict = true)
    {
        try {
            return Auth::getInstance()->checkPrivilege($url, $strict);
        } catch (\Exception $e) {
            return false;
        }
    }
}
