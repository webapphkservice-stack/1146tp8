<?php
namespace app\common\enum\order;

/**
 * 支付方式枚举
 * 与 RN 端 src/config/enum.ts 的 PayTypeEnum 保持一致：
 *  BALANCE=10 / WECHAT=20 / FRIEND=30 / ALIPAY=40 / STRIPE=50
 * VIRTUAL=60 为微信小程序虚拟支付（wx.requestVirtualPayment），仅小程序端 VIP/代币使用
 */
class PayType
{
    const BALANCE = 10; // 余额支付
    const WECHAT  = 20; // 微信支付
    const FRIEND  = 30; // 好友代付
    const ALIPAY  = 40; // 支付宝
    const STRIPE  = 50; // 银行卡(Stripe)
    const VIRTUAL = 60; // 微信小程序虚拟支付(道具直购/代币)
}
