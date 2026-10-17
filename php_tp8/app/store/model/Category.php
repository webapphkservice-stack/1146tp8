<?php
namespace app\store\model;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 商品分类模型（服务端渲染版，移植自 TP5 store/model/Category.php）
 * 用 Db 直查自包含实现，不依赖缺失的 common 基类。
 */
class Category extends BaseModel
{
    protected $name = 'category';
    protected $pk = 'category_id';
    protected $updateTime = false;

    /**
     * 获取分类树（嵌套 child）
     */
    public static function getCacheTree()
    {
        $self = new static();
        return $self->getTree($self->getCacheAll());
    }

    private function getCacheAll()
    {
        $rows = Db::name('category')
            ->where('wxapp_id', self::getWxappId())
            ->order('sort', 'asc')->order('create_time', 'asc')
            ->select()->toArray();
        foreach ($rows as &$r) {
            $r['image'] = $r['image_id'] ? ['file_path' => self::getFileUrl($r['image_id'])] : [];
        }
        return $rows;
    }

    private function getTree($all, $parentId = 0)
    {
        $tree = [];
        foreach ($all as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->getTree($all, $item['category_id']);
                if ($children) {
                    $item['child'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 分类详情（含图片）
     */
    public static function detail($categoryId, $with = [])
    {
        $row = Db::name('category')->where('category_id', $categoryId)->find();
        if ($row) {
            $row['image'] = $row['image_id'] ? ['file_path' => self::getFileUrl($row['image_id'])] : [];
        }
        return $row ?: [];
    }

    public function add($data)
    {
        $data['wxapp_id'] = self::getWxappId();
        $data['create_time'] = time();
        $data['update_time'] = time();
        return Db::name('category')->insert($data) !== false;
    }

    public function edit($data)
    {
        $categoryId = (int)$data['category_id'];
        if ($data['parent_id'] > 0 && static::hasSubCategory($categoryId)) {
            $this->error = '该分类下存在子分类，不可以移动';
            return false;
        }
        $data['update_time'] = time();
        if (!array_key_exists('image_id', $data)) {
            $data['image_id'] = 0;
        }
        return Db::name('category')->where('category_id', $categoryId)->update($data) !== false;
    }

    public function remove($categoryId)
    {
        if ($this->getGoodsTotal($categoryId) > 0) {
            $this->error = '该分类下存在商品，不允许删除';
            return false;
        }
        if (static::hasSubCategory($categoryId)) {
            $this->error = '该分类下存在子分类，请先删除';
            return false;
        }
        return Db::name('category')->where('category_id', $categoryId)->delete();
    }

    public static function hasSubCategory($categoryId)
    {
        return Db::name('category')->where('parent_id', $categoryId)->count() > 0;
    }

    public function getGoodsTotal($categoryId)
    {
        return Db::name('goods')->where('category_id', $categoryId)->where('is_delete', 0)->count();
    }

    /**
     * 获取分类及其所有子分类 id 集（用于商品筛选）
     */
    public static function getSubCategoryId($categoryId)
    {
        $all = Db::name('category')->where('wxapp_id', self::getWxappId())
            ->field('category_id, parent_id')->select()->toArray();
        $ids = [(int)$categoryId];
        $changed = true;
        while ($changed) {
            $changed = false;
            foreach ($all as $item) {
                if (in_array($item['parent_id'], $ids, true)
                    && !in_array($item['category_id'], $ids, true)) {
                    $ids[] = $item['category_id'];
                    $changed = true;
                }
            }
        }
        return $ids;
    }
}
