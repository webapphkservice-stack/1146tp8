<?php

namespace app\h5\controller;

use app\api\model\Coupon as CouponModel;
use think\facade\Db;

/**
 * 手机网站 - 我的（用户中心）
 *
 * 样式对齐 1146weixin/pages/user/index（微信小程序）。
 */
class User extends Controller
{
    /**
     * 我的页
     */
    public function index()
    {
        $h5User = $this->h5User();
        if (!$h5User) {
            return redirect((string)url('passport/login', ['redirect' => '/h5/user/index.html']));
        }

        // 从 user 表取完整资料（balance / points / coin / is_vip / vip_expire_time / grade_id / avatarUrl / nickName）
        $row = Db::name('user')->where('user_id', (int)$h5User['user_id'])
            ->where('wxapp_id', self::$wxapp_id)->find();
        $userInfo = $row ? $row : $h5User;

        // 订单角标计数（对齐小程序 orderCount）
        $userId = (int)$h5User['user_id'];
        $orderCount = [
            'all'      => Db::name('order')->where('user_id', $userId)->where('wxapp_id', self::$wxapp_id)
                ->where('is_delete', 0)->count(),
            'payment'  => Db::name('order')->where('user_id', $userId)->where('wxapp_id', self::$wxapp_id)
                ->where('is_delete', 0)->where('pay_status', 10)->count(),
            'received' => Db::name('order')->where('user_id', $userId)->where('wxapp_id', self::$wxapp_id)
                ->where('is_delete', 0)->where('pay_status', 20)->where('delivery_status', 20)->where('receipt_status', 10)->count(),
            'refund'   => Db::name('order')->where('user_id', $userId)->where('wxapp_id', self::$wxapp_id)
                ->where('is_delete', 0)->where('order_status', 'in', [30, 40, 50])->count(),
        ];

        // 余额/代币 两位小数
        $balanceText = number_format((float)($userInfo['balance'] ?? 0), 2);
        $coinText    = number_format((float)($userInfo['coin'] ?? 0), 2);
        $points      = (int)($userInfo['points'] ?? 0);

        // VIP 状态
        $isVip        = !empty($userInfo['is_vip']) && !empty($userInfo['vip_expire_time']) && (int)$userInfo['vip_expire_time'] > time();
        $vipExpireDate = $isVip ? date('Y-m-d', (int)$userInfo['vip_expire_time']) : '';

        return $this->fetch('user/index', [
            'storeName'     => $this->storeName(),
            'userInfo'      => $userInfo,
            'isLogin'       => true,
            'orderCount'    => $orderCount,
            'balanceText'   => $balanceText,
            'coinText'      => $coinText,
            'points'        => $points,
            'isVip'         => $isVip,
            'vipExpireDate' => $vipExpireDate,
            'defaultAvatar' => '/assets/h5/images/default-avatar.jpg',
        ]);
    }

    /**
     * 领券中心 / 我的优惠券
     */
    public function coupon()
    {
        $userInfo = $this->h5User();
        $list = CouponModel::getList($userInfo);
        return $this->fetch('user/coupon', [
            'list'      => $list,
            'storeName' => $this->storeName(),
        ]);
    }
}
