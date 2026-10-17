<?php
namespace app\common\model;

use think\facade\Db;
use think\Collection;

/**
 * 马来西亚本地外送模型（独立表 yoshop_local_delivery，与运费模板 yoshop_delivery 分离）
 *
 * TP8 适配要点：
 *  - Request::instance()->request() → request()->param()
 *  - TP5 式 where 数组 ['field' => ['like', '%x%']] → 三元素条件（think-orm v4 已移除旧式数组）
 *  - \think\Db → think\facade\Db
 *  - self::$wxapp_id → self::getWxappId()
 *  - 不使用 with('image') 关联（避免依赖 UploadFile 模型），改为手动补 image_url / image.file_path
 *  - detail() 返回模型对象（供控制器 edit()/setDelete() 调用）
 */
class LocalDelivery extends BaseModel
{
    protected $name = 'local_delivery';
    protected $pk = 'delivery_id';

    /**
     * 吉隆坡配送区域列表
     * 与小程序端 utils/myRegion.js 中的吉隆坡城市列表保持一致，
     * 后台勾选的配送范围可与用户收货地址的城市直接匹配
     * @return array
     */
    public static function getKlRegions()
    {
        return [
            '甲洞 (Kepong)', '巴都 (Batu)', '冼都 (Sentul)', '文良港 (Setapak)',
            '旺沙玛珠 (Wangsa Maju)', '孟加拉拉 (Manjalara)', '满家乐 (Mont Kiara)',
            '白沙罗高原 (Bukit Damansara)', '士马勒 (Semarak)', '斯迪亚旺沙 (Setiawangsa)',
            '大使 (Duta)', '甘榜巴鲁 (Kampung Baru)', '拿督克拉末 (Datuk Keramat)',
            '班登 (Pandan)', '市中心/KLCC (Ibu Kota)', '十五碑 (Brickfields)',
            '沙叻 (Salak)', '班底谷 (Lembah Pantai)', '孟沙 (Bangsar)',
            '郊外岭 (Taman Desa)', '士布爹 (Seputeh)', '大城堡 (Sri Petaling)',
            '半山芭 (Pudu)', '蕉赖 (Cheras)', '康乐 (Connaught)',
            '武吉加里尔 (Bukit Jalil)', '敦拉扎克镇 (Bandar Tun Razak)',
            '新街场 (Sungai Besi)', '阿南达迈 (Alam Damai)',
        ];
    }

    /** 可写字段白名单 */
    protected static $writableFields = [
        'title', 'image_id', 'description', 'delivery_range', 'price',
        'min_order', 'delivery_fee', 'country', 'status', 'sort', 'notify_url', 'shop_id',
    ];

    /**
     * 按白名单过滤表单数据
     * @param array $data
     * @return array
     */
    protected static function filterFields($data)
    {
        $filtered = [];
        foreach (self::$writableFields as $field) {
            if (isset($data[$field])) {
                $filtered[$field] = $data[$field];
            }
        }
        // 配送范围支持数组提交（多选区域），统一转为顿号分隔字符串存储
        if (isset($filtered['delivery_range']) && is_array($filtered['delivery_range'])) {
            $filtered['delivery_range'] = implode('、', $filtered['delivery_range']);
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
        if (!empty($param['title'])) {
            $query = $query->where('title', 'like', '%' . trim($param['title']) . '%');
        }
        if (isset($param['country']) && $param['country'] !== '') {
            $query = $query->where('country', $param['country']);
        }
        if (isset($param['status']) && $param['status'] !== '') {
            $query = $query->where('status', $param['status']);
        }
        if (isset($param['shop_id']) && $param['shop_id'] !== '') {
            $query = $query->where('shop_id', $param['shop_id']);
        }
        $list = $query->order(['sort' => 'asc', 'delivery_id' => 'desc'])
            ->paginate(['list_rows' => 15, 'query' => request()->param()]);
        $this->attachListFields($list);
        return $list;
    }

    /**
     * 详情（返回模型对象，供控制器 edit()/setDelete() 调用）
     * @param int $deliveryId
     * @return static|null
     */
    public static function detail($deliveryId)
    {
        return (new static)->where('delivery_id', $deliveryId)->find();
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
        return Db::name('local_delivery')->insert($data) !== false;
    }

    /**
     * 编辑（直接更新，不依赖模型变更检测）
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
        return Db::name('local_delivery')
            ->where('delivery_id', $this['delivery_id'])
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
     * 批量补充展示字段（封面图地址 + 所属店铺名）
     * 视图同时兼容 $row['image']['file_path']、$row['image_url']、$row['shop']['shop_name']、$row['shop_name']
     * @param \think\Paginator $list
     */
    private function attachListFields($list)
    {
        $items = $list->items();
        foreach ($items as &$row) {
            $row = $row instanceof self ? $row->toArray() : (array)$row;
            $row['image_url'] = !empty($row['image_id']) ? BaseModel::getFileUrl($row['image_id']) : '';
            $row['image'] = ['file_path' => $row['image_url']];
            $shopName = Db::name('local_delivery_shop')
                ->where('shop_id', $row['shop_id'])
                ->value('shop_name');
            $row['shop_name'] = $shopName ?: '';
            $row['shop'] = ['shop_id' => $row['shop_id'], 'shop_name' => $row['shop_name']];
        }
        unset($row);
        $list->setCollection(Collection::make($items));
    }
}
