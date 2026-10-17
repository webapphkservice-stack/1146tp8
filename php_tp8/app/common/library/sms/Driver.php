<?php
namespace app\common\library\sms;

/**
 * 短信驱动（移植自 php_my common/library/sms/Driver）
 *
 * TP8 已收敛短信引擎（仅保留实际使用的通道），且 dev 环境验证码走缓存返回，
 * 因此发送引擎不强制接入：sendSms 默认返回 false，由调用方回退到“返回验证码”的开发态。
 */
class Driver
{
    private $config;
    private $error = '';

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 发送短信
     * @param string $scene
     * @param array $data
     * @param bool $isVirtual
     * @param string $mobile
     * @return bool
     */
    public function sendSms($scene, $data, $isVirtual, $mobile)
    {
        $this->error = 'TP8 未配置短信发送引擎（dev 环境验证码由缓存返回）';
        return false;
    }

    public function getError()
    {
        return $this->error;
    }
}
