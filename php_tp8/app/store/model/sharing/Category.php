<?php

namespace app\store\model\sharing;

use think\facade\Cache;
use app\common\model\BaseModel;

/**
 * 拼团商品分类模型(后台，自包含)
 * Class Category
 * @package app\store\model\sharing
 */
class Category extends BaseModel
{
    protected $name = 'sharing_category';

    /**
     * 详情
     * @param $category_id
     * @return null|\think\Model
     */
    public static function detail($category_id)
    {
        return static::find($category_id);
    }

    /**
     * 获取分类缓存树
     * @return array|mixed
     */
    public static function getCacheTree()
    {
        $cacheKey = 'sharing_category_' . self::getWxappId();
        if (!$data = Cache::get($cacheKey)) {
            $data = (new static())->getTreeData();
            Cache::set($cacheKey, $data);
        }
        return $data;
    }

    /**
     * 构建分类树
     * @return array
     */
    private function getTreeData()
    {
        $all = $this->order(['sort' => 'asc', 'create_time' => 'desc'])->select()->toArray();
        return $this->toTree($all);
    }

    /**
     * 递归生成树
     * @param $list
     * @param int $parentId
     * @return array
     */
    private function toTree($list, $parentId = 0)
    {
        $tree = [];
        foreach ($list as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->toTree($list, $item['category_id']);
                if (!empty($children)) {
                    $item['child'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 新增分类
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::getWxappId();
        $this->deleteCache();
        return $this->save($data);
    }

    /**
     * 编辑分类
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        $this->deleteCache();
        return $this->save($data) !== false;
    }

    /**
     * 删除分类
     * @param $category_id
     * @return bool
     */
    public function remove($category_id)
    {
        if ($goodsCount = (new Goods)->getGoodsTotal(['category_id' => $category_id])) {
            $this->error = '该分类下存在' . $goodsCount . '个商品，不允许删除';
            return false;
        }
        if ((new static)->where(['parent_id' => $category_id])->count()) {
            $this->error = '该分类下存在子分类，请先删除';
            return false;
        }
        $this->deleteCache();
        return $this->where('category_id', '=', $category_id)->delete();
    }

    /**
     * 删除分类缓存
     * @return mixed
     */
    private function deleteCache()
    {
        return Cache::delete('sharing_category_' . self::getWxappId());
    }

}
