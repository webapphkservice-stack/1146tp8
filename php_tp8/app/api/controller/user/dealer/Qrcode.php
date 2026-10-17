<?php
namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\Setting as SettingModel;

/**
 * 分销海报 / 二维码（对齐 RN dealer.ts qrcodePoster）
 *
 * 说明：php_my 通过 Poster 服务合成海报图片。本迁移为轻量版，
 * 直接返回可生成二维码的分享链接（RN 端据此生成二维码）。
 * 若需服务端合成海报 PNG，请安装二维码库后扩展本方法。
 */
class Qrcode extends Controller
{
    public function poster()
    {
        $user = $this->getUser();
        $qrcode = 'pages/dealer/apply?referee_id=' . $user['user_id'];
        $words = SettingModel::getItem('words');
        return $this->renderSuccess(compact('qrcode', 'words'));
    }
}
