<?php

namespace app\store\model;

use app\common\model\Article as ArticleModel;

/**
 * 文章模型（后台，TP8 适配）
 * Class Article
 * @package app\store\model
 */
class Article extends ArticleModel
{
    protected $field = [
        'article_id', 'article_title', 'summary', 'show_type', 'category_id',
        'image_id', 'image_ids', 'article_content', 'article_sort', 'article_status',
        'virtual_views', 'actual_views', 'is_delete', 'wxapp_id', 'create_time', 'update_time',
    ];

    /**
     * 获取文章列表
     * @return \think\Paginator
     */
    public function getList()
    {
        return $this->with(['image', 'category'])
            ->where('is_delete', '=', 0)
            ->order(['article_sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 新增记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        if (empty($data['image_ids']) || !is_array($data['image_ids']) || count($data['image_ids']) === 0) {
            $this->error = '请至少上传一张封面图';
            return false;
        }
        if (count($data['image_ids']) > 9) {
            $this->error = '封面图最多上传9张';
            return false;
        }
        if (empty($data['article_content'])) {
            $this->error = '请输入文章内容';
            return false;
        }
        $data['image_id'] = reset($data['image_ids']);
        $data['image_ids'] = json_encode(array_values($data['image_ids']));
        $data['wxapp_id'] = self::getWxappId();
        return $this->allowField($this->field)->save($data);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        if (empty($data['image_ids']) || !is_array($data['image_ids']) || count($data['image_ids']) === 0) {
            $this->error = '请至少上传一张封面图';
            return false;
        }
        if (count($data['image_ids']) > 9) {
            $this->error = '封面图最多上传9张';
            return false;
        }
        if (empty($data['article_content'])) {
            $this->error = '请输入文章内容';
            return false;
        }
        $data['image_id'] = reset($data['image_ids']);
        $data['image_ids'] = json_encode(array_values($data['image_ids']));
        return $this->allowField($this->field)->save($data) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 获取文章总数量
     * @param array $where
     * @return int|string
     */
    public static function getArticleTotal($where = [])
    {
        $model = new static;
        !empty($where) && $model->where($where);
        return $model->where('is_delete', '=', 0)->count();
    }

}
