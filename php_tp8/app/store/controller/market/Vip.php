<?php

namespace app\store\controller\market;

use app\store\controller\Controller;
use app\store\model\Setting as SettingModel;
use app\common\model\Coupon as CouponModel;

/**
 * VIP会员设置
 * Class Vip
 * @package app\store\controller\market
 */
class Vip extends Controller
{
    /**
     * VIP设置
     * @return array|bool|mixed
     */
    public function setting()
    {
        $model = new SettingModel;
        if (!$this->request->isAjax()) {
            $values = $model->getItem('vip');
            // 运费券列表(优惠券类型为运费券)
            $couponList = CouponModel::where('coupon_type', '=', 30)
                ->where('is_delete', '=', 0)
                ->order(['sort' => 'asc', 'create_time' => 'desc'])
                ->select();
            return $this->fetch('setting', ['values' => $values, 'couponList' => $couponList]);
        }
        if ($model->edit('vip', $this->postData('vip'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

}
