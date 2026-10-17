<?php

namespace app\store\model;

use app\common\model\UserCoupon as UserCouponModel;

/**
 * 用户优惠券模型（门店后台）
 * Class UserCoupon
 * @package app\store\model
 */
class UserCoupon extends UserCouponModel
{
    /**
     * 获取领取记录列表（含用户信息）
     * @return \think\Paginator
     */
    public function getList()
    {
        return $this->with(['user'])
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }
}
