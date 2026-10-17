<?php

namespace app\store\controller;

use app\store\model\CoinOrder as CoinOrderModel;
use app\common\model\Setting as SettingModel;
use app\store\model\Setting as StoreSettingModel;

/**
 * 代币管理（后台）
 * Class Coin
 * @package app\store\controller
 */
class Coin extends Controller
{
    /**
     * 代币充值订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $filter = [
            'pay_status' => input('pay_status/d', 0),
            'keyword' => input('keyword/s', ''),
        ];
        $list = (new CoinOrderModel)->getList($filter);
        return $this->fetch('index', compact('list', 'filter'));
    }

    /**
     * 虚拟支付设置（代币充值入口）
     *
     * 代币充值走微信虚拟支付，配置与全局「设置-虚拟支付配置」共用同一份
     * setting.virtual_payment 数据，此处仅提供代币模块内的入口页。
     */
    public function setting()
    {
        if (!$this->request->isAjax()) {
            $values = SettingModel::getItem('virtual_payment');
            return $this->fetch('setting', compact('values'));
        }
        $data = $this->postData('virtual_payment');
        $model = new StoreSettingModel;
        if ($model->edit('virtual_payment', $data)) {
            return $this->renderSuccess('更新成功', url('coin/setting'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
}
