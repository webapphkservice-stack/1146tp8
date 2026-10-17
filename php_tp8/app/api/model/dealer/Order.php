<?php
namespace app\api\model\dealer;

use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 分销订单模型（轻量版，直查 dealer_order + order）
 * 列表：当前分销商作为一/二/三级的订单佣金记录
 */
class Order
{
    /**
     * 分销订单列表
     * @param int $userId
     * @param int $settled -1=全部 10=已结算 20=未结算
     * @return array
     */
    public static function getList($userId, $settled = -1)
    {
        $wxappId = BaseModel::getWxappId();
        $q = Db::name('dealer_order')
            ->where('first_user_id', $userId)
            ->whereOr('second_user_id', $userId)
            ->whereOr('third_user_id', $userId)
            ->where('wxapp_id', $wxappId)
            ->where('is_invalid', 0);
        if ($settled == 10) {
            $q->where('is_settled', 1);
        } elseif ($settled == 20) {
            $q->where('is_settled', 0);
        }
        $rows = $q->order('create_time', 'desc')->select()->toArray();
        foreach ($rows as &$o) {
            $order = Db::name('order')
                ->where('order_id', $o['order_id'])
                ->field('order_no, pay_price, create_time')
                ->find();
            $o = array_merge($o, $order ?: []);
            foreach (['first_money', 'second_money', 'third_money', 'order_price'] as $k) {
                if (isset($o[$k])) {
                    $o[$k] = (float)$o[$k];
                }
            }
            foreach (['first_user_id', 'second_user_id', 'third_user_id', 'is_settled', 'is_invalid'] as $k) {
                if (isset($o[$k])) {
                    $o[$k] = (int)$o[$k];
                }
            }
        }
        return $rows;
    }
}
