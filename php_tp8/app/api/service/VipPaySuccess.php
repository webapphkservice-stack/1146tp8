<?php

namespace app\api\service;

use think\facade\Db;

/**
 * VIP开通订单支付成功收口（仿 RechargePaySuccess）
 *   - 标记 vip_order 已支付（幂等）；
 *   - user.is_vip=1、vip_expire_time=本次到期时间；
 *   - 赠送运费券（若后台 vip 设置配置了 freight_coupon_id）。
 *
 * 网关验签由各 Notify* 控制器负责，本服务仅做业务逻辑收口。
 */
class VipPaySuccess
{
    /** @var string 业务错误信息 */
    private $error = '';

    public function getError()
    {
        return $this->error;
    }

    /**
     * VIP订单支付成功处理
     * @param string $orderNo
     * @param string $transactionId
     * @return bool
     */
    public function paySuccess($orderNo, $transactionId = '')
    {
        $order = Db::name('vip_order')->where('order_no', $orderNo)->find();
        if (empty($order)) {
            $this->error = 'VIP订单不存在';
            return false;
        }
        if ($order['pay_status'] == 20) {
            return true; // 幂等：已处理
        }
        if ($order['pay_status'] != 10) {
            $this->error = 'VIP订单状态异常';
            return false;
        }

        Db::startTrans();
        try {
            // 标记已支付
            Db::name('vip_order')->where('order_id', $order['order_id'])->update([
                'pay_status'  => 20,
                'update_time' => time(),
            ]);
            // 开通会员
            Db::name('user')->where('user_id', $order['user_id'])->update([
                'is_vip'          => 1,
                'vip_expire_time' => $order['vip_expire_time'],
            ]);
            // 赠送运费券
            $this->sendFreightCoupon($order);
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollback();
            $this->error = '处理失败：' . $e->getMessage();
            return false;
        }
    }

    /**
     * 赠送运费券
     * @param array $order
     */
    private function sendFreightCoupon($order)
    {
        $row = Db::name('setting')->where('key', 'vip')->where('wxapp_id', $order['wxapp_id'])->value('values');
        $setting = $row ? json_decode($row, true) : [];
        if (empty($setting['freight_coupon_id'])) {
            return;
        }
        $couponId = (int)$setting['freight_coupon_id'];
        $coupon   = Db::name('coupon')->where('coupon_id', $couponId)->where('wxapp_id', $order['wxapp_id'])->find();
        if (empty($coupon)) {
            return;
        }
        $exist = Db::name('user_coupon')->where('user_id', $order['user_id'])->where('coupon_id', $couponId)->count();
        if ($exist > 0) {
            return;
        }
        $data = [
            'coupon_id'           => $coupon['coupon_id'],
            'name'                => $coupon['name'],
            'color'               => $coupon['color'],
            'coupon_type'         => $coupon['coupon_type'],
            'reduce_price'        => $coupon['reduce_price'],
            'discount'            => $coupon['discount'],
            'min_price'           => $coupon['min_price'],
            'expire_type'         => $coupon['expire_type'],
            'expire_day'          => $coupon['expire_day'],
            'start_time'          => $coupon['start_time'],
            'end_time'            => $coupon['end_time'],
            'apply_range'         => $coupon['apply_range'],
            'apply_range_config'  => $coupon['apply_range_config'],
            'is_expire'           => 0,
            'is_use'              => 0,
            'user_id'             => $order['user_id'],
            'wxapp_id'            => $order['wxapp_id'],
            'create_time'         => time(),
            'update_time'         => time(),
        ];
        if ($coupon['expire_type'] == 10) {
            $data['start_time'] = time();
            $data['end_time']   = time() + (int)$coupon['expire_day'] * 86400;
        }
        Db::name('user_coupon')->insert($data);
    }
}
