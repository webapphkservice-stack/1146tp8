<?php

namespace app\store\controller\delivery;

use app\common\model\DeliveryOrder as DeliveryOrderModel;
use app\store\controller\Controller;

/**
 * 外送订单管理（商户后台）
 * 兼容路由 delivery.order/index（ThinkPHP 将带点的 delivery.order 解析为子目录控制器 delivery\Order）
 *
 * TP8 适配：
 *  - DeliveryOrderModel::get($id) → (new Model)->where(...)->find()
 *  - $this->success()/error() → renderSuccess()/renderError()（基类无 success()）
 */
class Order extends Controller
{
    /**
     * 外送订单列表
     */
    public function index()
    {
        $model = new DeliveryOrderModel;
        $list = $model->getListForStore($this->getWxappId(), $this->request->param());
        return $this->fetch('delivery/order', [
            'list' => $list,
        ]);
    }

    /**
     * 修改外送订单状态（店铺后台操作）
     * 与列表页 delivery.order/index 同属子目录控制器 delivery\Order，避免路由解析错位
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
