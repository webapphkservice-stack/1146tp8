<?php
namespace app\common\service;

use app\common\library\helper;

/**
 * WhatsApp 验证码下发服务（移植自 php_my common/service/ChatAppService）
 *
 * 生产通过阿里云 ChatApp（WhatsApp Cloud API）下发；TP8 未接入真实下发通道，
 * 开发态直接返回成功，验证码由调用方写入 chatapp_verify_code 表供校验。
 */
class ChatAppService
{
    private $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 发送验证码
     * @param string $phone
     * @param string $code
     * @param string $event
     * @param string $channelType
     * @return array
     */
    public function sendVerificationCode($phone, $code, $event, $channelType)
    {
        return [
            'code' => 1,
            'msg'  => 'dev',
            'data' => ['MessageId' => getGuidV4()],
        ];
    }
}
