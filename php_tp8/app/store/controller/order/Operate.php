<?php
namespace app\store\controller\order;

use app\store\controller\Controller;
use app\store\model\Order as OrderModel;
use app\store\model\Express as ExpressModel;

/**
 * 订单操作控制器（服务端渲染版，移植自 TP5 store/controller/order/Operate.php）
 *
 * 已迁移：export（CSV 导出）、deliveryTpl（批量发货模板）、confirmCancel（审核取消）、extract（门店自提核销）、batchDelivery（页面入口）
 * P2 未迁移：batchDelivery 的 CSV 解析执行（读取上传的 CSV 并批量发货）
 */
class Operate extends Controller
{
    /** @var OrderModel */
    private $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = (new OrderModel)->setWxappId(self::getWxappId());
    }

    /**
     * 订单导出（CSV）
     * @param string $dataType all|payment|delivery|received|finish|cancel
     */
    public function export($dataType = 'all')
    {
        $result = $this->model->exportList($dataType, $this->request->get());
        return response($result['csv'], 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment;filename=' . $result['filename'],
        ]);
    }

    /**
     * 批量发货
     */
    public function batchDelivery()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('batchDelivery', [
                'express_list' => ExpressModel::getAll(),
            ]);
        }
        return $this->renderError('批量发货的 CSV 解析执行尚未迁移，属 P2 增强项');
    }

    /**
     * 批量发货模板（CSV，仅含「订单号 / 物流单号」两列表头）
     */
    public function deliveryTpl()
    {
        $result = $this->model->deliveryTpl();
        return response($result['csv'], 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment;filename=' . $result['filename'],
        ]);
    }

    /**
     * 审核：用户取消订单
     * @param int $order_id
     */
    public function confirmCancel($order_id)
    {
        if (!$this->model->confirmCancel($order_id)) {
            return $this->renderError($this->model->getError() ?: '操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 门店自提核销
     * @param int $order_id
     */
    public function extract($order_id)
    {
        $data = $this->postData('order');
        $clerkId = (int)($data['extract_clerk_id'] ?? 0);
        if ($clerkId <= 0) {
            return $this->renderError('请选择核销员');
        }
        if (!$this->model->verificationOrder($order_id, $clerkId)) {
            return $this->renderError($this->model->getError() ?: '核销失败');
        }
        return $this->renderSuccess('核销成功');
    }
}
