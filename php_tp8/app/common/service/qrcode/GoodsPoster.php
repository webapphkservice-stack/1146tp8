<?php

namespace app\common\service\qrcode;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 商品海报合成（GD 实现，完整移植 php Grafika 海报合成语义）
 *
 * 背景=商品主图 → 叠加商品标题/价格文本 → 底部小程序码 → 保存 temp/ 返回 URL。
 */
class GoodsPoster extends Base
{
    private $wxappId;

    public function __construct($wxappId)
    {
        $this->wxappId = $wxappId;
    }

    /**
     * 生成商品海报
     * @param array $goods goods_id/goods_name/price/page
     * @return string 海报 URL
     * @throws \Exception
     */
    public function make($goods)
    {
        if (file_exists($this->posterPath($goods))) {
            return $this->posterUrl($goods);
        }
        // 1. 商品主图
        $imageId = (int)Db::name('goods_image')->where('goods_id', $goods['goods_id'])
            ->order('id', 'asc')->value('image_id');
        $imagePath = $imageId > 0 ? BaseModel::getFileUrl($imageId) : '';
        $bgPath = $this->tempImage($imagePath, $goods['goods_id']);
        // 2. 小程序码
        $qrcodePath = $this->saveQrcode($this->wxappId,
            'gid:' . $goods['goods_id'], $goods['page'] ?? 'pages/goods/index');
        // 3. 合成
        $this->compose($bgPath, $qrcodePath, $goods);
        return $this->posterUrl($goods);
    }

    private function compose($bgPath, $qrcodePath, $goods)
    {
        $bgData = file_get_contents($bgPath);
        if ($bgData === false || ($bg = @imagecreatefromstring($bgData)) === false) {
            throw new \Exception('商品图无效，无法生成海报');
        }
        $w = 750;
        $h = 1000;
        $canvas = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        // 商品图 cover 裁剪 750x750
        $bw = imagesx($bg);
        $bh = imagesy($bg);
        $side = min($bw, $bh);
        imagecopyresampled($canvas, $bg, 0, 0, (int)(($bw - $side) / 2), (int)(($bh - $side) / 2), $w, $w, $side, $side);
        // 标题（两行截断）
        $black = imagecolorallocate($canvas, 34, 34, 34);
        $red = imagecolorallocate($canvas, 250, 53, 52);
        $gray = imagecolorallocate($canvas, 153, 153, 153);
        $title = mb_substr($goods['goods_name'], 0, 18, 'utf-8');
        imagestring($canvas, 5, 24, 770, $this->asciiFallback($title), $black);
        // 价格
        imagestring($canvas, 5, 24, 810, 'RM ' . $goods['price'], $red);
        imagestring($canvas, 3, 24, 845, '长按识别小程序码购买', $gray);
        // 小程序码 160x160 右下
        $qrData = file_get_contents($qrcodePath);
        if ($qrData === false || ($qr = @imagecreatefromstring($qrData)) === false) {
            throw new \Exception('小程序码无效，无法生成海报');
        }
        imagecopyresampled($canvas, $qr, $w - 190, $h - 190, 0, 0, 160, 160, imagesx($qr), imagesy($qr));
        imagepng($canvas, $this->posterPath($goods));
        // PHP 8.5 起 imagedestroy 已弃用（8.0 起无实际作用），GD 资源交由 GC 回收
    }

    /** imagestring 不支持中文时的兜底（中文标题仅绘制 ASCII 部分） */
    private function asciiFallback($text)
    {
        return preg_replace('/[^\x20-\x7e]/', '*', $text);
    }

    private function tempImage($url, $goodsId)
    {
        if ($url === '') {
            throw new \Exception('商品图片缺失，无法生成海报');
        }
        return $this->saveTempImage($this->wxappId, $url, 'poster_' . $goodsId);
    }

    private function posterPath($goods)
    {
        $tempPath = public_path() . 'temp/' . $this->wxappId . '/';
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }
        return $tempPath . 'poster_' . md5('goods_' . $goods['goods_id']) . '.png';
    }

    private function posterUrl($goods)
    {
        return request()->domain() . '/temp/' . $this->wxappId . '/poster_' . md5('goods_' . $goods['goods_id']) . '?t=' . time();
    }
}
