<?php
namespace app\api\model;

use app\common\model\BaseModel;

/**
 * 商品分类（精简版，仅服务 RN 端 category/index）
 * 表 yoshop_category：category_id / name / parent_id / image_id / sort / wxapp_id
 * 注：category 表无 status/is_delete 列，按 wxapp_id 全量取。
 */
class Category extends BaseModel
{
    protected $name = 'category';

    /**
     * 分类树（两级，含图片 file_path）
     * 返回结构对齐 RN CategoryScreen：list[] = { category_id, name, image, child?[] }
     */
    public function getTree()
    {
        $list = $this->order('sort', 'asc')->select()->toArray();
        $byId = [];
        foreach ($list as $item) {
            $item['image'] = self::getFileUrl($item['image_id']);
            $item['child'] = [];
            $byId[$item['category_id']] = $item;
        }
        // 挂子级
        foreach ($byId as $item) {
            if ($item['parent_id'] > 0 && isset($byId[$item['parent_id']])) {
                $byId[$item['parent_id']]['child'][] = $item;
            }
        }
        // 收集根节点（此时 child 已填充）
        $roots = [];
        foreach ($byId as $item) {
            if ($item['parent_id'] == 0) {
                $roots[] = $byId[$item['category_id']];
            }
        }
        return $roots;
    }
}
