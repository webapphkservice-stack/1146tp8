<?php
namespace app\common\library\wechat;

/**
 * 微信基础类（移植自 php_my common/library/WeChat/WxBase，去掉 SDK 依赖）
 */
class WxBase
{
    protected $error = '';

    public function getError()
    {
        return $this->error;
    }

    /**
     * 获取 access_token（带文件缓存）
     * @param string $appId
     * @param string $appSecret
     * @return bool|string
     */
    public function getAccessToken($appId, $appSecret)
    {
        $cacheKey = "access_token_{$appId}";
        if ($token = \think\facade\Cache::get($cacheKey)) {
            return $token;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$appSecret}";
        $result = curl($url);
        $data = \app\common\library\helper::jsonDecode($result);
        if (empty($data) || empty($data['access_token'])) {
            log_write("微信 access_token 获取失败：{$result}");
            return false;
        }
        \think\facade\Cache::set($cacheKey, $data['access_token'], 3600);
        return $data['access_token'];
    }

    /**
     * 发送订阅消息 / 模板消息（TP8 不迁微信订阅消息，仅保留基础通道占位）
     * @return bool
     */
    public function sendTemplateMessage()
    {
        $this->error = 'TP8 未接入微信订阅消息通道';
        return false;
    }
}
