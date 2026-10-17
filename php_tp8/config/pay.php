<?php
// +----------------------------------------------------------------------
// | 支付配置（P0 真实支付接入）
// +----------------------------------------------------------------------
// 说明：
//  - 这里的配置是「兜底默认值」，运行时优先读取 setting 表（后台可配置）与 wxapp 表，
//    统一由 app\common\library\pay\PayConfig 聚合，业务代码不要直接读本文件。
//  - 三个渠道均为原生实现（curl + openssl），无需安装任何 SDK。
// +----------------------------------------------------------------------
return [
    // ------------------------------------------------------------------
    // 微信支付 v2（MD5 签名）：旧版兼容保留
    // 仅在未配置下方 wechat_v3 时兜底使用；生产请优先配置 v3。
    // ------------------------------------------------------------------
    'wechat' => [
        // APP = 开放平台移动应用支付（RN App 场景，默认）；JSAPI = 小程序/公众号支付（需 openid）
        'trade_type' => env('PAY_WECHAT_TRADE_TYPE', 'APP'),
    ],

    // ------------------------------------------------------------------
    // 微信支付 v3（官方 SDK wechatpay/wechatpay，P0 主链路）
    // ------------------------------------------------------------------
    // 上线前置条件（微信商户平台下载）：
    //   1. 商户 API 证书 apiclient_cert.pem / apiclient_key.pem + 证书序列号 serial_no
    //   2. 微信支付平台证书（旧模式）
    //      或「微信支付公钥」pub_key.pem + 公钥 ID（新模式，推荐，无需定期轮换平台证书）
    //   3. APIv3 密钥（32 位，用于解密回调 resource）
    // 证书建议放项目 cert/wechatpay/ 下（非 public 目录），此处只配「路径」或「PEM 文本」。
    // 三者均可被 setting 表 wechat_pay.v3_* 覆盖，见 PayConfig::wechatV3()。
    'wechat_v3' => [
        'mchid'              => env('PAY_WECHAT_V3_MCHID', ''),
        'serial_no'          => env('PAY_WECHAT_V3_SERIAL_NO', ''),
        // 商户 API 证书私钥 apiclient_key.pem：支持绝对路径 / file:// 路径 / 直接 PEM 文本
        'private_key'        => env('PAY_WECHAT_V3_PRIVATE_KEY', ''),
        // 二选一：平台证书（旧）或「微信支付公钥」（新，推荐）
        'platform_cert'      => env('PAY_WECHAT_V3_PLATFORM_CERT', ''),
        'platform_pubkey'    => env('PAY_WECHAT_V3_PLATFORM_PUBKEY', ''),
        'platform_pubkey_id' => env('PAY_WECHAT_V3_PLATFORM_PUBKEY_ID', ''),
        // APIv3 密钥（回调 resource 解密用）
        'apiv3_key'          => env('PAY_WECHAT_V3_API_KEY', ''),
    ],

    // ------------------------------------------------------------------
    // 支付宝（官方 SDK alipaysdk/easysdk，P0 主链路）
    // 密钥本身仍取自 wxapp 表 / setting 表（见 PayConfig::alipay()）
    // ------------------------------------------------------------------
    'alipay_sdk' => [
        'protocol'     => 'https',
        'gateway_host' => env('PAY_ALIPAY_GATEWAY', 'openapi.alipay.com'),
        'sign_type'    => env('PAY_ALIPAY_SIGN_TYPE', 'RSA2'),
        // easySDK 为全局单例；单请求内只服务一个 wxapp，若未来需多实例请改用 multipleFactory
        'ignore_ssl'   => false,
    ],

    // Stripe（REST + HMAC 验签）
    'stripe' => [
        'secret_key'      => env('STRIPE_SECRET_KEY', ''),
        'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),
        'webhook_secret'  => env('STRIPE_WEBHOOK_SECRET', ''),
        // 结算币种（马来西亚业务默认 MYR）
        'currency'        => env('STRIPE_CURRENCY', 'myr'),
        // PaymentSheet 展示的商户名（RN 端 merchantName）
        'merchant_name'   => env('STRIPE_MERCHANT_NAME', 'Shop'),
        'api_version'     => '2023-10-16',
    ],

    // 异步通知地址覆盖（留空则由 APP_HOST + /api/notify/{channel} 自动生成）
    // 生产环境若域名与 APP_HOST 不一致（如回调走独立域名），在此显式配置。
    'notify_url' => [
        'order'  => env('PAY_NOTIFY_URL_WECHAT', ''),
        'alipay' => env('PAY_NOTIFY_URL_ALIPAY', ''),
        'stripe' => env('PAY_NOTIFY_URL_STRIPE', ''),
    ],
];
