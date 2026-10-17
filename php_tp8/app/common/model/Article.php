<?php

namespace app\common\model;

use app\common\model\UploadFile;

/**
 * 文章模型（TP8 适配）
 * Class Article
 * @package app\common\model
 */
class Article extends BaseModel
{
    protected $name = 'article';
    protected $append = ['show_views', 'images'];

    /**
     * 关联文章封面图
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne(UploadFile::class, 'file_id', 'image_id');
    }

    /**
     * 关联文章分类表
     * @return \think\model\relation\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('app\store\model\article\Category', 'category_id', 'category_id');
    }

    /**
     * 展示的浏览次数
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getShowViewsAttr($value, $data)
    {
        return $data['virtual_views'] + $data['actual_views'];
    }

    /**
     * 封面图多图（解析 image_ids JSON 为 file_id/file_path 数组）
     * @param $value
     * @param $data
     * @return array
     */
    public function getImagesAttr($value, $data)
    {
        $list = [];
        if (!empty($data['image_ids'])) {
            $ids = json_decode($data['image_ids'], true);
            if (is_array($ids) && count($ids) > 0) {
                $files = (new UploadFile)->whereIn('file_id', $ids)->select();
                $map = [];
                foreach ($files as $f) {
                    $map[$f['file_id']] = $f['file_path'];
                }
                foreach ($ids as $id) {
                    if (isset($map[$id])) {
                        $list[] = [
                            'file_id'   => $id,
                            'file_path' => $map[$id],
                        ];
                    }
                }
            }
        }
        if (empty($list) && !empty($data['image_id'])) {
            $img = $this->image;
            if (!empty($img) && !empty($img['file_path'])) {
                $list[] = [
                    'file_id'   => $data['image_id'],
                    'file_path' => $img['file_path'],
                ];
            }
        }
        return $list;
    }

    /**
     * 文章详情
     * @param $article_id
     * @return null|static
     */
    public static function detail($article_id)
    {
        return static::with(['image', 'category'])->find($article_id);
    }

}
