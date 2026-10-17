<?php
namespace app\common\library\pay;

use app\common\exception\BaseException;
use think\facade\Log;

/**
 * 微信支付（原生实现，零 SDK 依赖）
 *
 * 移植自 php_my common/library/wechat/WxPay.php 的统一下单与签名算法，并针对
 * TP8 / RN App 场景做了以下调整：
 *  1. 支持 APP（开放平台）与 JSAPI（小程序）两种 trade_type：传 openid 走 JSAPI，否则 APP。
 *  2. 不再继承 WxBase（TP8 未迁移该基类），配置由 PayConfig 统一注入。
 *  3. notify_url 由 PayConfig::notifyUrl() 生成（旧版硬编码 notice.php，TP8 为 /api/notify/order）。
 *  4. 不调用 openssl_free_key（PHP 8 已废弃，会触发 deprecated）。
 *
 * 签名规则（v2 MD5）：字典序排序 -> k=v& 拼接 -> 末尾 &key=APIKEY -> MD5 -> 大写
 */
class WxPay
{
    /** 统一下单地址 */
    const UNIFIED_ORDER_URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    /** @var array app_id / mchid / apikey */
    private $config;

    /**
     * @param array $config 至少含 app_id、mchid、apikey（由 PayConfig::wechat() 提供）
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * 统一下单
     * @param string      $orderNo      商户订单号（回调时用它定位订单）
     * @param float       $totalFee     金额（元，内部换算为分）
     * @param string      $openid       JSAPI 必填；MWEB/APP 忽略
     * @param string      $body         商品描述（默认取订单号）
     * @param string|null $outTradeNo   实际提交给微信的商户单号（好友代付等需与 orderNo 区分时使用）
     * @param string      $tradeType    APP|JSAPI|MWEB
     * @param array       $sceneInfo    MWEB 场景信息：{payer_client_ip}
     * @return array APP:  {appid,partnerid,prepayid,package,noncestr,timestamp,sign}
     *               JSAPI:{appId,timeStamp,nonceStr,package,signType,paySign}
     *               MWEB: {mweb_url, out_trade_no, total_fee}（前端 window.location.href 跳转即可）
     * @throws BaseException
     */
    public function unifiedOrder($orderNo, $totalFee, $openid = '', $body = '', $outTradeNo = null, $tradeType = '', $sceneInfo = [])
    {
        $this->checkConfig();
        $time     = time();
        $nonceStr = md5($time . $orderNo . mt_rand(1000, 9999));
        // 未显式指定时：有 openid 走 JSAPI，否则走 APP（MWEB 需调用方显式指定）
        if ($tradeType === '') {
            $tradeType = $openid !== '' ? 'JSAPI' : 'APP';
        }
        if ($tradeType === 'JSAPI') {
            $appId = $this->config['mp_app_id'] ?: $this->config['app_id'];
        } else {
            $appId = $this->config['app_id'];
        }

        $params = [
            'appid'            => $appId,
            'attach'           => $orderNo, // 回调据此定位订单（微信原样返回）
            'body'             => $body !== '' ? $body : $orderNo,
            'mch_id'           => $this->config['mchid'],
            'nonce_str'        => $nonceStr,
            'notify_url'       => $this->config['notify_url'],
            'out_trade_no'     => $outTradeNo ?: $orderNo,
            'spbill_create_ip' => self::clientIp(),
            'total_fee'        => (int)round($totalFee * 100), // 单位：分
            'trade_type'       => $tradeType,
        ];
        if ($tradeType === 'JSAPI') {
            $params['openid'] = $openid;
        }
        // MWEB：需要 scene_info（payer_client_ip 已由 spbill_create_ip 提供，v2 里 scene_info 作为单独字段）
        if ($tradeType === 'MWEB') {
            $sceneInfoArr = [
                'payer_client_ip' => $sceneInfo['payer_client_ip'] ?? self::clientIp(),
                'h5_info'         => ['type' => 'Wap'],
            ];
            $params['scene_info'] = json_encode($sceneInfoArr, JSON_UNESCAPED_UNICODE);
        }
        $params['sign'] = $this->makeSign($params);

        Log::write('微信v2统一下单请求[' . $tradeType . ']：' . json_encode($params, JSON_UNESCAPED_UNICODE), 'info');

        $xml    = $this->postXml(self::UNIFIED_ORDER_URL, $params);
        $result = $this->fromXml($xml);

        if (empty($result)) {
            throw new BaseException(['msg' => '微信支付接口返回解析失败']);
        }
        if (($result['return_code'] ?? '') === 'FAIL') {
            throw new BaseException(['msg' => '微信支付接口：' . ($result['return_msg'] ?? '未知错误')]);
        }
        if (($result['result_code'] ?? '') === 'FAIL') {
            $msg = $result['err_code_des'] ?? ($result['err_code'] ?? '未知错误');
            throw new BaseException(['msg' => '微信支付接口：' . $msg]);
        }

        Log::write('微信v2统一下单结果：' . json_encode($result, JSON_UNESCAPED_UNICODE), 'info');

        // MWEB 返回 mweb_url，前端直接 location.href 跳转，无需二次签名
        if ($tradeType === 'MWEB') {
            if (empty($result['mweb_url'])) {
                throw new BaseException(['msg' => '微信 H5 支付下单失败：未返回 mweb_url']);
            }
            return [
                'mweb_url'     => $result['mweb_url'],
                'out_trade_no' => $outTradeNo ?: $orderNo,
                'total_fee'    => $totalFee,
            ];
        }

        if (empty($result['prepay_id'])) {
            throw new BaseException(['msg' => '微信支付下单失败：未返回 prepay_id']);
        }

        if ($tradeType === 'JSAPI') {
            return [
                'appId'     => $appId,
                'timeStamp' => (string)$time,
                'nonceStr'  => $nonceStr,
                'package'   => 'prepay_id=' . $result['prepay_id'],
                'signType'  => 'MD5',
                'paySign'   => $this->makePaySign($appId, $nonceStr, 'prepay_id=' . $result['prepay_id'], (string)$time),
                'prepay_id' => $result['prepay_id'],
            ];
        }
        // APP 支付：字段名为全小写，package 固定 Sign=WXPay
        $params = [
            'appid'     => $appId,
            'partnerid' => $this->config['mchid'],
            'prepayid'  => $result['prepay_id'],
            'package'   => 'Sign=WXPay',
            'noncestr'  => $nonceStr,
            'timestamp' => (string)$time,
        ];
        $params['sign'] = $this->makeSign($params);
        return $params;
    }

