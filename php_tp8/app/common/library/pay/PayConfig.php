<?php
namespace app\common\library\pay;

use think\facade\Db;

/**
 * 支付配置统一读取入口（P0：真实支付接入）
 *
 * 设计要点：
 * 1. 不依赖全局 wxapp_id（notify 回调场景下 wxapp_id 未注入），所有方法显式传入 $wxappId。
 * 2. 数据源优先级：setting 表（后台可配置） > wxapp 表（小程序主配置） > config/pay.php（兜底默认）。
 * 3. 零 SDK 依赖：微信/支付宝/Stripe 全部走原生 curl + openssl，与 php_my 的 library 实现对齐。
 */
class PayConfig
{
    /**
     * 微信支付配置
     * @param int $wxappId
     * @return array{app_id:string,mchid:string,apikey:string,app_app_id:string,trade_type:string,notify_url:string}
     */
    public static function wechat($wxappId)
    {
        $wxapp = self::wxappRow($wxappId);
        $extra = self::settingRow('wechat_pay', $wxappId);
        $default = config('pay.wechat', []);
        return [
            // APP 支付（RN App）需开放平台 appid；未单独配置时回退小程序 app_id
            'app_id'      => $extra['app_app_id'] ?? ($wxapp['app_id'] ?? ''),
            // JSAPI（小程序/公众号）使用小程序 appid
            'mp_app_id'   => $wxapp['app_id'] ?? '',
            'mchid'       => $extra['mchid'] ?? ($wxapp['mchid'] ?? ''),
            'apikey'      => $extra['apikey'] ?? ($wxapp['apikey'] ?? ''),
            'trade_type'  => $extra['trade_type'] ?? ($default['trade_type'] ?? 'APP'),
            'notify_url'  => self::notifyUrl('order'),
        ];
    }

    /**
     * 微信支付 v3 配置（官方 SDK wechatpay/wechatpay）
     *
     * 优先级：setting 表 wechat_pay.v3_* > config/pay.php wechat_v3（来自 env）> wxapp 表（仅 mchid/app_id 兜底）
     * 证书类参数支持三种写法：绝对路径、file:// 路径、直接 PEM 文本（由 WxPayV3 统一归一化）。
     *
     * @param int $wxappId
     * @return array
     */
    public static function wechatV3($wxappId)
    {
        $wxapp   = self::wxappRow($wxappId);
        $extra   = self::settingRow('wechat_pay', $wxappId);
        $default = config('pay.wechat_v3', []);
        return [
            'app_id'             => self::pick($extra['app_app_id'] ?? null, $wxapp['app_id'] ?? ''),
            'mp_app_id'          => $wxapp['app_id'] ?? '',
            'mchid'              => self::pick($extra['v3_mchid'] ?? null, $default['mchid'] ?? null, $wxapp['mchid'] ?? ''),
            'serial_no'          => self::pick($extra['v3_serial_no'] ?? null, $default['serial_no'] ?? null),
            'private_key'        => self::pick($extra['v3_private_key'] ?? null, $default['private_key'] ?? null),
            'platform_cert'      => self::pick($extra['v3_platform_cert'] ?? null, $default['platform_cert'] ?? null),
            'platform_pubkey'    => self::pick($extra['v3_platform_pubkey'] ?? null, $default['platform_pubkey'] ?? null),
            'platform_pubkey_id' => self::pick($extra['v3_platform_pubkey_id'] ?? null, $default['platform_pubkey_id'] ?? null),
            'apiv3_key'          => self::pick($extra['v3_apiv3_key'] ?? null, $default['apiv3_key'] ?? null),
            'notify_url'         => self::notifyUrl('order'),
        ];
    }

    /**
     * 微信支付 v3 是否可用（商户证书 + 平台证书/公钥 + APIv3 密钥齐备）
     * @param int $wxappId
     * @return bool
     */
    public static function wechatV3Ready($wxappId)
    {
        $c = self::wechatV3($wxappId);
        return !empty($c['mchid']) && !empty($c['serial_no'])
            && !empty($c['private_key']) && !empty($c['apiv3_key'])
            && (!empty($c['platform_cert']) || !empty($c['platform_pubkey']));
    }

    /**
     * 支付宝 EasySDK 配置
     * @param int $wxappId
     * @return array
     */
    public static function alipaySdk($wxappId)
    {
        $base = self::alipay($wxappId);
        $sdk  = config('pay.alipay_sdk', []);
        return [
            'protocol'           => $sdk['protocol'] ?? 'https',
            'gatewayHost'        => $sdk['gateway_host'] ?? 'openapi.alipay.com',
            'appId'              => $base['app_id'],
            'signType'           => $base['sign_type'] ?: ($sdk['sign_type'] ?? 'RSA2'),
            'alipayPublicKey'    => $base['public_key'],
            'merchantPrivateKey' => $base['private_key'],
            'notifyUrl'          => $base['notify_url'],
        ];
    }

    /**
     * 取第一个非空值（用于多级配置回退）
     * @param mixed ...$values
     * @return mixed
     */
    private static function pick(...$values)
    {
        foreach ($values as $v) {
            if ($v !== null && $v !== '') {
                return $v;
            }
        }
        return '';
    }

