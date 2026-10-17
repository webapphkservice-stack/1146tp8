<?php

namespace app\common\model\article;

use think\facade\Cache;
use app\common\model\BaseModel;

/**
 * 文章分类模型（TP8 适配）
 * Class Category
 * @package app\common\model
 */
class Category extends BaseModel
{
    protected $name = 'article_category';

    /**
     * 所有分类（带缓存）
     * @return mixed
     */
    public static function getALL()
    {
        $model = new static;
        $cacheKey = 'article_category_' . self::getWxappId();
        if (!$data = Cache::get($cacheKey)) {
            $data = $model->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
            $data = !empty($data) ? $data->toArray() : [];
            Cache::tag('cache')->set($cacheKey, $data);
        }
        return $data;
    }

}
