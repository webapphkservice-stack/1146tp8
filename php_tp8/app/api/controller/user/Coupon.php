<?php
namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\UserCoupon;

/**
 * 优惠券 - 我的优惠券 / 领取（第 8 批）
 * 路由：GET /api/user.coupon/lists、POST /api/user.coupon/receive（需登录）
 */
class Coupon extends Controller
{
    // 我的优惠券（data_type: not_use|is_use|is_expire|all）
    public function lists($data_type = 'all')
    {
        $user = $this->getUser(true);
        $list = UserCoupon::getList($user['user_id'], $data_type);
        return $this->renderSuccess(compact('list'));
    }

    // 领取优惠券
    public function receive()
    {
        $user = $this->getUser(true);
        $couponId = (int)$this->request->param('coupon_id', 0);
        $res = UserCoupon::receive($user, $couponId);
        if ($res !== true) {
            return $this->renderError($res['error']);
        }
        return $this->renderSuccess([], '领取成功');
    }
}
