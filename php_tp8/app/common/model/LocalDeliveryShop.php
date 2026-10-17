<?php
namespace app\common\model;

use think\facade\Db;
use think\Collection;

/**
 * 本地外送-店铺管理模型（独立表 yoshop_local_delivery_shop）
 *
 * TP8 适配要点同 LocalDelivery：
 *  - Request::instance() → request()->param()
 *  - \think\Db → think\facade\Db
 *  - self::$wxapp_id → self::getWxappId()
 *  - 不使用 with('image') 关联，改为手动补 image_url / image.file_path
 */
class LocalDeliveryShop extends BaseModel
{
    protected $name = 'local_delivery_shop';
    protected $pk = 'shop_id';

    /** 可写入字段白名单（防止表单注入无关字段） */
    protected static $writableFields = [
        'shop_name', 'address', 'whatsapp', 'business_hours', 'accept_order',
        'delivery_enabled', 'description', 'image_id', 'sort', 'status',
    ];

    protected static function filterFields($data)
    {
        $filtered = [];
        foreach (self::$writableFields as $field) {
            if (isset($data[$field])) {
                $filtered[$field] = $data[$field];
            }
        }
        return $filtered;
    }

    /**
     * 列表（后台）
     * @param array $param
     * @return \think\Paginator
     */
    public function getList($param = [])
    {
        $query = $this;
        if (!empty($param['shop_name'])) {
            $query = $query->where('shop_name', 'like', '%' . trim($param['shop_name']) . '%');
        }
        $list = $query->order(['sort' => 'asc', 'shop_id' => 'desc'])
            ->paginate(['list_rows' => 15, 'query' => request()->param()]);
        $this->attachListFields($list);
        return $list;
    }

    /**
     * 详情（返回模型对象，供控制器 edit()/setDelete() 调用）
     * @param int $shopId
     * @return static|null
     */
    public static function detail($shopId)
    {
        return (new static)->where('shop_id', $shopId)->find();
    }

    /**
     * 新增
     * @param array $data
     * @return bool
     */
    public function add($data)
    {
        $data = self::filterFields($data);
        $data['wxapp_id'] = self::getWxappId();
        $data['create_time'] = time();
        $data['update_time'] = time();
        return Db::name('local_delivery_shop')->insert($data) !== false;
    }

    /**
     * 编辑
     * @param array $data
     * @return bool
     */
    public function edit($data)
    {
        $data = self::filterFields($data);
        if (empty($data)) {
            $this->error = '未接收到表单数据';
            return false;
        }
        $data['update_time'] = time();
        return Db::name('local_delivery_shop')
            ->where('shop_id', $this['shop_id'])
            ->update($data) !== false;
    }

    /**
     * 删除（物理删除）
     * @return bool
     */
    public function setDelete()
    {
        return $this->delete();
    }

    /**
     * 后台表单下拉：返回 [shop_id => shop_name]（含停用，便于把商品归属到任意店铺）
     * @return array
     */
    public static function getListForSelect()
    {
        $rows = (new static)->order(['sort' => 'asc', 'shop_id' => 'desc'])->select();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['shop_id']] = $row['shop_name'] . ($row['status'] != 1 ? '（停用）' : '');
        }
        return $map;
    }

    /**
     * 批量补充展示字段（店铺图片地址）
     * @param \think\Paginator $list
     */
    private function attachListFields($list)
    {
        $items = $list->items();
        foreach ($items as &$row) {
            $row = $row instanceof self ? $row->toArray() : (array)$row;
            $row['image_url'] = !empty($row['image_id']) ? BaseModel::getFileUrl($row['image_id']) : '';
            $row['image'] = ['file_path' => $row['image_url']];
        }
        unset($row);
        $list->setCollection(Collection::make($items));
    }
}
