<?php
namespace app\common\library\wechat;

use app\common\library\helper;

/**
 * 微信用户类（移植自 php_my common/library/WeChat/WxUser，去 SDK 依赖）
 * 仅实现小程序 code -> openid 解析；昵称/头像由前端传入（TP8 不迁移微信订阅消息通道）。
 */
class WxUser extends WxBase
{
    private $appId;
    private $appSecret;

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * 获取微信用户信息（openid / session_key）
     * @param string $code 小程序 wx.login 得到的 code
     * @return array|false
     */
    public function getUserInfo($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appId}"
            . "&secret={$this->appSecret}&js_code={$code}&grant_type=authorization_code";
        $result = curl($url);
        $wxResult = helper::jsonDecode($result);
        if (empty($wxResult) || empty($wxResult['openid'])) {
            log_write("WxUser 获取 openid 失败：{$result}");
            $this->error = '微信登录失败';
            return false;
        }
        return $wxResult;
    }
}
