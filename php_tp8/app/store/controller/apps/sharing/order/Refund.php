<?php

namespace app\store\controller\apps\sharing\order;

use app\store\controller\Controller;
use app\store\model\sharing\Order as OrderModel;
use app\store\model\sharing\OrderRefund as OrderRefundModel;
use app\store\model\ReturnAddress as ReturnAddressModel;

/**
 * 售后管理
 * Class Refund
 * @package app\store\controller\apps\sharing\order
 */
class Refund extends Controller
{
    /**
     * 售后列表
     * @return mixed
     */
    public function index()
    {
        $model = new OrderRefundModel;
        $list = $model->getList($this->getData());
        return $this->fetch('index', compact('list'));
    }

    /**
     * 售后单详情
     * @param $order_refund_id
     * @return mixed
     */
    public function detail($order_refund_id)
    {
        $detail = OrderRefundModel::detail($order_refund_id);
        $order = OrderModel::detail($detail['order_id']);
        $address = (new ReturnAddressModel)->getAll();
        return $this->fetch('detail', compact('detail', 'order', 'address'));
    }

    /**
     * 商家审核
     * @param $order_refund_id
     * @return array|bool
     */
    public function audit($order_refund_id)
    {
        if (!$this->request->isAjax()) {
            return false;
        }
        $model = OrderRefundModel::detail($order_refund_id);
        if ($model->audit($this->postData('refund'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 确认收货并退款
     * @param $order_refund_id
     * @return array|bool
     */
    public function receipt($order_refund_id)
    {
        if (!$this->request->isAjax()) {
            return false;
        }
        $model = OrderRefundModel::detail($order_refund_id);
        if ($model->receipt($this->postData('refund'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

}
