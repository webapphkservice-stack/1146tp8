<?php
namespace app\common\library\storage\engine;

/**
 * 本地存储引擎
 */
class Local
{
    private $error = '';
    private $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 保存文件到 public/uploads
     * @param string $savePath 形如 uploads/2026/08/xxx.jpg
     * @param string $tempFile 临时文件路径
     * @return bool
     */
    public function put($savePath, $tempFile)
    {
        $dest = public_path() . $savePath;
        $dir = dirname($dest);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!copy($tempFile, $dest)) {
            $this->error = '文件保存失败';
            return false;
        }
        return true;
    }

    public function getError()
    {
        return $this->error;
    }
}
