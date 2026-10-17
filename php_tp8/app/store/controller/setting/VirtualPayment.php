<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\common\model\Setting as SettingModel;
use app\store\model\Setting as StoreSettingModel;

/**
 * 虚拟支付配置
 * Class VirtualPayment
 * @package app\store\controller\setting
 */
class VirtualPayment extends Controller
{
    /**
     * 虚拟支付配置
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        if (!$this->request->isAjax()) {
            $values = SettingModel::getItem('virtual_payment');
            return $this->fetch('index', compact('values'));
        }
        // 保存配置（写入 yoshop_setting 表 key=virtual_payment）
        $data = $this->postData('virtual_payment');
        $model = new StoreSettingModel;
        if ($model->edit('virtual_payment', $data)) {
            return $this->renderSuccess('更新成功', url('setting.virtual_payment/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
}
