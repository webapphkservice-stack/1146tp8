<?php

namespace app\api\model;

use app\common\model\VipPlan as VipPlanModel;
use app\common\model\VipOrder as VipOrderModel;
use app\common\model\Setting as SettingModel;
use app\common\model\User as UserModel;
use app\api\service\Payment as PaymentService;
use app\common\enum\order\PayType as PayTypeEnum;
use think\facade\Db;

/**
 * VIP会员业务逻辑（php_tp8 轻量版，移植自 php_my api/model/Vip）
 *
 * 说明：本类仅为业务逻辑容器，不绑定任何数据表（所有读写经 Db 门面或其它模型完成），
 *       故不继承 think\Model（否则构造时会按类名探测 mybaixin_vip 表而 500）。
 *
 * 与源版差异：
 *  - 余额流水 scene 用字面量 50（VIP开通），对应前端余额明细展示；
 *  - 第三方支付不再调用 php_my 的 PaymentService::wechat/alipay，
 *    改用 php_tp8 统一的 Payment::getParams($payType, $order, $user)；
 *  - 赠送运费券复用 Db 直写 user_coupon（php_tp8 coupon 表无 is_receive 字段，
 *    改为「券存在即赠送」并做重复防护）。
 */
class Vip
{
    /**
     * 会员套餐列表（隐藏 wxapp_id/create_time/update_time）
     * @return array
     */
    public function getPlans()
    {
        $list = (new VipPlanModel)->getEnableList()->toArray();
        foreach ($list as &$item) {
            unset($item['wxapp_id'], $item['create_time'], $item['update_time']);
        }
        return $list;
    }

    /**
     * 当前用户的VIP状态
     * @param $user
     * @return array
     */
    public function getStatus($user)
    {
        $isVip = !empty($user['is_vip']) && $user['vip_expire_time'] > time();
        return [
            'is_vip'           => $isVip ? 1 : 0,
            'vip_expire_time' => (int)($user['vip_expire_time'] ?? 0),
            'is_expire'       => $isVip ? 0 : 1,
        ];
    }

    /**
     * 今天是否为会员日
     * @return bool
     */
    public function isMemberDay()
    {
        $setting = SettingModel::getItem('vip');
        if (empty($setting['member_day']) || empty($setting['is_member_day_double'])) {
            return false;
        }
        return (int)date('j') === (int)$setting['member_day'];
    }

    /**
     * 获取积分倍数(会员日双倍积分)
     * @param $user
     * @return int
     */
    public function getPointsRatio($user)
    {
        $status = $this->getStatus($user);
        if (!$status['is_vip'] || !$this->isMemberDay()) {
            return 1;
        }
        $setting = SettingModel::getItem('vip');
        $ratio   = (int)($setting['double_points_ratio'] ?? 0);
        return $ratio > 1 ? $ratio : 2;
    }

