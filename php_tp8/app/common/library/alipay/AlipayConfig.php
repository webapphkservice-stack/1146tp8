<?php
namespace app\common\library\alipay;

use think\facade\Config;

/**
 * 支付宝配置（移植自 php_my common/library/Aliyun/AliPay/AlipayConfig）
 * TP8 暂仅从 config/alipay.php 读取，真实 aop SDK 未接入。
 */
class AlipayConfig
{
    public static function get($wxappId = null)
    {
        $config = Config::get('alipay', []);
        return $config ?: [
            'default' => 'alipay',
            'engine'  => [
                'alipay' => [
                    'app_id'         => '',
                    'sign_type'      => 'RSA2',
                    'private_key'    => '',
                    'public_key'     => '',
                    'alipay_public_key' => '',
                ],
            ],
        ];
    }
}
