<?php
namespace app\api\model;

use app\common\model\UploadFile as UploadFileModel;

/**
 * API 应用文件库模型
 */
class UploadFile extends UploadFileModel
{
    protected $hidden = ['is_user', 'channel', 'group_id'];

    /**
     * 新增上传文件记录
     * @param array $data
     * @param int $userId
     * @return bool
     */
    public function add($data, $userId = 0)
    {
        $data['wxapp_id'] = self::getWxappId();
        $data['is_user'] = $userId ? 1 : 0;
        if (!$this->save($data)) {
            $this->error = '文件记录保存失败';
            return false;
        }
        return true;
    }
}
