<?php
namespace app\store\controller\order;

use app\store\controller\Controller;
use app\store\model\Order as OrderModel;
use app\store\model\OrderRefund as OrderRefundModel;
use app\store\model\ReturnAddress as ReturnAddressModel;

/**
 * 售后管理（服务端渲染版，移植自 TP5 store/controller/order/Refund.php）
 * 菜单：订单管理 -> 售后管理（order.refund/index）
 */
class Refund extends Controller
{
    /** @var OrderRefundModel */
    private $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = new OrderRefundModel;
    }

    /**
     * 售后单列表
     */
    public function index()
    {
        $list = $this->model->getList($this->getData());
        return $this->fetch('index', compact('list'));
    }

    /**
     * 售后单详情
     * @param int $order_refund_id
     */
    public function detail($order_refund_id)
    {
        $detail = OrderRefundModel::detail($order_refund_id);
        if (empty($detail)) {
            // 避免视图直接访问空数组触发 Undefined array key（原本会 500），
            // 记录不存在时直接返回 404 错误页。
            return abort(404, '售后单不存在或已删除');
        }
        // 订单详情
        $order = (new OrderModel)->detailForAdmin($detail['order_id']);
        // 退货地址
        $address = (new ReturnAddressModel)->getAll();
        return $this->fetch('detail', compact('detail', 'order', 'address'));
    }

    /**
     * 商家审核
     * @param int $order_refund_id
     */
    public function audit($order_refund_id)
    {
        if (!$this->request->isAjax()) {
            return $this->renderError('非法请求');
        }
        if (!$this->model->audit($order_refund_id, $this->postData('refund'))) {
            return $this->renderError($this->model->getError() ?: '操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 确认收货并退款
     * @param int $order_refund_id
     */
    public function receipt($order_refund_id)
    {
        if (!$this->request->isAjax()) {
            return $this->renderError('非法请求');
        }
        if (!$this->model->receipt($order_refund_id, $this->postData('refund'))) {
            return $this->renderError($this->model->getError() ?: '操作失败');
        }
        return $this->renderSuccess('操作成功');
    }
}
