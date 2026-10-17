<?php
namespace app\common\library\alipay;

use app\common\exception\BaseException;

/**
 * 支付宝用户类（移植自 php_my common/library/Aliyun/AliPay/AlipayUser）
 *
 * 说明：支付宝小程序登录依赖 aop SDK（AlipayClient 等），该 SDK 不在 TP8 迁移范围内，
 * 因此本类仅保留接口形态，实际调用时抛出清晰错误，不阻断其他登录通道。
 */
class AlipayUser
{
    private $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 通过 auth_code 获取支付宝用户信息
     * @param string $code
     * @return array
     * @throws BaseException
     */
    public function getUserInfoByAuthCode($code)
    {
        throw new BaseException('支付宝登录暂未接入 TP8（aop SDK 未迁移）');
    }
}
