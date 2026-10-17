<?php

namespace app\store\controller;

use app\store\model\Wxapp as WxappModel;

/**
 * 小程序管理
 * 菜单入口：wxapp/setting
 */
class Wxapp extends Controller
{
    /**
     * 小程序设置（appid/密钥/支付商户号/支付宝密钥等）
     * @return mixed
     */
    public function setting()
    {
        // 当前小程序信息
        $model = WxappModel::detail();
        if (empty($model)) {
            return $this->renderError('未找到当前小程序');
        }
        if (!$this->request->isAjax()) {
            return $this->fetch('setting', compact('model'));
        }
        if ($model->edit($this->postData('wxapp'))) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
}
