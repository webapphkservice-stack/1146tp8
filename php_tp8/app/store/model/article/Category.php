<?php

namespace app\store\model\article;

use think\facade\Cache;
use app\store\model\Article as ArticleModel;
use app\common\model\article\Category as CategoryModel;

/**
 * 文章分类模型（后台，TP8 适配）
 * Class Category
 * @package app\store\model\article
 */
class Category extends CategoryModel
{
    protected $field = [
        'category_id', 'name', 'sort', 'wxapp_id', 'create_time', 'update_time',
    ];

    /**
     * 分类详情
     * @param $category_id
     * @return Category|null
     */
    public static function detail($category_id)
    {
        return static::find($category_id);
    }

    /**
     * 分类列表
     * @return \think\Paginator
     */
    public function getList()
    {
        return $this->order(['sort' => 'asc', 'create_time' => 'asc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::getWxappId();
        $this->deleteCache();
        return $this->allowField($this->field)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        $this->deleteCache();
        return $this->allowField($this->field)->save($data);
    }

    /**
     * 删除文章分类
     * @param $category_id
     * @return bool|int
     */
    public function remove($category_id)
    {
        // 判断是否存在文章
        $articleCount = ArticleModel::getArticleTotal(['category_id' => $category_id]);
        if ($articleCount > 0) {
            $this->error = '该分类下存在' . $articleCount . '个文章，不允许删除';
            return false;
        }
        $this->deleteCache();
        return $this->delete();
    }

    /**
     * 删除缓存
     * @return bool
     */
    private function deleteCache()
    {
        return Cache::rm('article_category_' . self::getWxappId());
    }

}
