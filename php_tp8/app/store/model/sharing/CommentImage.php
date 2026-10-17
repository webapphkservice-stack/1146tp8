<?php

namespace app\store\model\sharing;

use app\common\model\BaseModel;

/**
 * 拼团商品评价图片模型(后台，自包含)
 * Class CommentImage
 * @package app\store\model\sharing
 */
class CommentImage extends BaseModel
{
    protected $name = 'sharing_comment_image';

    /**
     * 关联图片文件
     * @return \think\model\relation\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo('app\common\model\UploadFile', 'image_id', 'file_id')
            ->bind(['file_path']);
    }

}
