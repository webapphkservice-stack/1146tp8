<?php
namespace app\common\library\wechat;

/**
 * 微信小程序「订阅消息」通道
 *
 * 对应 php common/library/wechat/WxSubMsg.php。
 * 说明：TP8 的 WxBase::sendTemplateMessage() 是占位（恒 false），
 * 本类继承 WxBase 并覆写该方法，走订阅消息接口：
 *   POST https://api.weixin.qq.com/cgi-bin/message/subscribe/send
 *
 * 前置条件：用户需在小程序端对对应模板做过订阅授权，否则微信会返回 43101。
 */
class WxSubMsg extends WxBase
{
    /** @var string */
    private $appId;

    /** @var string */
    private $appSecret;

    public function __construct($appId, $appSecret)
    {
        $this->appId     = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * 发送订阅消息
     * @param array $params touser / template_id / page / data
     * @return bool
     */
    public function sendTemplateMessage($params = [])
    {
        $token = parent::getAccessToken($this->appId, $this->appSecret);
        if (empty($token)) {
            $this->error = '获取 access_token 失败';
            return false;
        }

        $payload = [
            'touser'      => $params['touser'] ?? '',
            'template_id' => $params['template_id'] ?? '',
            'page'        => $params['page'] ?? '',
            'data'        => $params['data'] ?? [],
        ];
        if (empty($payload['touser']) || empty($payload['template_id'])) {
            $this->error = '订阅消息缺少 touser 或 template_id';
            return false;
        }
        // 小程序订阅消息默认跳转小程序，不需要 miniprogram_state 时可省略；
        // 这里显式用 formal（正式版），避免开发版/体验版用户收不到
        $payload['miniprogram_state'] = 'formal';
        $payload['lang']              = 'zh_CN';

        $url  = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . $token;
        $resp = json_decode(curl($url, json_encode($payload, JSON_UNESCAPED_UNICODE), true, [
            'Content-Type: application/json',
        ]), true);

        if (!is_array($resp)) {
            $this->error = '订阅消息接口返回异常';
            return false;
        }
        if (!empty($resp['errcode'])) {
            $this->error = '订阅消息发送失败：' . ($resp['errmsg'] ?? ('errcode ' . $resp['errcode']));
            return false;
        }
        return true;
    }
}
