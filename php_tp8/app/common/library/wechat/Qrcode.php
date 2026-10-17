<?php

namespace app\common\library\wechat;

/**
 * 微信小程序码（完整移植 php common/library/wechat/Qrcode 语义）
 */
class Qrcode extends WxBase
{
    /**
     * 获取小程序码（getwxacodeunlimit，scene 为业务参数）
     * @return string 图片二进制
     * @throws \Exception
     */
    public function getQrcode($appId, $appSecret, $scene, $page = null)
    {
        $accessToken = $this->getAccessToken($appId, $appSecret);
        $apiUrl = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$accessToken}";
        $data = [
            'scene' => $scene,
            'width' => 430,
            'check_path' => false,
        ];
        if (!empty($page)) {
            $data['page'] = $page;
        }
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
        ]);
        $content = curl_exec($ch);
        $errno = curl_errno($ch);
        
        if ($errno) {
            throw new \Exception('小程序码获取失败：网络错误');
        }
        // 微信返回 JSON 错误时 content 以 { 开头
        if (!empty($content) && $content[0] === '{') {
            $json = json_decode($content, true);
            throw new \Exception('小程序码获取失败：' . ($json['errmsg'] ?? '未知错误'));
        }
        return $content;
    }
}
