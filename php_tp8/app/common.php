<?php
// 应用公共文件

if (!function_exists('pad_left')) {
    /**
     * 数字左侧补零（yoshop TP5 助手函数，TP8 迁移补全）
     * @param int|string $num
     * @param int $length
     * @return string
     */
    function pad_left($num, $length = 2)
    {
        return str_pad((string) $num, $length, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('time_text')) {
    /**
     * unix timestamp (秒级 int) → 人类可读日期字符串
     *
     * 用于 store / admin 后台模板里直接 <?= time_text($item['create_time']) ?> 输出，
     * 避免 <?= $item['create_time'] ?> 裸输出 10 位 unix timestamp。
     *
     * @param int|string|null $ts     秒级 unix timestamp；0/空/null 时返回 "--"
     * @param string          $format date() 格式，默认 "Y-m-d H:i:s"
     * @return string
     */
    function time_text($ts, $format = 'Y-m-d H:i:s')
    {
        if ($ts === null || $ts === '' || $ts === 0 || $ts === '0') {
            return '--';
        }
        $ts = (int) $ts;
        if ($ts <= 0) {
            return '--';
        }
        return date($format, $ts);
    }
}
