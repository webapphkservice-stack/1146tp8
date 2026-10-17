<?php

namespace app\common\service\qrcode;

use think\facade\Request;

/**
 * 订单核销二维码（完整移植 php common/service/qrcode/Extract）
 *
 * 场景值 oid:{orderId},oty:{orderType}；核销页 pages/store/check/order。
 */
class Extract extends Base
{
    private $wxappId;
    private $user;
    private $orderId;
    private $orderType;

    public function __construct($wxappId, $user, $orderId, $orderType = 10)
    {
        $this->wxappId = $wxappId;
        $this->user = $user;
        $this->orderId = $orderId;
        $this->orderType = $orderType;
    }

    /**
     * 获取核销小程序码（返回可访问 URL）
     * @throws \Exception
     */
    public function getImage()
    {
        if (file_exists($this->getPosterPath())) {
            return $this->getPosterUrl();
        }
        $qrcode = $this->saveQrcode(
            $this->wxappId,
            "oid:{$this->orderId},oty:{$this->orderType}",
            'pages/store/check/order'
        );
        copy($qrcode, $this->getPosterPath());
        return $this->getPosterUrl();
    }

    /**
     * 静态入口（控制器调用便捷方式）
     */
    public static function build($wxappId, $user, $orderId, $orderType = 10)
    {
        return (new self($wxappId, $user, $orderId, $orderType))->getImage();
    }

    private function getPosterPath()
    {
        $tempPath = public_path() . 'temp/' . $this->wxappId . '/';
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }
        return $tempPath . $this->getPosterName();
    }

    private function getPosterName()
    {
        return 'extract_' . md5("{$this->orderId}_{$this->user['open_id']}}") . '.png';
    }

    private function getPosterUrl()
    {
        return request()->domain() . '/temp/' . $this->wxappId . '/' . $this->getPosterName() . '?t=' . time();
    }
}
