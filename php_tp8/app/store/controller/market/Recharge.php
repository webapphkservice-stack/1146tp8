<?php

namespace app\store\controller\market;

use app\store\controller\Controller;
use app\store\model\Setting as SettingModel;

/**
 * 充值设置
 * Class Recharge
 * @package app\store\controller\market
 */
class Recharge extends Controller
{
    /**
     * 充值设置
     * @return array|bool|mixed
     */
    public function setting()
    {
        if (!$this->request->isAjax()) {
            $values = SettingModel::getItem('recharge');
            return $this->fetch('setting', ['values' => $values]);
        }
        $model = new SettingModel;
        if ($model->edit('recharge', $this->postData('recharge'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

}
