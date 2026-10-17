<?php
namespace app\api\model;

use app\common\model\User as UserModel;
use think\facade\Db;
use think\Model;

/**
 * 充值订单模型——php_tp8 轻量版
 * 生成充值订单（RC 前缀订单号），供 Payment 生成支付参数；到账由 RechargePaySuccess 在回调中处理。
 */
class RechargeOrder extends Model
{
    protected $name = 'recharge_order';

    /**
     * 创建充值订单
     * @param array $user
     * @param int   $planId      套餐 id（0=自定义金额）
     * @param float $customMoney 自定义金额
     * @return array|array{error:string}
     */
    public static function createOrder($user, $planId, $customMoney)
    {
        $wxappId = UserModel::getWxappId();
        $planId = (int)$planId;
        $data = [
            'order_no'     => 'RC' . time() . rand(1000, 9999),
            'user_id'      => $user['user_id'],
            'recharge_type' => $planId > 0 ? 20 : 10,
            'plan_id'      => $planId,
            'pay_price'    => 0,
            'gift_money'   => 0,
            'actual_money' => 0,
            'pay_status'   => 10,
            'pay_time'     => 0,
            'transaction_id' => '',
            'wxapp_id'     => $wxappId,
            'create_time'  => time(),
            'update_time'  => time(),
        ];

        if ($planId > 0) {
            $plan = Db::name('recharge_plan')
                ->where('plan_id', $planId)
                ->where('wxapp_id', $wxappId)
                ->where('is_delete', 0)
                ->find();
            if (empty($plan)) {
                return ['error' => '套餐不存在'];
            }
            $data['pay_price']    = $plan['money'];
            $data['gift_money']   = $plan['gift_money'];
            $data['actual_money'] = bcadd((string)$plan['money'], (string)$plan['gift_money'], 2);
        } else {
            $money = floatval($customMoney);
            if ($money <= 0) {
                return ['error' => '请输入正确的充值金额'];
            }
            $data['pay_price']    = $money;
            $data['actual_money'] = $money;
        }

        $orderId = Db::name('recharge_order')->insertGetId($data);
        if ($planId > 0) {
            Db::name('recharge_order_plan')->insert([
                'order_id'    => $orderId,
                'plan_id'     => $plan['plan_id'],
                'plan_name'   => $plan['plan_name'],
                'money'       => $plan['money'],
                'gift_money'  => $plan['gift_money'],
                'wxapp_id'    => $wxappId,
                'create_time' => time(),
                'update_time' => time(),
            ]);
        }
        $data['order_id'] = $orderId;
        return $data;
    }
}
