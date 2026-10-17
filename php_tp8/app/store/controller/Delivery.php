<?php

namespace app\store\controller;

use app\common\model\LocalDelivery as DeliveryModel;
use app\common\model\LocalDeliveryShop as ShopModel;
use app\common\model\DeliveryOrder as DeliveryOrderModel;
use app\common\model\Setting;
use think\facade\Cache;
use think\facade\Db;

/**
 * 马来西亚本地外送管理（商户后台）
 *
 * TP8 适配要点：
 *  - use think\Cache → think\facade\Cache
 *  - DeliveryOrderModel::get($id) → (new Model)->where(...)->find()（TP8 已移除静态 get）
 *  - $this->success() / $this->error() → renderSuccess() / renderError()
 *    （TP8 的 store\Controller 基类只有 error() 没有 success()，且 error() 渲染 HTML 错误页，
 *      在 Ajax 提交下应统一用返回 json 的 renderError/renderSuccess）
 *  - Setting 保存：yoshop_setting 无自增主键，改为按 key + wxapp_id 做 Db upsert
 */
class Delivery extends Controller
{
    /**
     * 列表
     */
    public function index()
    {
        $model = new DeliveryModel;
        $list = $model->getList($this->request->param());
        return $this->fetch('index', [
            'list' => $list,
            'shops' => ShopModel::getListForSelect(),
        ]);
    }

    /**
     * 新增
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('form', [
                'model' => null,
                'shops' => ShopModel::getListForSelect(),
                'regions' => DeliveryModel::getKlRegions(),
            ]);
        }
        $data = $this->postData('delivery');
        $model = new DeliveryModel;
        if ($model->add($data)) {
            return $this->renderSuccess('添加成功', (string)url('delivery/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑
     */
    public function edit($delivery_id = null)
    {
        if (!$this->request->isAjax()) {
            $model = DeliveryModel::detail($delivery_id);
            if (!$model) {
                return $this->renderError('记录不存在');
            }
            return $this->fetch('form', [
                'model' => $model,
                'shops' => ShopModel::getListForSelect(),
                'regions' => DeliveryModel::getKlRegions(),
            ]);
        }
        $data = $this->postData('delivery');
        if (empty($data) || !is_array($data)) {
            return $this->renderError('未接收到表单数据，请勿直接刷新提交');
        }
        $model = DeliveryModel::detail($delivery_id);
        if (!$model) {
            return $this->renderError('记录不存在');
        }
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功', (string)url('delivery/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除
     */
    public function delete($delivery_id)
    {
        $model = DeliveryModel::detail($delivery_id);
        if (!$model) {
            return $this->renderError('记录不存在');
        }
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError('删除失败');
    }

    /**
     * 外送设置
     */
    public function setting()
    {
        $wxappId = $this->getWxappId();
        if (!$this->request->isAjax()) {
            $values = Setting::getItem('delivery') ?: [];
            return $this->fetch('setting', [
                'values' => $values,
            ]);
        }
        $data = $this->postData('delivery');
        // yoshop_setting 无自增主键 id，按 key + wxapp_id 显式 upsert，避免 WHERE 失效导致保存失败
        $exists = Db::name('setting')
            ->where('key', 'delivery')
            ->where('wxapp_id', $wxappId)
            ->find();
        $save = [
            'values'      => json_encode($data, JSON_UNESCAPED_UNICODE),
            'update_time' => time(),
        ];
        if ($exists) {
            $ok = Db::name('setting')
                ->where('key', 'delivery')
                ->where('wxapp_id', $wxappId)
                ->update($save) !== false;
        } else {
            $ok = Db::name('setting')->insert(array_merge($save, [
                'key'       => 'delivery',
                'describe'  => '马来西亚本地外送设置',
                'wxapp_id'  => $wxappId,
            ])) !== false;
        }
        if ($ok) {
            // 清除设置缓存，否则刷新后读到旧缓存导致字段为空
            Cache::rm('setting_' . $wxappId);
            return $this->renderSuccess('保存成功', (string)url('delivery/setting'));
        }
        return $this->renderError('保存失败');
    }

    /* ---------------------------------- 店铺管理 ---------------------------------- */

    /**
     * 店铺管理-列表
     */
    public function shop()
    {
        $model = new ShopModel;
        $list = $model->getList($this->request->param());
        return $this->fetch('shop', [
            'list' => $list,
        ]);
    }

    /**
     * 店铺管理-新增
     */
    public function shopAdd()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('shop_form', [
                'model' => null,
            ]);
        }
        $data = $this->postData('shop');
        $model = new ShopModel;
        if ($model->add($data)) {
            return $this->renderSuccess('添加成功', (string)url('delivery/shop'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 店铺管理-编辑
     */
    public function shopEdit($shop_id = null)
    {
        if (!$this->request->isAjax()) {
            $model = ShopModel::detail($shop_id);
            if (!$model) {
                return $this->renderError('店铺不存在');
            }
            return $this->fetch('shop_form', [
                'model' => $model,
            ]);
        }
        $data = $this->postData('shop');
        if (empty($data) || !is_array($data)) {
            return $this->renderError('未接收到表单数据，请勿直接刷新提交');
        }
        $model = ShopModel::detail($shop_id);
        if (!$model) {
            return $this->renderError('店铺不存在');
        }
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功', (string)url('delivery/shop'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 店铺管理-删除
     */
    public function shopDelete($shop_id)
    {
        $model = ShopModel::detail($shop_id);
        if (!$model) {
            return $this->renderError('店铺不存在');
        }
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError('删除失败');
    }

    /* ---------------------------------- 外送订单 ---------------------------------- */

    /**
     * 外送订单列表（商家后台）
     */
    public function order()
    {
        $model = new DeliveryOrderModel;
        $list = $model->getListForStore($this->getWxappId(), $this->request->param());
        return $this->fetch('order', [
            'list' => $list,
        ]);
    }

    /**
     * 修改外送订单状态（店铺后台操作）
     */
    public function orderState($order_id = 0)
    {
        $orderId = (int)$this->request->post('order_id', $order_id);
        $status = (int)$this->request->post('status', -1);
        if ($orderId <= 0) {
            return $this->renderError('缺少参数：order_id');
        }
        // 已完成(2)仅限下单人在小程序端确认收货触发，店铺后台不可设置
        if (!in_array($status, [0, 1, 3], true)) {
            return $this->renderError('请选择有效的订单状态');
        }
        $model = (new DeliveryOrderModel)->where('order_id', $orderId)->find();
        if (!$model || $model['wxapp_id'] != $this->getWxappId()) {
            return $this->renderError('订单不存在或无权操作');
        }
        if (!$model->setStatusByStore($status)) {
            return $this->renderError($model->getError() ?: '操作失败');
        }
        return $this->renderSuccess('操作成功', (string)url('delivery.order/index'));
    }
}