    /**
     * 微信支付 v2 异步通知验签（MD5）
     * @param array $data 解析后的通知数组（含 sign）
     * @return bool
     */
    public function verifyNotify(array $data)
    {
        if (empty($data['sign'])) {
            return false;
        }
        return hash_equals(strtoupper((string)$data['sign']), $this->makeSign($data));
    }

    // -------------------- 签名与 XML --------------------

    /**
     * 生成签名（sign 字段自身不参与签名）
     * @param array $values
     * @return string
     */
    public function makeSign(array $values)
    {
        ksort($values);
        $buff = '';
        foreach ($values as $k => $v) {
            if ($k !== 'sign' && $v !== '' && !is_array($v) && !is_null($v)) {
                $buff .= $k . '=' . $v . '&';
            }
        }
        $string = trim($buff, '&') . '&key=' . $this->config['apikey'];
        return strtoupper(md5($string));
    }

    /**
     * 生成 JSAPI 调起支付所需的 paySign
     * @param string $appId
     * @param string $nonceStr
     * @param string $package
     * @param string $timeStamp
     * @return string
     */
    private function makePaySign($appId, $nonceStr, $package, $timeStamp)
    {
        return $this->makeSign([
            'appId'     => $appId,
            'nonceStr'  => $nonceStr,
            'package'   => $package,
            'signType'  => 'MD5',
            'timeStamp' => $timeStamp,
        ]);
    }

    /**
     * 数组转 XML（数值不加 CDATA，其余加）
     * @param array $values
     * @return string
     */
    private function toXml(array $values)
    {
        $xml = '<xml>';
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
            }
        }
        return $xml . '</xml>';
    }

    /**
     * XML 转数组（禁止外部实体，防 XXE）
     * @param string $xml
     * @return array
     */
    private function fromXml($xml)
    {
        if (!is_string($xml) || trim($xml) === '') {
            return [];
        }
        // 注意：PHP 8.0 起 libxml_disable_entity_loader() 已废弃（外部实体默认即禁用），
        // 调用会触发 deprecated 异常；防 XXE 改用 LIBXML_NONET 即可。
        $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        return $obj ? json_decode(json_encode($obj), true) : [];
    }

    /**
     * 发送 XML POST 请求
     * @param string $url
     * @param array  $params
     * @return string
     * @throws BaseException
     */
    private function postXml($url, array $params)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $this->toXml($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['Content-Type: text/xml'],
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        // 注意：PHP 8.5 起 curl_close() 被标记为 deprecated（PHP 8.0 之后它已无实际作用，
        // 句柄会随变量释放自动关闭），调用会触发 deprecated 异常，故不再调用。
        $ch = null;
        if ($error) {
            throw new BaseException(['msg' => '微信支付请求网络错误：' . $error]);
        }
        return (string)$response;
    }

    /**
     * 校验必填配置
     * @throws BaseException
     */
    private function checkConfig()
    {
        foreach (['app_id', 'mchid', 'apikey'] as $key) {
            if (empty($this->config[$key])) {
                throw new BaseException(['msg' => "微信支付配置缺失：{$key}"]);
            }
        }
    }

    /**
     * 获取客户端 IP
     * @return string
     */
    private static function clientIp()
    {
        try {
            $ip = request()->ip();
        } catch (\Throwable $e) {
            $ip = '';
        }
        if (!$ip || $ip === '0.0.0.0') {
            $ip = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
        }
        return $ip;
    }
}
