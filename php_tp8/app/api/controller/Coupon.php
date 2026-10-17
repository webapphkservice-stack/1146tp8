<?php
namespace app\api\controller;

use app\api\model\Coupon as CouponModel;
use app\api\controller\Controller;

/**
 * 优惠券 - 领券中心（第 8 批）
 * 路由：GET /api/coupon/lists（需 wxapp_id，免登录）
 */
class Coupon extends Controller
{
    public function lists()
    {
        $user = $this->getUser(false);
        $list = CouponModel::getList($user);
        return $this->renderSuccess(compact('list'));
    }
}
