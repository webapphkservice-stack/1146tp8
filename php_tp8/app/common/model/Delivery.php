<?php
namespace app\common\model;

use think\facade\Db;

/**
 * 配送模板（运费模板）模型
 *
 * TP8 适配要点：
 *  - Request::instance()->request() → request()->param()
 *  - self::get($id, ['rule']) → where()->find()（TP8 已移除静态 get）
 *  - 不使用 hasMany('DeliveryRule') 关联（TP8 未迁移 DeliveryRule 模型），
 *    改为 Db 直查 delivery_rule 表，避免依赖缺失类导致 fatal
 */
class Delivery extends BaseModel
{
    protected $name = 'delivery';
    protected $pk = 'delivery_id';

    /** 计费方式 */
    protected static $methodData = [10 => '按件数', 20 => '按重量'];

    /**
     * 计费方式获取器
     * @param int $value
     * @return array
     */
    public function getMethodAttr($value)
    {
        return [
            'text'  => self::$methodData[$value] ?? '未知',
            'value' => $value,
        ];
    }

    /**
     * 获取全部记录（含区域规则）
     * @return array
     */
    public static function getAll()
    {
        $list = (new static)->order(['sort' => 'asc', 'delivery_id' => 'desc'])->select()->toArray();
        if (empty($list)) {
            return [];
        }
        $ids = array_column($list, 'delivery_id');
        $rules = Db::name('delivery_rule')->whereIn('delivery_id', $ids)->select()->toArray();
        $map = [];
        foreach ($rules as $r) {
            $map[$r['delivery_id']][] = $r;
        }
        foreach ($list as &$item) {
            $item['rule'] = $map[$item['delivery_id']] ?? [];
        }
        unset($item);
        return $list;
    }

    /**
     * 获取列表（后台分页）
     * @return \think\Paginator
     */
    public function getList()
    {
        return $this->order(['sort' => 'asc', $this->getPk() => 'desc'])
            ->paginate(['list_rows' => 15, 'query' => request()->param()]);
    }

    /**
     * 模板详情（返回模型对象，供 store\model\Delivery 的 edit/remove/getFormList 调用）
     * @param int $deliveryId
     * @return static|null
     */
    public static function detail($deliveryId)
    {
        return (new static)->where('delivery_id', $deliveryId)->find();
    }

    /**
     * 根据模板id集获取模板列表（含区域规则）
     * @param array $deliveryIds
     * @return array
     */
    public function getListByIds($deliveryIds)
    {
        $list = $this->where('delivery_id', 'in', $deliveryIds)->select()->toArray();
        if (empty($list)) {
            return [];
        }
        $rules = Db::name('delivery_rule')->whereIn('delivery_id', $deliveryIds)->select()->toArray();
        $map = [];
        foreach ($rules as $r) {
            $map[$r['delivery_id']][] = $r;
        }
        foreach ($list as &$item) {
            $item['rule'] = $map[$item['delivery_id']] ?? [];
        }
        unset($item);
        return $list;
    }
}