    /**
     * 支付宝配置
     * @param int $wxappId
     * @return array{app_id:string,private_key:string,public_key:string,sign_type:string,notify_url:string}
     */
    public static function alipay($wxappId)
    {
        $wxapp = self::wxappRow($wxappId);
        $extra = self::settingRow('alipay', $wxappId);
        return [
            'app_id'      => $extra['app_id'] ?? ($wxapp['alipay_app_id'] ?? ''),
            'private_key' => $extra['private_key'] ?? ($wxapp['alipay_private_key'] ?? ''),
            'public_key'  => $extra['public_key'] ?? ($wxapp['alipay_public_key'] ?? ''),
            'sign_type'   => $extra['sign_type'] ?? ($wxapp['alipay_sign_type'] ?? 'RSA2'),
            'notify_url'  => self::notifyUrl('alipay'),
        ];
    }

    /**
     * Stripe 配置
     * @param int $wxappId
     * @return array{secret_key:string,publishable_key:string,currency:string,webhook_secret:string,merchant_name:string,api_version:string}
     */
    public static function stripe($wxappId)
    {
        $default = config('pay.stripe', []);
        $extra = self::settingRow('stripe', $wxappId);
        return [
            'secret_key'      => $extra['secret_key'] ?? ($default['secret_key'] ?? ''),
            'publishable_key' => $extra['publishable_key'] ?? ($default['publishable_key'] ?? ''),
            'currency'        => strtolower($extra['currency'] ?? ($default['currency'] ?? 'myr')),
            'webhook_secret'  => $extra['webhook_secret'] ?? ($default['webhook_secret'] ?? ''),
            'merchant_name'   => $extra['merchant_name'] ?? ($default['merchant_name'] ?? 'Shop'),
            'api_version'     => $default['api_version'] ?? '2023-10-16',
        ];
    }

    /**
     * 交易设置（未支付自动关闭 / 自动确认收货 / 售后期），与 php_my Setting::getAll 默认值一致
     * @param int $wxappId
     * @return array{close_days:int,receive_days:int,refund_days:int}
     */
    public static function trade($wxappId)
    {
        $row = self::settingRow('trade', $wxappId);
        $order = $row['order'] ?? [];
        return [
            'close_days'   => (int)($order['close_days'] ?? 3),
            'receive_days' => (int)($order['receive_days'] ?? 10),
            'refund_days'  => (int)($order['refund_days'] ?? 7),
        ];
    }

    /**
     * 支付异步通知地址（网关回调需公网可访问）
     * @param string $channel order|alipay|stripe
     * @return string
     */
    public static function notifyUrl($channel = 'order')
    {
        $host = rtrim((string)env('APP_HOST', ''), '/');
        $map = [
            'order'  => '/api/notify/order',
            'alipay' => '/api/notify/alipay',
            'stripe' => '/api/notify/stripe',
        ];
        $path = $map[$channel] ?? $map['order'];
        $override = config("pay.notify_url.{$channel}", '');
        if ($override) {
            return $override;
        }
        return $host . $path;
    }

    /**
     * 判断某渠道是否已完成配置（用于下单前的前置校验，给出明确报错）
     * @param string $channel wechat|alipay|stripe
     * @param int    $wxappId
     * @return bool
     */
    public static function isReady($channel, $wxappId)
    {
        switch ($channel) {
            case 'wechat':
                // v3（官方 SDK）配置齐备即视为可用；否则回退校验 v2（app_id/mchid/apikey）
                if (self::wechatV3Ready($wxappId)) {
                    return true;
                }
                $c = self::wechat($wxappId);
                return !empty($c['app_id']) && !empty($c['mchid']) && !empty($c['apikey']);
            case 'alipay':
                $c = self::alipay($wxappId);
                return !empty($c['app_id']) && !empty($c['private_key']) && !empty($c['public_key']);
            case 'stripe':
                $c = self::stripe($wxappId);
                return !empty($c['secret_key']);
        }
        return false;
    }

    // -------------------- 内部：原始数据读取 --------------------

    /**
     * 读取 wxapp 主配置行（小程序/商户号/支付密钥/支付宝密钥）
     * @param int $wxappId
     * @return array
     */
    private static function wxappRow($wxappId)
    {
        static $cache = [];
        if (!isset($cache[$wxappId])) {
            $cache[$wxappId] = Db::name('wxapp')->where('wxapp_id', $wxappId)->find() ?: [];
        }
        return $cache[$wxappId];
    }

    /**
     * 读取 setting 表单项（后台可覆盖配置）
     * @param string $key
     * @param int    $wxappId
     * @return array
     */
    private static function settingRow($key, $wxappId)
    {
        static $cache = [];
        $cacheKey = $wxappId . ':' . $key;
        if (!isset($cache[$cacheKey])) {
            $values = Db::name('setting')
                ->where('key', $key)
                ->where('wxapp_id', $wxappId)
                ->value('values');
            $decoded = $values ? json_decode($values, true) : [];
            $cache[$cacheKey] = is_array($decoded) ? $decoded : [];
        }
        return $cache[$cacheKey];
    }
}
