<?php
namespace app\common\library;

/**
 * 通用工具类（移植自 php_my common/library/helper）
 */
class helper
{
    /**
     * JSON 解码（容忍非字符串）
     * @param string|mixed $json
     * @param bool $assoc
     * @return mixed
     */
    public static function jsonDecode($json, $assoc = true)
    {
        if (!is_string($json)) {
            return $json;
        }
        return json_decode($json, $assoc);
    }

    /**
     * JSON 编码
     * @param mixed $data
     * @return string
     */
    public static function jsonEncode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 打印变量（调试）
     * @param mixed $var
     */
    public static function dump($var)
    {
        if (function_exists('dump')) {
            dump($var);
        } else {
            var_dump($var);
        }
    }

    /**
     * 获取数组最大深度
     * @param array $array
     * @return int
     */
    public static function getArrayDepth($array)
    {
        $maxDepth = 1;
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = self::getArrayDepth($value) + 1;
                if ($depth > $maxDepth) {
                    $maxDepth = $depth;
                }
            }
        }
        return $maxDepth;
    }

    /**
     * 数组逆向排序（保持索引）
     * @param array $data
     * @return array
     */
    public static function arrayReverse($data)
    {
        return array_reverse($data, true);
    }

    /**
     * 重置数组索引（连续数字键）
     * @param array $data
     * @return array
     */
    public static function arrayValues($data)
    {
        return array_values($data);
    }

    /**
     * 数组合并（递归）
     * @param array ...$arrays
     * @return array
     */
    public static function arrayMerge(...$arrays)
    {
        return array_merge(...$arrays);
    }

    /**
     * CDN 地址（无 CDN 时返回原地址）
     * @param string $url
     * @return string
     */
    public static function cdnUrl($url)
    {
        return $url;
    }

    /**
     * 获取服务器 IP
     * @return string
     */
    public static function getServerIp()
    {
        return gethostbyname(gethostname());
    }

    // ==================== 金额/数组工具（移植自 php_my，供优惠券与积分分摊使用） ====================

    /**
     * 高精度数值转字符串（避免 bcmath 收到浮点/非字符串参数在 PHP 8.1+ 触发 deprecated）
     * @param mixed $value
     * @return string
     */
    private static function num($value)
    {
        if (is_float($value)) {
            // 用 10 位精度截断，避免浮点尾数（如 0.30000000000000004）进入 bc 运算
            return rtrim(rtrim(sprintf('%.10F', $value), '0'), '.') ?: '0';
        }
        if (is_int($value)) {
            return (string)$value;
        }
        $value = (string)$value;
        return $value === '' ? '0' : $value;
    }

    public static function getArrayColumn($source, $column)
    {
        $columnArr = [];
        foreach ($source as $item) {
            $columnArr[] = $item[$column];
        }
        return $columnArr;
    }

    public static function getArrayColumns($source, $columns)
    {
        $columnArr = [];
        foreach ($source as $item) {
            $temp = [];
            foreach ($columns as $index) {
                $temp[$index] = $item[$index];
            }
            $columnArr[] = $temp;
        }
        return $columnArr;
    }

    /**
     * 二维数组指定列求和（先乘 100 累加再除，规避浮点误差）
     */
    public static function getArrayColumnSum($array, $column)
    {
        $sum = 0;
        foreach ($array as $item) {
            $sum += (int)round($item[$column] * 100);
        }
        return $sum / 100;
    }

    public static function number2($number, $isMinimum = false, $minimum = 0.01)
    {
        if ($isMinimum) {
            $number = max($minimum, $number);
        }
        return sprintf('%.2f', $number);
    }

    public static function setDataAttribute(&$source, $defaultData, $isArray = false)
    {
        if (!$isArray) {
            $dataSource = [&$source];
        } else {
            $dataSource = &$source;
        }
        foreach ($dataSource as &$item) {
            foreach ($defaultData as $key => $value) {
                $item[$key] = $value;
            }
        }
        return $source;
    }

    public static function bcadd($left, $right, $scale = 2)
    {
        return \bcadd(self::num($left), self::num($right), $scale);
    }

    public static function bcsub($left, $right, $scale = 2)
    {
        return \bcsub(self::num($left), self::num($right), $scale);
    }

    public static function bcmul($left, $right, $scale = 2)
    {
        return \bcmul(self::num($left), self::num($right), $scale);
    }

    public static function bcdiv($left, $right, $scale = 2)
    {
        $right = self::num($right);
        if (bccomp($right, '0', 10) === 0) {
            return '0';
        }
        return \bcdiv(self::num($left), $right, $scale);
    }

    public static function bccomp($left, $right, $scale = 2)
    {
        return \bccomp(self::num($left), self::num($right), $scale);
    }

    /**
     * 二维数组按指定列排序（替代 php_my 的 array_sort 辅助函数）
     * @param array  $source
     * @param string $column
     * @param bool   $desc  true=降序
     * @return array
     */
    public static function arraySort($source, $column, $desc = false)
    {
        $values = [];
        foreach ($source as $key => $item) {
            $values[$key] = $item[$column];
        }
        if ($desc) {
            arsort($values, SORT_NUMERIC);
        } else {
            asort($values, SORT_NUMERIC);
        }
        $result = [];
        foreach (array_keys($values) as $key) {
            $result[$key] = $source[$key];
        }
        return $result;
    }
}
