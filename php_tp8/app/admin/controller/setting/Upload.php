<?php
namespace app\admin\controller\setting;

use app\common\controller\Controller;
use app\common\library\storage\Driver as StorageDriver;

/**
 * 总后台图片上传（网站信息 logo 等）。
 * 与 store/api 上传不同：后台为平台级操作，直接用 local 引擎，不依赖用户登录态。
 *
 *   POST /admin/setting.upload/image   (multipart，字段名 iFile)
 */
class Upload extends Controller
{
    public function image()
    {
        try {
            $driver = new StorageDriver(['default' => 'local', 'engine' => ['local' => []]]);
            if (!$driver->setUploadFile('iFile')) {
                return $this->renderError($driver->getError());
            }
            if (!$driver->upload()) {
                return $this->renderError($driver->getError());
            }
            $fileName = $driver->getFileName();
            return $this->renderSuccess([
                'file_name' => $fileName,
                'file_path' => '/' . ltrim($fileName, '/'),
            ], '上传成功');
        } catch (\Throwable $e) {
            return $this->renderError('上传异常：' . $e->getMessage());
        }
    }
}