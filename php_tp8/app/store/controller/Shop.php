<?php

namespace app\store\controller;

use app\store\model\store\Shop as ShopModel;
use app\store\model\store\shop\Clerk as ClerkModel;
use app\common\model\ShopDeliveryArea as ShopDeliveryAreaModel;

/**
 * 门店管理
 * Class Shop
 * @package app\store\controller
 */
class Shop extends Controller
{
    /**
     * 门店列表
     * @return mixed
     */
    public function index()
    {
        $model = new ShopModel;
        $list = $model->getList($this->request->get());
        return $this->fetch('index', compact('list'));
    }

    /**
     * 腾讯地图坐标选取器（iframe 内嵌，禁用布局）
     * @return mixed
     */
    public function getpoint()
    {
        $this->layout(false);
        return $this->fetch('getpoint');
    }

    /**
     * 添加门店
     * @return array|bool|mixed
     */
    public function add()
    {
        $model = new ShopModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        if ($model->add($this->postData('shop'))) {
            return $this->renderSuccess('添加成功', url('shop/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑门店
     * @param $shop_id
     * @return array|bool|mixed
     */
    public function edit($shop_id)
    {
        $model = ShopModel::detail($shop_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        if ($model->edit($this->postData('shop'))) {
            return $this->renderSuccess('更新成功', url('shop/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除门店
     * @param $shop_id
     * @return array
     */
    public function delete($shop_id)
    {
        $model = ShopModel::detail($shop_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 门店配送(送水)设置
     * @param int $shop_id
     * @return array|mixed
     */
    public function delivery($shop_id)
    {
        $model = ShopModel::detail($shop_id);
        if (!$this->request->isAjax()) {
            $areaList = ShopDeliveryAreaModel::getListByShop($shop_id);
            $clerkList = ClerkModel::getListByShop($shop_id);
            return $this->fetch('delivery', compact('model', 'areaList', 'clerkList'));
        }
        $shopData = $this->postData('shop');
        // 配送时段由两个时间选择器组装为 HH:mm-HH:mm
        if (array_key_exists('delivery_time_start', $shopData) || array_key_exists('delivery_time_end', $shopData)) {
            $start = trim($shopData['delivery_time_start'] ?? '');
            $end = trim($shopData['delivery_time_end'] ?? '');
            $shopData['delivery_time_range'] = ($start && $end) ? "{$start}-{$end}" : '';
            unset($shopData['delivery_time_start'], $shopData['delivery_time_end']);
        }
        // 强校验：开启押桶押金时必须同时开启空桶回收
        if (!empty($shopData['deposit_enabled']) && empty($shopData['empty_bucket_recycle'])) {
            return $this->renderError('开启押桶押金时，必须同时开启空桶回收（否则用户无法归还空桶退押金）');
        }
        if ($model->editDelivery($shopData)) {
            return $this->renderSuccess('保存成功', url('shop/index'));
        }
        return $this->renderError($model->getError() ?: '保存失败');
    }

    /**
     * 添加门店配送区域
     * @param int $shop_id
     * @return array
     */
    public function addArea($shop_id)
    {
        $data = $this->postData('area');
        if (empty($data['province_id']) || empty($data['city_id'])) {
            return $this->renderError('请选择配送区域的省份与城市');
        }
        $wxappId = ShopModel::detail($shop_id)['wxapp_id'] ?? 0;
        if (!ShopDeliveryAreaModel::addArea(
            $shop_id,
            $wxappId,
            (int)$data['province_id'],
            (int)$data['city_id'],
            !empty($data['region_id']) ? (int)$data['region_id'] : 0
        )) {
            return $this->renderError('添加失败');
        }
        return $this->renderSuccess('添加成功', url('shop/delivery', ['shop_id' => $shop_id]));
    }

    /**
     * 删除门店配送区域
     * @param int $id
     * @param int $shop_id
     * @return array
     */
    public function delArea($id, $shop_id)
    {
        if (!ShopDeliveryAreaModel::removeArea($id, $shop_id)) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 设置/取消店员为配送员（送水）
     * @param int $clerk_id
     * @return array
     */
    public function setDeliveryer($clerk_id)
    {
        $clerk = ClerkModel::detail($clerk_id);
        if (empty($clerk)) {
            return $this->renderError('店员不存在');
        }
        $isDeliveryer = $this->request->post('is_deliveryer/d', 0);
        if ($clerk->save(['is_deliveryer' => $isDeliveryer ? 1 : 0]) === false) {
            return $this->renderError('设置失败');
        }
        return $this->renderSuccess('设置成功');
    }
}
