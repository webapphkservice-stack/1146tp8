<?php

namespace app\common\model;


/**
 * 文件库模型
 * Class UploadFile
 * @package app\common\model
 */
class UploadFile extends BaseModel
{
    protected $name = 'upload_file';
    protected $updateTime = false;
    protected $deleteTime = false;
    protected $append = ['file_path'];

    /**
     * 关联文件库分组表
     * @return \think\model\relation\BelongsTo
     */
    public function uploadGroup()
    {
        return $this->belongsTo('UploadGroup', 'group_id');
    }

    /**
     * 获取图片完整路径
     * @param $value
     * @param $data
     * @return string
     */
    public function getFilePathAttr($value, $data)
    {
        $fileName = $data['file_name'];
        // file_name 已是完整 URL 时直接返回，避免重复拼接域名导致 404
        if (stripos($fileName, 'http://') === 0 || stripos($fileName, 'https://') === 0) {
            return $fileName;
        }
        if ($data['storage'] === 'local') {
            return rtrim(base_url(), '/') . '/uploads/' . ltrim($fileName, '/');
        }
        return rtrim($data['file_url'], '/') . '/' . ltrim($fileName, '/');
    }

    /**
     * 文件详情
     * @param $file_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($file_id)
    {
        return self::find($file_id);
    }

    /**
     * 根据文件名查询文件id
     * @param $fileName
     * @return mixed
     */
    public static function getFildIdByName($fileName)
    {
        return (new static)->where(['file_name' => $fileName])->value('file_id');
    }

    /**
     * 查询文件id
     * @param $fileId
     * @return mixed
     */
    public static function getFileName($fileId)
    {
        return (new static)->where(['file_id' => $fileId])->value('file_name');
    }

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->save($data);
    }

}
