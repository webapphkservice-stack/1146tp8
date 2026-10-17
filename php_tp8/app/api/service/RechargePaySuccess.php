<?php
namespace app\api\service;

use think\facade\Db;

/**
 * 充值订单支付成功收口（第 8 批）
 * 与 OrderStateMachine 并列，专门处理 RC 前缀的充值订单号：
 *   - 标记充值订单已支付（幂等）；
 *   - user.balance 增加 actual_money；
 *   - 写入 user_balance_log（scene=10 充值）。
 * 网关验签由对应 Notify 控制器负责（见各 Notify* 的 TODO）。
 */
class RechargePaySuccess
{
    private $error = '';

    public function getError()
    {
        return $this->error;
    }

    public function paySuccess($orderNo, $transactionId = '')
    {
        $order = Db::name('recharge_order')->where('order_no', $orderNo)->find();
        if (empty($order)) {
            $this->error = '充值订单不存在';
            return false;
        }
        if ($order['pay_status'] == 20) {
            return true; // 幂等：已处理
        }
        if ($order['pay_status'] != 10) {
            $this->error = '充值订单状态异常';
            return false;
        }

        Db::startTrans();
        try {
            Db::name('recharge_order')->where('order_id', $order['order_id'])->update([
                'pay_status'    => 20,
                'pay_time'      => time(),
                'transaction_id' => $transactionId,
                'update_time'   => time(),
            ]);
            Db::name('user')->where('user_id', $order['user_id'])->inc('balance', $order['actual_money'])->update();
            Db::name('user_balance_log')->insert([
                'user_id'    => $order['user_id'],
                'scene'      => 10,
                'money'      => $order['actual_money'],
                'describe'   => '用户充值',
                'remark'     => '',
                'wxapp_id'   => $order['wxapp_id'],
                'create_time' => time(),
            ]);
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollback();
            $this->error = '处理失败：' . $e->getMessage();
            return false;
        }
    }
}
