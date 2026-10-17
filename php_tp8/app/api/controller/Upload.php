<?php
namespace app\api\controller;

use app\api\model\UploadFile as UploadFileModel;
use app\common\library\storage\Driver;
use app\common\model\Setting;

/**
 * 文件上传（移植自 php_my api/controller/Upload，仅保留本地引擎）
 */
class Upload extends Controller
{
    /**
     * 图片上传（multipart，字段名 iFile）
     */
    public function image()
    {
        $user = $this->getUser(true);
        $StorageDriver = new Driver(Setting::getItem('storage'));
        $StorageDriver->setUploadFile('iFile');
        if (!$StorageDriver->upload()) {
            return $this->renderError($StorageDriver->getError());
        }
        $fileName = $StorageDriver->getFileName();
        $fileInfo = $StorageDriver->getFileInfo();

        $uploadFile = new UploadFileModel;
        $data = [
            'storage'        => $StorageDriver->getStorageName(),
            'file_group_id'  => 0,
            'file_name'      => $fileName,
            'file_size'       => $fileInfo['size'],
            'file_type'       => $fileInfo['type'],
            'extension'       => $StorageDriver->getFileExt(),
            'is_user'         => 1,
            'channel'         => 0,
        ];
        if (!$uploadFile->add($data, $user['user_id'])) {
            return $this->renderError($uploadFile->error ?: '上传失败');
        }
        return $this->renderSuccess([
            'file_id'   => $uploadFile['file_id'],
            'file_path' => $uploadFile['file_path'],
        ]);
    }
}
