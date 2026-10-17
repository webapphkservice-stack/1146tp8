<?php

namespace app\store\controller;

use app\store\controller\Controller;
use app\store\model\UploadFile as UploadFileModel;
use app\store\model\Setting as SettingModel;
use app\common\library\storage\Driver as StorageDriver;

/**
 * 文件上传控制器（文件库弹窗的图片上传入口）
 *
 * 对应 TP5 的 store/controller/Upload.php。
 * 路由：upload/image（POST）— WebUploader 通过 file.library.js 发起。
 */
class Upload extends Controller
{
    private $config = [];

    public function initialize()
    {
        parent::initialize();
        $this->config = SettingModel::getItem('storage');
    }

    /**
     * 图片上传接口
     * @param int $group_id
     * @return \think\response\Json
     */
    public function image($group_id = -1)
    {
        try {
            $driver = new StorageDriver($this->config);
            if (!$driver->setUploadFile('iFile')) {
                return json(['code' => 0, 'msg' => '未检测到上传文件']);
            }

            // === 自动转 WebP（若 GD 支持且上传的是 jpg/png）===
            $converted = $this->tryConvertToWebp($driver);
            if ($converted !== false) {
                $driver->setFileName($converted);
            }

            if (!$driver->upload()) {
                return json(['code' => 0, 'msg' => '图片上传失败：' . $driver->getError()]);
            }

            $fileName = $driver->getFileName();
            $fileInfo = $driver->getFileInfo();
            $storageName = $driver->getStorageName();

            $uploadFile = $this->addUploadFile(
                (int)$group_id,
                $fileName,
                $fileInfo,
                $storageName
            );

            if ($uploadFile === false) {
                return json(['code' => 0, 'msg' => '文件库记录写入失败']);
            }

            return json([
                'code' => 1,
                'msg' => '图片上传成功',
                'data' => $uploadFile,
            ]);
        } catch (\Throwable $e) {
            return json(['code' => 0, 'msg' => '上传异常：' . $e->getMessage()]);
        }
    }

    /**
     * 尝试将上传图片转为 webp（节省带宽和存储）
     *
     * 优先级：Imagick > GD。
     * Imagick 支持 262 种格式（含 HEIC/TIFF/PSD），且正确处理透明 PNG、GIF 动画。
     * GD 仅作兜底（且必须 --with-webp 编译）。
     *
     * @param StorageDriver $driver
     * @return string|false 转换后的临时文件路径（.webp）；不支持时返回 false
     */
    private function tryConvertToWebp(StorageDriver $driver)
    {
        $tmpFile = $driver->getUploadFile()->getRealPath();
        if (!$tmpFile || !file_exists($tmpFile)) {
            return false;
        }

        $fileInfo = $driver->getFileInfo();
        $mime = strtolower($fileInfo['type'] ?? '');
        $ext = strtolower(pathinfo($fileInfo['name'] ?? '', PATHINFO_EXTENSION));

        // 已是 webp / gif / bmp 不动（gif 转 webp 会丢动画，bmp 极少）
        if (in_array($ext, ['webp', 'gif', 'bmp'], true)
            || in_array($mime, ['image/webp', 'image/gif', 'image/bmp'], true)) {
            return false;
        }

        // —— 优先：Imagick（已安装，262 格式支持）——
        if (extension_loaded('imagick') && class_exists('Imagick')) {
            return $this->convertWithImagick($tmpFile);
        }

        // —— 兜底：GD（需 --with-webp）——
        if (extension_loaded('gd') && function_exists('imagecreatewebp')) {
            return $this->convertWithGd($tmpFile, $mime, $ext);
        }

        return false;
    }

    /**
     * Imagick 转 webp（推荐路径）
     */
    private function convertWithImagick(string $tmpFile)
    {
        try {
            $img = new Imagick($tmpFile);
            $img->setImageFormat('webp');
            // 质量 80（Imagick 0-100，比 GD 75 视觉更稳）
            $img->setImageCompressionQuality(80);
            // HEIC/CMYK 等先转 sRGB，防止 webp 输出异常
            if ($img->getImageColorspace() !== Imagick::COLORSPACE_SRGB) {
                $img->transformImageColorspace(Imagick::COLORSPACE_SRGB);
            }
            // 移除任何附加配置文件，减小体积
            $img->stripImage();

            $dest = $tmpFile . '.webp';
            if ($img->writeImage($dest)) {
                $img->clear();
                $img->destroy();
                @unlink($tmpFile);
                rename($dest, $tmpFile);
                return $tmpFile;
            }
            $img->clear();
            $img->destroy();
            @unlink($dest);
        } catch (\Throwable $e) {
            // Imagick 对某些损坏/特殊格式可能抛异常，静默回退
        }
        return false;
    }

    /**
     * GD 转 webp（兜底路径）
     */
    private function convertWithGd(string $tmpFile, string $mime, string $ext)
    {
        if ($mime === 'image/jpeg' || in_array($ext, ['jpg', 'jpeg'], true)) {
            $src = @imagecreatefromjpeg($tmpFile);
        } elseif ($mime === 'image/png' || $ext === 'png') {
            $src = @imagecreatefrompng($tmpFile);
        } else {
            return false;
        }
        if (!$src) {
            return false;
        }
        $dest = $tmpFile . '.webp';
        $ok = @imagewebp($src, $dest, 75);
        // imagedestroy 在 PHP 8.5+ 已 deprecated（8.0 起就 no-op）
        if (PHP_VERSION_ID < 80500) {
            imagedestroy($src);
        }
        if ($ok) {
            @unlink($tmpFile);
            rename($dest, $tmpFile);
            return $tmpFile;
        }
        @unlink($dest);
        return false;
    }

    /**
     * 写入文件库记录
     */
    private function addUploadFile($group_id, $fileName, $fileInfo, $storage)
    {
        $model = new UploadFileModel;
        $model->save([
            'group_id'    => $group_id > 0 ? $group_id : 0,
            'storage'     => $storage ?: 'local',
            'file_url'    => '',   // local 引擎 file_url 留空，file_path accessor 会自动拼 APP_HOST
            'file_name'   => $fileName,
            'file_size'   => (int)($fileInfo['size'] ?? 0),
            'file_type'   => 'image',
            'extension'   => pathinfo($fileInfo['name'] ?? $fileName, PATHINFO_EXTENSION),
            'is_user'     => 0,
            'is_recycle'  => 0,
            'is_delete'   => 0,
        ]);
        if (!$model->file_id) {
            return false;
        }
        return [
            'file_id'   => $model->file_id,
            'file_path' => $model->file_path,
            'file_name' => $model->file_name,
        ];
    }
}
