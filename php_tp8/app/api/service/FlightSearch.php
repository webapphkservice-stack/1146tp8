<?php

namespace app\api\service;

use app\common\library\FlightTfs;
use app\common\model\Setting;

/**
 * 调用 Bright Data 查询 Google Flights，并解析结果
 * Class FlightSearch
 * @package app\api\service
 */
class FlightSearch
{
    /** @var array Bright Data 配置 */
    private $config = [];

    public function __construct($wxappId = 10001)
    {
        $this->config = Setting::getItem('brightdata') ?: [];
    }

    /**
     * 是否已启用（需要开启开关 + API Key + Zone）
     * @return bool
     */
    public function isEnabled()
    {
        return !empty($this->config['enable'])
            && !empty($this->config['api_key'])
            && !empty($this->config['zone']);
    }

    /**
     * 获取配置
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 搜索航班
     * @param array $params origin,destination,departure_date,return_date,trip_type,cabin,adult,currency
     * @return array ['flights'=>[...], 'best_price'=>float|null]
     * @throws \Exception
     */
    public function search($params)
    {
        $url = $this->buildUrl($params);
        $html = $this->request($url);
        return $this->parse($html);
    }

    /**
     * 构造 Google Flights 搜索地址
     */
    private function buildUrl($params)
    {
        $tfs = FlightTfs::build(
            $params['origin'],
            $params['destination'],
            $params['departure_date'],
            !empty($params['return_date']) ? $params['return_date'] : null,
            (int)$params['cabin'],
            (int)$params['adult']
        );
        $currency = $this->config['currency'] ?: 'CNY';
        $country = $this->config['country'] ?: 'cn';
        $lang = $this->config['lang'] ?: 'zh-CN';
        return sprintf(
            'https://www.google.com/travel/flights/search?tfs=%s&hl=%s&gl=%s&currency=%s',
            $tfs, $lang, $country, $currency
        );
    }

    /**
     * 请求 Bright Data
     * @param string $url
     * @return string
     * @throws \Exception
     */
    private function request($url)
    {
        $ch = curl_init('https://api.brightdata.com/request');
        $body = json_encode([
            'zone'        => $this->config['zone'],
            'url'         => $url,
            'format'      => 'raw',
            'data_format' => 'html',
        ]);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->config['api_key'],
            ],
            CURLOPT_TIMEOUT        => (int)($this->config['timeout'] ?: 30),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
        ]);
        $resp = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            throw new \Exception('Bright Data 请求失败: ' . $error);
        }
        if ($httpCode != 200) {
            throw new \Exception('Bright Data 返回错误码 ' . $httpCode . '：' . mb_substr($resp, 0, 200));
        }
        return $resp;
    }

    /**
     * 解析 Google Flights 返回的 HTML（best-effort）
     * Google Flights 的每条航班结果都带有 aria-label 无障碍标签，从中提取结构化字段。
     */
    private function parse($html)
    {
        $flights = [];
        if (preg_match_all('/aria-label=["\'](.*?)["\']/is', $html, $matches)) {
            foreach ($matches[1] as $label) {
                $flight = $this->parseLabel($label);
                if ($flight) {
                    $flights[] = $flight;
                }
            }
        }
        $flights = $this->uniqueFlights($flights);

        $bestPrice = null;
        foreach ($flights as $f) {
            if (isset($f['price']) && ($bestPrice === null || $f['price'] < $bestPrice)) {
                $bestPrice = $f['price'];
            }
        }
        return ['flights' => $flights, 'best_price' => $bestPrice];
    }

    /**
     * 从单条 aria-label 解析航班信息
     */
    private function parseLabel($label)
    {
        // 价格：支持 $ ¥ € £ 以及 “xxx 元”
        if (!preg_match('/(?:[$¥€£]|RMB|CNY|USD)\s*([0-9,]+(\.\d+)?)/iu', $label, $pm)
            && !preg_match('/([0-9,]+(\.\d+)?)\s*元/iu', $label, $pm)) {
            return null;
        }
        $price = (float)str_replace(',', '', $pm[1]);

        // 航班号：两字母 + 数字
        $flightNo = '';
        if (preg_match('/([A-Z]{2})\s?(\d{1,4})/', $label, $fm)) {
            $flightNo = $fm[1] . $fm[2];
        }
        // 航司名（航班号之前的中文/英文）
        $airline = '';
        if ($flightNo && preg_match('/([\x{4e00}-\x{9fa5}A-Za-z&.\s]{2,16})?' . preg_quote($flightNo, '/') . '/u', $label, $am)) {
            $airline = trim($am[1]);
        }

        // 起降时间
        $times = [];
        if (preg_match_all('/\b(\d{1,2}:\d{2}\s*[AP]M)\b/i', $label, $tm)) {
            $times = $tm[1];
        } elseif (preg_match_all('/(\d{1,2}:\d{2})/', $label, $tm)) {
            $times = $tm[1];
        }
        $departureTime = $times[0] ?? '';
        $arrivalTime = $times[1] ?? '';

        // 时长
        $duration = '';
        if (preg_match('/(\d+\s*小时\s*\d+\s*分|\d+\s*h\s*\d+\s*m|\d+\s*小时|\d+\s*小时\d+\s*分钟|\d+\s*小时\s*\d+\s*分钟)/iu', $label, $dm)) {
            $duration = trim($dm[1]);
        }

        // 经停
        $stops = 0;
        if (preg_match('/直达|直飞|nonstop|无中转/i', $label)) {
            $stops = 0;
        } elseif (preg_match('/(\d+)\s*次?中转|(\d+)\s*stops?/i', $label, $sm)) {
            $stops = (int)$sm[1];
        } elseif (preg_match('/中转|经停|stop/i', $label)) {
            $stops = 1;
        }

        return [
            'airline'        => $airline,
            'flight_no'      => $flightNo,
            'departure_time' => $departureTime,
            'arrival_time'   => $arrivalTime,
            'duration'       => $duration,
            'stops'          => $stops,
            'cabin'          => '',
            'price'          => $price,
            'extensions'     => [],
        ];
    }

    /**
     * 航班去重
     */
    private function uniqueFlights($flights)
    {
        $seen = [];
        $out = [];
        foreach ($flights as $f) {
            $key = ($f['flight_no'] ?: $f['airline']) . '|' . ($f['price'] ?? 0) . '|' . ($f['departure_time'] ?? '');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $f;
        }
        return $out;
    }
}
