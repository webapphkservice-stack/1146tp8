<?php

namespace app\store\controller\market;

use app\store\controller\Controller;
use app\store\model\Setting as SettingModel;
use app\store\model\user\PointsLog as PointsLogModel;

/**
 * 积分管理
 * Class Points
 * @package app\store\controller\market
 */
class Points extends Controller
{
    /**
     * 积分设置
     * @return array|bool|mixed
     */
    public function setting()
    {
        if (!$this->request->isAjax()) {
            $values = SettingModel::getItem('points');
            // PHP8 下缺失键会触发未定义索引告警，这里用默认值兜底
            $default = [
                'points_name'          => '积分',
                'describe'             => '',
                'is_shopping_gift'     => 1,
                'gift_ratio'           => 100,
                'is_signin_open'       => 0,
                'signin_points'        => 10,
                'is_shopping_discount' => 0,
                'discount'             => [
                    'discount_ratio'   => 0.01,
                    'full_order_price' => 0,
                    'max_money_ratio'  => 10,
                ],
            ];
            $values = array_replace_recursive($default, $values);
            return $this->fetch('setting', compact('values'));
        }
        $model = new SettingModel;
        if ($model->edit('points', $this->postData('points'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 积分明细
     * @return mixed
     */
    public function log()
    {
        // 积分明细列表
        $model = new PointsLogModel;
        $list = $model->getList($this->request->param());
        return $this->fetch('log', compact('list'));
    }

}
