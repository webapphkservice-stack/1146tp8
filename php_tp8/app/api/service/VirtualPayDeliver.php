<?php

namespace app\api\service;

use think\facade\Db;
use think\facade\Log;

/**
 * 微信小程序虚拟支付（xpay）业务收口
 *
 * 为「消息推送回调」和「前端主动查询确认」两条路径提供统一的发货/充值逻辑，
 * 避免两套代码分叉（如金额核对遗漏、幂等重复实现）。
 *
 * 路由入口：
 *   - 推送回调：POST /api/notify/virtual  → Notify::virtual() → VirtualPayDeliver::dispatch()
 *   - 主动查询：POST /api/user.vip/confirmPay / api/user.coin/confirmPay → VirtualPayDeliver::confirmVip/confirmCoin()
 */
class VirtualPayDeliver
{
    /**
     * 按订单号前缀/表归属分发（回调路径入口）
     *
     * @param string $orderNo        商户订单号
     * @param string $transactionId  微信交易号（推送里可能为 MchOrderNo）
     * @param int    $paidAmount     微信回传的实际支付金额（分），0 表示不核对
     * @return array [bool 成功, string 错误信息]
     */
    public function dispatch($orderNo, $transactionId = '', $paidAmount = 0)
    {
        if ($orderNo === '') {
            return [false, 'missing order_no'];
        }
        // 代币充值订单（CO 前缀）
        if (strpos($orderNo, 'CO') === 0) {
            return $this->handleCoin($orderNo, $transactionId, $paidAmount);
        }
        // VIP 开通订单
        if (Db::name('vip_order')->where('order_no', $orderNo)->count()) {
            return $this->handleVip($orderNo, $transactionId, $paidAmount);
        }
        return [false, 'order not found'];
    }

    /**
     * 处理 VIP 开通（虚拟支付）
     */
    public function handleVip($orderNo, $transactionId = '', $paidAmount = 0)
    {
        $order = Db::name('vip_order')->where('order_no', $orderNo)->find();
        if (empty($order)) {
            Log::write('[VirtualPayDeliver] VIP订单不存在：' . $orderNo, 'warning');
            return [false, 'vip order not found'];
        }
        // 幂等：已开通直接成功
        if ((int)$order['pay_status'] === 20) {
            return [true, ''];
        }
        // 非待支付态拦截（避免重复推送/查询触发状态错乱）
        if ((int)$order['pay_status'] !== 10) {
            return [false, 'order state conflict'];
        }
        // 金额核对（微信回传 ActualPrice 单位为分，订单 pay_price 单位为元）
        if ($paidAmount > 0) {
            $orderFen = (int)round((float)$order['price'] * 100);
            if ($orderFen > 0 && $paidAmount !== $orderFen) {
                Log::write('[VirtualPayDeliver] VIP金额核对失败 ' . $orderNo
                    . " paid={$paidAmount}fen order={$orderFen}fen", 'warning');
                return [false, 'amount mismatch'];
            }
        }
        try {
            // 复用 VipPaySuccess（已做幂等 + 开通会员 + 赠送运费券）
            $srv = new VipPaySuccess;
            $ok  = $srv->paySuccess($orderNo, $transactionId);
            if (!$ok) {
                return [false, $srv->getError() ?: 'VIP开通失败'];
            }
            Log::write('[VirtualPayDeliver] VIP开通成功 ' . $orderNo, 'info');
            return [true, ''];
        } catch (\Throwable $e) {
            Log::write('[VirtualPayDeliver] VIP开通异常 ' . $orderNo . ': ' . $e->getMessage(), 'error');
            return [false, 'process error'];
        }
    }

    /**
     * 处理代币充值
     */
    public function handleCoin($orderNo, $transactionId = '', $paidAmount = 0)
    {
        $order = Db::name('coin_order')->where('order_no', $orderNo)->find();
        if (empty($order)) {
            Log::write('[VirtualPayDeliver] 代币订单不存在：' . $orderNo, 'warning');
            return [false, 'coin order not found'];
        }
        // 幂等：已充值直接成功
        if ((int)$order['pay_status'] === 20) {
            return [true, ''];
        }
        if ((int)$order['pay_status'] !== 10) {
            return [false, 'order state conflict'];
        }
        // 金额核对（代币订单 money 单位为元，微信回传 ActualPrice 为分）
        if ($paidAmount > 0) {
            $orderFen = (int)round((float)$order['money'] * 100);
            if ($orderFen > 0 && $paidAmount !== $orderFen) {
                Log::write('[VirtualPayDeliver] 代币金额核对失败 ' . $orderNo
                    . " paid={$paidAmount}fen order={$orderFen}fen", 'warning');
                return [false, 'amount mismatch'];
            }
        }

        Db::startTrans();
        try {
            // 1. 更新订单支付状态
            Db::name('coin_order')->where('order_no', $orderNo)->update([
                'pay_status'      => 20,
                'transaction_id'  => $transactionId,
                'pay_time'        => time(),
            ]);
            // 2. 累加用户代币余额
            Db::name('user')->where('user_id', $order['user_id'])->inc('coin', (int)$order['coin_num'])->update();
            // 3. 流水（若 coin_balance_log 表存在则写）
            if (Db::query("SHOW TABLES LIKE 'mybaixin_coin_balance_log'")) {
                Db::name('coin_balance_log')->insert([
                    'user_id'     => $order['user_id'],
                    'coin'        => (int)$order['coin_num'],
                    'describe'    => '代币充值（订单号：' . $orderNo . '）',
                    'wxapp_id'    => (int)($order['wxapp_id'] ?? 0),
                    'create_time' => time(),
                ]);
            }
            Db::commit();
            Log::write('[VirtualPayDeliver] 代币充值成功 ' . $orderNo . ' +' . $order['coin_num'], 'info');
            return [true, ''];
        } catch (\Throwable $e) {
            Db::rollback();
            Log::write('[VirtualPayDeliver] 代币充值异常 ' . $orderNo . ': ' . $e->getMessage(), 'error');
            return [false, 'process error'];
        }
    }
}
