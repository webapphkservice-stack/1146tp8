<?php
namespace app\api\controller;

use app\api\model\DeliveryOrder as DeliveryOrderModel;
use app\common\library\DeliveryWebhookQueue;

/**
 * 外送订单（小程序端，移植自 TP5 api/controller/DeliveryOrder.php）
 *
 * 路由：delivery_order/lists | delivery_order/create | delivery_order/confirm
 *      （同时注册 delivery.order/* 点号别名，兼容小程序调用面）
 */
class DeliveryOrder extends Controller
{
    /**
     * 我的外送订单列表
     * @param int $status 不传或 -1 表示全部；0待接单 1配送中 2已完成 3已取消
     */
    public function lists($status = -1)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->renderError('请先登录');
        }
        $model = new DeliveryOrderModel;
        $result = $model->getMyList($user['user_id'], (int)$status);
        return $this->renderSuccess($result);
    }

    /**
     * 小程序端创建外送订单
     * 接收 delivery_id / address_id / remark，价格与服务可用性均在后端重新校验，不信任前端
     */
    public function create()
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->renderError('请先登录');
        }
        $deliveryId = (int)$this->request->post('delivery_id', 0);
        $addressId = (int)$this->request->post('address_id', 0);
        $remark = (string)$this->request->post('remark', '');
        $model = new DeliveryOrderModel;
        $result = $model->createOrder($user, $deliveryId, $addressId, $remark);
        if ($result === false) {
            return $this->renderError($model->getError() ?: '下单失败');
        }
        return $this->renderSuccess($result, '下单成功');
    }

    /**
     * 用户确认收货（小程序端）
     * 状态置为「已完成」，并同步回写商户侧 webhook，保证两端状态一致
     */
    public function confirm($order_id = 0)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->renderError('请先登录');
        }
        $orderId = (int)$this->request->post('order_id', $order_id);
        if ($orderId <= 0) {
            return $this->renderError('缺少参数：order_id');
        }
        $model = DeliveryOrderModel::find($orderId);
        if (!$model || $model['user_id'] != $user['user_id'] || $model['wxapp_id'] != $this->getWxappId()) {
            return $this->renderError('订单不存在或无权操作');
        }
        if ($model['status'] == 3) {
            return $this->renderError('订单已取消，无法确认收货');
        }
        if (!$model->confirmByUser()) {
            return $this->renderError($model->getError() ?: '确认收货失败');
        }
        // 商户侧 webhook 回写：入队异步处理（由 task 模块定时消费），保证两端最终一致
        DeliveryWebhookQueue::push($model);
        return $this->renderSuccess([
            'order_id'      => $model['order_id'],
            'status'        => $model['status'],
            'status_text'   => $model->getAttr('status_text'),
            'notify_status' => $model['notify_status'],
        ], '已确认收货');
    }
}
