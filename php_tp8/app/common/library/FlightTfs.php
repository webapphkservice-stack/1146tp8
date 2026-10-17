<?php

namespace app\common\library;

/**
 * 生成 Google Flights 搜索链接所需的 tfs 参数
 * tfs 是 URL-safe base64 编码的 protobuf 结构（机场代码 + 日期）
 * 算法参考 github.com/NYCU-Chung/google-flights-search
 */
class FlightTfs
{
    // 字段 16 的固定填充（全部结果标记）
    const FIELD16 = "\x08\xff\xff\xff\xff\xff\xff\xff\xff\xff\x01";

    /**
     * 编码无符号 varint（LEB128）
     * @param int $n
     * @return string
     */
    private static function varint($n)
    {
        $n = (int)$n;
        if ($n < 0) {
            throw new \Exception('varint does not support negative values');
        }
        $buf = '';
        while ($n > 0x7F) {
            $buf .= chr(($n & 0x7F) | 0x80);
            $n >>= 7;
        }
        $buf .= chr($n);
        return $buf;
    }

    /**
     * 写入 varint 类型字段（wire type 0）
     */
    private static function fieldVarint($field, $value)
    {
        return self::varint(($field << 3) | 0) . self::varint($value);
    }

    /**
     * 写入 length-delimited 类型字段（wire type 2）
     */
    private static function fieldLen($field, $data)
    {
        return self::varint(($field << 3) | 2) . self::varint(strlen($data)) . $data;
    }

    /**
     * 机场实体编码（entity_type=1 机场）
     */
    private static function airportBytes($iata)
    {
        return self::fieldVarint(1, 1) . self::fieldLen(2, strtoupper($iata));
    }

    /**
     * 单程段编码
     */
    private static function flightDataBytes($date, $from, $to)
    {
        return self::fieldLen(2, $date)
            . self::fieldLen(13, self::airportBytes($from))
            . self::fieldLen(14, self::airportBytes($to));
    }

    /**
     * 生成 tfs 参数值
     * @param string $origin 出发地机场三字码
     * @param string $destination 目的地机场三字码
     * @param string $departureDate 出发日期 Y-m-d
     * @param string|null $returnDate 返程日期 Y-m-d（往返时传入）
     * @param int $seat 舱位 1经济 2超经 3商务 4头等
     * @param int $adults 成人数
     * @return string
     */
    public static function build($origin, $destination, $departureDate, $returnDate = null, $seat = 1, $adults = 1)
    {
        $info = self::fieldVarint(1, 28)
            . self::fieldVarint(2, 2)
            . self::fieldLen(3, self::flightDataBytes($departureDate, $origin, $destination));

        if ($returnDate) {
            $info .= self::fieldLen(4, self::flightDataBytes($returnDate, $destination, $origin));
        }

        for ($i = 0; $i < (int)$adults; $i++) {
            $info .= self::fieldVarint(8, 1);
        }

        $info .= self::fieldVarint(9, (int)$seat);
        $info .= self::fieldVarint(14, 1);
        $info .= self::fieldLen(16, self::FIELD16);
        $info .= self::fieldVarint(19, $returnDate ? 1 : 2);

        return rtrim(strtr(base64_encode($info), '+/', '-_'), '=');
    }
}
