<?php
namespace app\store\model\recharge;

use app\common\model\BaseModel;
use think\facade\Db;
use think\Collection;

/**
 * 充值订单（服务端渲染版，供「充值记录」列表）
 */
class Order extends BaseModel
{
    protected $name = 'recharge_order';
    protected $pk = 'order_id';
    protected $updateTime = false;

    /** 支付状态 */
    protected static $payStatusText = [10 => '待支付', 20 => '已支付'];
    /** 充值类型（对齐 TP5 common\enum\recharge\order\RechargeType） */
    protected static $rechargeTypeText = [10 => '自定义金额', 20 => '套餐充值'];

    /**
     * 充值记录列表
     * @param array $query
     * @return \think\Paginator
     */
    public function getList($query = [])
    {
        $q = Db::name('recharge_order')->alias('m')
            ->where('m.wxapp_id', self::getWxappId());
        if (!empty($query['search'])) {
            $q->whereLike('m.order_no', '%' . trim($query['search']) . '%');
        }
        if (isset($query['pay_status']) && is_numeric($query['pay_status']) && (int)$query['pay_status'] > 0) {
            $q->where('m.pay_status', (int)$query['pay_status']);
        }
        if (isset($query['recharge_type']) && is_numeric($query['recharge_type']) && (int)$query['recharge_type'] > 0) {
            $q->where('m.recharge_type', (int)$query['recharge_type']);
        }
        $list = $q->order('m.create_time', 'desc')
            ->paginate(15, false, ['query' => request()->param()]);
        $items = $list->items();
        foreach ($items as &$item) {
            $item['pay_status'] = [
                'text'  => self::$payStatusText[$item['pay_status']] ?? '未知',
                'value' => (int)$item['pay_status'],
            ];
            $item['recharge_type'] = [
                'text'  => self::$rechargeTypeText[$item['recharge_type']] ?? '未知',
                'value' => (int)$item['recharge_type'],
            ];
            $item['user'] = Db::name('user')->where('user_id', $item['user_id'])
                ->field('user_id, nickName, avatarUrl')->find()
                ?: ['user_id' => $item['user_id'], 'nickName' => '', 'avatarUrl' => ''];
            // 充值套餐（自定义金额时为空）
            $item['order_plan'] = [];
            if (!empty($item['plan_id'])) {
                $item['order_plan'] = Db::name('recharge_plan')
                    ->where('plan_id', $item['plan_id'])->find() ?: [];
            }
        }
        $list->setCollection(Collection::make($items));
        return $list;
    }

    /**
     * 属性集（供筛选下拉）
     * 视图按 foreach($attributes[...] as $attr) 使用 $attr['value']/$attr['name']
     * @return array
     */
    public static function getAttributes()
    {
        $pay = [];
        foreach (self::$payStatusText as $value => $name) {
            $pay[$value] = ['value' => $value, 'name' => $name];
        }
        $type = [];
        foreach (self::$rechargeTypeText as $value => $name) {
            $type[$value] = ['value' => $value, 'name' => $name];
        }
        return [
            'pay_status'    => $pay,
            'rechargeType'  => $type,
        ];
    }
}