    /**
     * 开通/续费VIP
     * @param $user 当前用户
     * @param $planId 套餐ID
     * @param $payType 支付方式(余额/微信/支付宝/Stripe/虚拟支付)
     * @param string $platform 支付平台 android / ios / h5（虚拟支付必须为 android/ios；h5 走 MWEB）
     * @param string $redirectUrl MWEB 支付完成后的回跳 URL
     * @return array|bool|string 第三方支付返回支付参数数组；余额支付成功返回true；失败返回错误信息
     */
    public function buy($user, $planId, $payType, $platform = 'android', $redirectUrl = '')
    {
        $plan = VipPlanModel::detail($planId);
        if (!$plan) {
            return '套餐不存在或已下架';
        }
        $plan = $plan->toArray();
        if (empty($plan['is_enable'])) {
            return '套餐不存在或已下架';
        }

        // 计算到期时间（续费在原有到期时间上累加，新开从当前时间起算）
        $expireTime = $user['vip_expire_time'] > time()
            ? $user['vip_expire_time'] + $plan['days'] * 86400
            : time() + $plan['days'] * 86400;
        $wxappId = UserModel::getWxappId();

        // 微信小程序虚拟支付(道具直购)：创建待支付订单，返回 wx.requestVirtualPayment 调起参数
        if ($payType == PayTypeEnum::VIRTUAL) {
            $vipOrder = new VipOrderModel;
            $orderNo  = $vipOrder->orderNo();
            $vipOrder->save([
                'order_no'         => $orderNo,
                'user_id'          => $user['user_id'],
                'plan_id'          => $plan['plan_id'],
                'plan_name'        => $plan['plan_name'],
                'price'            => $plan['price'],
                'pay_status'       => 10,
                'pay_type'         => PayTypeEnum::VIRTUAL,
                'vip_expire_time'  => $expireTime,
                'wxapp_id'         => $wxappId,
            ]);
            // 虚拟支付签名必需用户 session_key（登录时缓存于 token 对应会话）
            $token      = request()->param('token');
            $session    = $token ? \think\facade\Cache::get($token) : null;
            $sessionKey = is_array($session) ? ($session['session_key'] ?? '') : '';
            try {
                return (new \app\common\service\wechat\VirtualPayment($wxappId))
                    ->buildClientParams($user, $plan, $orderNo, $platform, $sessionKey, 'goods', 'vip_');
            } catch (\app\common\exception\BaseException $e) {
                return '虚拟支付下单失败：' . $e->getMessage();
            }
        }

        // 余额支付：直接扣款开通（同步完成）
        if ($payType == PayTypeEnum::BALANCE) {
            if ($user['balance'] < $plan['price']) {
                return '账户余额不足，请先充值';
            }
            $vipOrder = new VipOrderModel;
            Db::startTrans();
            try {
                // 1.扣除余额
                Db::name('user')->where('user_id', $user['user_id'])->dec('balance', $plan['price'])->update();
                // 2.余额流水
                Db::name('user_balance_log')->insert([
                    'user_id'     => $user['user_id'],
                    'scene'       => 50,
                    'money'       => -$plan['price'],
                    'describe'    => '开通VIP会员(' . $plan['plan_name'] . ')',
                    'remark'      => '',
                    'wxapp_id'    => $wxappId,
                    'create_time' => time(),
                ]);
                // 3.开通/续费VIP
                Db::name('user')->where('user_id', $user['user_id'])->update([
                    'is_vip'           => 1,
                    'vip_expire_time'  => $expireTime,
                ]);
                // 4.记录开通订单
                $vipOrder->save([
                    'order_no'         => $vipOrder->orderNo(),
                    'user_id'          => $user['user_id'],
                    'plan_id'          => $plan['plan_id'],
                    'plan_name'        => $plan['plan_name'],
                    'price'            => $plan['price'],
                    'pay_status'       => 20,
                    'pay_type'         => PayTypeEnum::BALANCE,
                    'vip_expire_time'  => $expireTime,
                    'wxapp_id'         => $wxappId,
                ]);
                // 5.赠送运费券
                $this->sendFreightCoupon($user, $wxappId);
                Db::commit();
                return true;
            } catch (\Exception $e) {
                Db::rollback();
                return '开通失败，请重试';
            }
        }

        // 第三方支付(微信/支付宝/Stripe)：创建待支付订单并返回支付参数，开通动作在异步回调中完成
        if (!in_array($payType, [PayTypeEnum::WECHAT, PayTypeEnum::ALIPAY, PayTypeEnum::STRIPE])) {
            return '不支持的支付方式';
        }
        $vipOrder = new VipOrderModel;
        $orderNo  = $vipOrder->orderNo();
        $vipOrder->save([
            'order_no'         => $orderNo,
            'user_id'          => $user['user_id'],
            'plan_id'          => $plan['plan_id'],
            'plan_name'        => $plan['plan_name'],
            'price'            => $plan['price'],
            'pay_status'       => 10,
            'pay_type'         => $payType,
            'vip_expire_time'  => $expireTime,
            'wxapp_id'         => $wxappId,
        ]);

        $order = [
            'order_no'   => $orderNo,
            'pay_price'  => $plan['price'],
            'wxapp_id'   => $wxappId,
            'user_id'    => $user['user_id'],
            'order_id'   => $vipOrder->order_id,
        ];
        try {
            return (new PaymentService)->getParams($payType, $order, $user, null, $platform, $redirectUrl);
        } catch (\Exception $e) {
            return '支付参数生成失败：' . $e->getMessage();
        }
    }

    /**
     * 赠送运费券（后台在 vip 设置中配置 freight_coupon_id）
     * @param $user
     * @param $wxappId
     */
    private function sendFreightCoupon($user, $wxappId)
    {
        $setting = SettingModel::getItem('vip');
        if (empty($setting['freight_coupon_id'])) {
            return;
        }
        $couponId = (int)$setting['freight_coupon_id'];
        $coupon   = Db::name('coupon')->where('coupon_id', $couponId)->where('wxapp_id', $wxappId)->find();
        if (empty($coupon)) {
            return;
        }
        $exist = Db::name('user_coupon')->where('user_id', $user['user_id'])->where('coupon_id', $couponId)->count();
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
            'user_id'             => $user['user_id'],
            'wxapp_id'            => $wxappId,
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
