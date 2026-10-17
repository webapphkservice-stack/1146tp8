<?php
namespace app\store\controller;

use app\store\model\Order as OrderModel;

/**
 * 商家后台订单管理 JSON API（原 Order 控制器，因页面版 Order 占用类名而改名）
 *
 * 路由（均在 Auth 中间件保护下，校验 Store-Token）：
 *   GET  /store/order/lists          订单列表（dataType 筛选 + 分页）
 *   GET  /store/order/detail         订单详情
 *   POST /store/order/delivery       订单发货（express_id + express_no）
 *   POST /store/order/confirmCancel  审核用户取消申请
 */
class OrderApi extends Api
{
    /** @var OrderModel */
    private $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = (new OrderModel())->setWxappId($this->getWxappId());
    }

    /**
     * 订单列表
     * @return \think\response\Json
     */
    public function lists()
    {
        $dataType = $this->request->param('dataType', 'all');
        $query    = $this->request->param();
        $list     = $this->model->getList($dataType, $query);
        return $this->renderSuccess([
            'list'  => $list->items(),
            'total' => $list->total(),
            'page'  => $list->currentPage(),
        ]);
    }

    /**
     * 订单详情
     * @return \think\response\Json
     */
    public function detail()
    {
        $orderId = (int)$this->request->param('order_id', 0);
        if ($orderId <= 0) {
            return $this->renderError('缺少订单id');
        }
        $detail = $this->model->detail($orderId);
        if (empty($detail)) {
            return $this->renderError('订单不存在');
        }
        return $this->renderSuccess(['detail' => $detail]);
    }

    /**
     * 订单发货
     * @return \think\response\Json
     */
    public function delivery()
    {
        $orderId = (int)$this->request->param('order_id', 0);
        if ($orderId <= 0) {
            return $this->renderError('缺少订单id');
        }
        $data = $this->postData();
        if (empty($data)) {
            $data = $this->request->param();
        }
        if (!$this->model->delivery($orderId, $data)) {
            return $this->renderError($this->model->getError() ?: '发货失败');
        }
        return $this->renderSuccess([], '发货成功');
    }

    /**
     * 审核：用户取消订单
     * @return \think\response\Json
     */
    public function confirmCancel()
    {
        $orderId = (int)$this->request->param('order_id', 0);
        if ($orderId <= 0) {
            return $this->renderError('缺少订单id');
        }
        if (!$this->model->confirmCancel($orderId)) {
            return $this->renderError($this->model->getError() ?: '操作失败');
        }
        return $this->renderSuccess([], '操作成功');
    }
}
