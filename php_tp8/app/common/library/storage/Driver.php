<?php
namespace app\common\library\storage;

/**
 * 存储驱动（移植自 php_my common/library/storage/Driver，仅保留 local 引擎）
 * 七牛等云引擎按迁移清单不迁。
 */
class Driver
{
    private $config;
    private $engine;
    private $uploadFile;
    private $fileName = '';
    private $fileInfo = [];
    private $error = '';

    public function __construct($config, $storage = '')
    {
        $this->config = $config;
        $engine = $storage ?: ($config['default'] ?? 'local');
        $class = '\\app\\common\\library\\storage\\engine\\' . ucfirst($engine);
        if (!class_exists($class)) {
            $class = '\\app\\common\\library\\storage\\engine\\Local';
        }
        $this->engine = new $class($config['engine']['local'] ?? []);
    }

    /**
     * 设置上传文件（来自请求）
     * @param string $name
     * @return bool
     */
    public function setUploadFile($name)
    {
        $this->uploadFile = request()->file($name);
        if (empty($this->uploadFile)) {
            $this->error = '未检测到上传文件';
            return false;
        }
        $this->fileInfo = [
            'name' => $this->uploadFile->getOriginalName(),
            'size' => $this->uploadFile->getSize(),
            'type' => $this->uploadFile->getOriginalMime(),
        ];
        return true;
    }

    /**
     * 执行上传
     * @return bool
     */
    public function upload()
    {
        if (empty($this->uploadFile)) {
            $this->error = '未检测到上传文件';
            return false;
        }
        // 用真实临时文件的扩展名（兼容 webp 转换后的临时文件）
        $ext = pathinfo($this->uploadFile->getRealPath(), PATHINFO_EXTENSION)
            ?: $this->uploadFile->extension();
        $this->fileName = 'uploads/' . date('Y/m') . '/' . getGuidV4() . '.' . $ext;
        if (!$this->engine->put($this->fileName, $this->uploadFile->getRealPath())) {
            $this->error = $this->engine->getError();
            return false;
        }
        return true;
    }

    /**
     * 获取原始上传文件对象（用于读取临时文件路径、扩展名等）
     */
    public function getUploadFile()
    {
        return $this->uploadFile;
    }

    /**
     * 直接覆盖文件名（webp 转换后修改后缀等场景）
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getFileExt()
    {
        return $this->uploadFile ? $this->uploadFile->extension() : '';
    }

    public function getStorageName()
    {
        return 'local';
    }
}
