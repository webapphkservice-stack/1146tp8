<?php

namespace app\common\service\qrcode;

use app\common\model\Wxapp as WxappModel;

/**
 * 二维码服务基类（完整移植 php common/service/qrcode/Base）
 */
class Base
{
    /**
     * 保存小程序码到文件（有缓存直接返回）
     * @return string 文件绝对路径
     * @throws \Exception
     */
    protected function saveQrcode($wxappId, $scene, $page = null)
    {
        $dirPath = runtime_path() . 'image' . '/' . $wxappId;
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }
        $fileName = 'qrcode_' . md5($wxappId . $scene . (string)$page) . '.png';
        $savePath = $dirPath . '/' . $fileName;
        if (file_exists($savePath)) {
            return $savePath;
        }
        $wxConfig = WxappModel::getWxappCache($wxappId);
        if (empty($wxConfig['app_id']) || empty($wxConfig['app_secret'])) {
            throw new \Exception('小程序配置缺失，无法生成小程序码');
        }
        $Qrcode = new \app\common\library\wechat\Qrcode();
        $content = $Qrcode->getQrcode($wxConfig['app_id'], $wxConfig['app_secret'], $scene, $page);
        // 校验返回内容为有效图片（微信出错时返回 JSON，避免坏文件进入缓存）
        if (empty($content) || @getimagesizefromstring($content) === false) {
            $errMsg = '';
            $decoded = json_decode((string)$content, true);
            if (is_array($decoded) && !empty($decoded['errmsg'])) {
                $errMsg = '：' . $decoded['errmsg'];
            }
            throw new \Exception('生成小程序码失败' . $errMsg);
        }
        file_put_contents($savePath, $content);
        return $savePath;
    }

    /**
     * 获取网络图片到临时目录
     */
    protected function saveTempImage($wxappId, $url, $mark = 'temp')
    {
        $dirPath = runtime_path() . 'image' . '/' . $wxappId;
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }
        $savePath = $dirPath . '/' . $mark . '_' . md5($url) . '.png';
        if (file_exists($savePath)) {
            return $savePath;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => 1,
            // CURLOPT_BINARYTRANSFER 自 PHP 8.4 起弃用（自 5.1.2 起已无效），直接移除
            CURLOPT_TIMEOUT => 30,
        ]);
        $img = curl_exec($ch);
        
        if ($img === false) {
            throw new \Exception('图片下载失败');
        }
        // 校验下载内容为有效图片（避免 404 页面等被缓存为图片，导致后续 imagecreatefromstring 报错）
        if (@getimagesizefromstring($img) === false) {
            throw new \Exception('图片下载失败：返回内容不是有效图片');
        }
        file_put_contents($savePath, $img);
        return $savePath;
    }
}
