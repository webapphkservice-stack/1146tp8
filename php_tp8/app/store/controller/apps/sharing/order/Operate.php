<?php

namespace app\store\controller\apps\sharing\order;

use app\store\controller\Controller;
use app\store\model\sharing\Order as OrderModel;
use app\store\model\Express as ExpressModel;

/**
 * 拼团订单操作控制器
 * Class Operate
 * @package app\store\controller\apps\sharing\order
 */
class Operate extends Controller
{
    /* @var OrderModel $model */
    private $model;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        $this->model = new OrderModel;
    }

    /**
     * 拼团订单列表
     * @param string $dataType
     * @return mixed
     */
    public function index($dataType = 'all')
    {
        $list = $this->model->getList($dataType, $this->request->param());
        return $this->fetch('index', compact('list'));
    }

    /**
     * 订单导出
     * @param string $dataType
     */
    public function export($dataType)
    {
        return $this->model->exportList($dataType, $this->request->param());
    }

    /**
     * 批量发货
     * @return array|bool|mixed
     */
    public function batchDelivery()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('batchDelivery', [
                'express_list' => ExpressModel::getAll()
            ]);
        }
        if ($this->model->batchDelivery($this->postData('order'))) {
            return $this->renderSuccess('发货成功');
        }
        return $this->renderError($this->model->getError() ?: '发货失败');
    }

    /**
     * 审核：用户取消订单
     * @param $order_id
     * @return array|bool
     */
    public function confirmCancel($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->confirmCancel($this->postData('order'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 拼团失败手动退款
     * @param $order_id
     * @return array|bool
     */
    public function refund($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->refund()) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 门店自提核销
     * @param $order_id
     * @return array|bool
     */
    public function extract($order_id)
    {
        $model = OrderModel::detail($order_id);
        $data = $this->postData('order');
        if ($model->verificationOrder($data['extract_clerk_id'])) {
            return $this->renderSuccess('核销成功');
        }
        return $this->renderError($model->getError() ?: '核销失败');
    }

}
