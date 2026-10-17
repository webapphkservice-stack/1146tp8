<?php

namespace app\store\controller;

use app\store\model\Order as OrderModel;
use app\store\model\store\Shop as ShopModel;
use app\common\enum\order\ShopOrderStatus as ShopOrderStatusEnum;
use app\api\service\OrderStateMachine as StateMachine;
use app\common\enum\DeliveryType as DeliveryTypeEnum;
use think\facade\Db;

/**
 * 送水订单(门店配送)管理
 *
 * 重写适配 TP8 数组风格 Order 模型：detail 返回数组、字段裸值，不依赖模型对象与枚举获取器。
 * 订单状态/支付状态使用 StateMachine 常量（TP8 未迁移 OrderStatus/PayStatus 枚举）。
 */
class WaterOrder extends Controller
{
    /**
     * 送水订单列表
     */
    public function index()
    {
        $model = new OrderModel;
        $list = new \think\paginator\driver\Bootstrap([], 15, 1, 0);
        $shopList = [];
        $waitAcceptCount = 0;
        try {
            $list = $model->getWaterList($this->request->get());
            $shopList = ShopModel::getAllList();
            $waitAcceptCount = OrderModel::getWaterWaitAcceptCount();
        } catch (\Throwable $e) {
            // 本地 DB 可能未同步送水相关表/列，降级返回空列表
            \think\facade\Log::warning('WaterOrder::index 降级：' . $e->getMessage());
        }
        return $this->fetch('index', compact('list', 'shopList', 'waitAcceptCount'));
    }

    /**
     * 门店接单 → 配送中（可同时指派配送员）
     */
    public function accept($order_id = 0)
    {
        $order_id = $order_id ?: (int)$this->request->post('order_id');
        $order = OrderModel::detailWater($order_id);
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        // 已取消/用户申请取消中的订单不允许接单
        if (in_array((int)$order['order_status'], [StateMachine::ORDER_CANCELLED, StateMachine::ORDER_APPLY_CANCEL])) {
            return $this->renderError('该订单已取消或用户正在申请取消，不可接单');
        }
        // 未付款订单不允许接单，避免未收款即安排配送
        if ((int)$order['pay_status'] !== StateMachine::PAY_SUCCESS) {
            return $this->renderError('该订单未付款，不可接单');
        }
        if ((int)$order['shop_order_status'] !== ShopOrderStatusEnum::WAIT_ACCEPT) {
            return $this->renderError('该订单当前状态不可接单');
        }
        $clerkId = $this->request->post('clerk_id/d', 0);
        $model = new OrderModel;
        if (!$model->updateShopOrderStatus($order_id, ShopOrderStatusEnum::DELIVERING, $clerkId)) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('已接单，配送中');
    }

    /**
     * 门店配送员列表（接单时指派用）
     */
    public function clerks($shop_id = 0)
    {
        $shop_id = $shop_id ?: (int)$this->request->post('shop_id');
        $list = Db::name('store_shop_clerk')
            ->where('shop_id', $shop_id)
            ->where('is_delete', 0)
            ->field('clerk_id, real_name')
            ->select()->toArray();
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 退还押桶押金（用户归还空桶后由后台操作）
     */
    public function refundDeposit($order_id = 0)
    {
        $order_id = $order_id ?: (int)$this->request->post('order_id');
        $order = OrderModel::detailWater($order_id);
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        // 仅门店配送订单存在押桶押金
        if ((int)$order['delivery_type'] !== DeliveryTypeEnum::SHOP_DELIVERY) {
            return $this->renderError('该订单不是门店配送订单');
        }
        if ((float)$order['deposit_amount'] <= 0) {
            return $this->renderError('该订单无押桶押金，无需退还');
        }
        // 已取消/取消中的订单：押金随全款原路退回，不能再单独退一次
        if (in_array((int)$order['order_status'], [StateMachine::ORDER_CANCELLED, StateMachine::ORDER_APPLY_CANCEL])) {
            return $this->renderError('该订单已取消或正在取消，押金随全款退回，无需单独退还');
        }
        // 幂等：已退还直接返回成功
        if ((int)$order['deposit_refund_status'] === 1) {
            return $this->renderError('押金已退还，请勿重复操作');
        }
        // 重复退款防护：售后已退金额 + 本次押金 不得超过订单实付款
        $refundedMoney = (float)Db::name('order_refund')
            ->where('order_id', '=', (int)$order['order_id'])
            ->where('status', '=', 20)
            ->sum('refund_money');
        if ($refundedMoney + (float)$order['deposit_amount'] > (float)$order['pay_price'] + 0.0001) {
            return $this->renderError('该订单已退款 ￥' . number_format($refundedMoney, 2)
                . '，押金可能已包含在退款金额中，请核对后线下处理');
        }
        // 校验空桶归还：已归还数量需达到购买件数(桶数)才允许退押金
        $totalNum = $this->getOrderBucketNum($order);
        $returned = (int)$order['empty_bucket_num'];
        if ($returned < $totalNum) {
            return $this->renderError("空桶未全部归还：应还 {$totalNum} 个，已还 {$returned} 个");
        }
        // 执行退款（退款服务当前支持微信支付 / 余额支付）
        $model = new OrderModel;
        if (!$model->refundDeposit($order_id, $order)) {
            return $this->renderError($model->getError() ?: '押金退还失败');
        }
        return $this->renderSuccess('押金已退还');
    }

    /**
     * 完成配送（门店状态完成 + 主订单完成）
     */
    public function finish($order_id = 0)
    {
        $order_id = $order_id ?: (int)$this->request->post('order_id');
        $order = OrderModel::detailWater($order_id);
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        if ((int)$order['delivery_type'] !== DeliveryTypeEnum::SHOP_DELIVERY) {
            return $this->renderError('该订单不是门店配送订单');
        }
        // 已取消/用户申请取消中的订单不允许完成
        if (in_array((int)$order['order_status'], [StateMachine::ORDER_CANCELLED, StateMachine::ORDER_APPLY_CANCEL])) {
            return $this->renderError('该订单已取消或用户正在申请取消，不可完成');
        }
        // 幂等：已完成直接返回成功
        if ((int)$order['shop_order_status'] === ShopOrderStatusEnum::COMPLETED) {
            return $this->renderSuccess('订单已完成');
        }
        $model = new OrderModel;
        if (!$model->finishWaterOrder($order_id)) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('配送已完成');
    }

    /**
     * 取消配送（联动主订单：已付款订单自动退款+回库存+回退优惠券/积分并取消主订单）
     */
    public function cancel($order_id = 0)
    {
        $order_id = $order_id ?: (int)$this->request->post('order_id');
        $model = new OrderModel;
        if (!$model->cancelWaterOrder($order_id)) {
            return $this->renderError($model->getError() ?: '操作失败');
        }
        return $this->renderSuccess('已取消，已付款金额已原路退回');
    }

    /**
     * 审核用户的取消申请（送水订单）
     * 用户在小程序取消"已付款"的门店配送订单后进入待取消(21)，
     * 而主订单列表已按配送方式排除送水单，若不在此提供审核入口，用户的退款将无人处理
     */
    public function auditCancel($order_id = 0)
    {
        $order_id = $order_id ?: (int)$this->request->post('order_id');
        $order = OrderModel::detailWater($order_id);
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        if ((int)$order['order_status'] !== StateMachine::ORDER_APPLY_CANCEL) {
            return $this->renderError('该订单没有待审核的取消申请');
        }
        $model = new OrderModel;
        // 拒绝：订单回到进行中，门店配送状态保持不变
        if ((int)$this->request->post('is_cancel/d', 0) !== 1) {
            if (!$model->updateWaterField($order_id, ['order_status' => StateMachine::ORDER_NORMAL])) {
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('已拒绝取消申请，订单继续配送');
        }
        // 同意：走标准送水取消联动(退款+回库存+回券/积分+回退水票+门店状态已取消)
        if (!$model->cancelWaterOrder($order_id)) {
            return $this->renderError($model->getError() ?: '操作失败');
        }
        return $this->renderSuccess('已同意取消，已付款金额已原路退回');
    }

    /**
     * 登记空桶归还数量（送水）
     */
    public function recycleBucket($order_id = 0)
    {
        $order_id = $order_id ?: (int)$this->request->post('order_id');
        $order = OrderModel::detailWater($order_id);
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        if ((int)$order['delivery_type'] !== DeliveryTypeEnum::SHOP_DELIVERY) {
            return $this->renderError('该订单不是门店配送订单，无空桶可登记');
        }
        if ((int)$order['order_status'] === StateMachine::ORDER_CANCELLED) {
            return $this->renderError('该订单已取消，无需登记空桶归还');
        }
        $num = $this->request->post('num/d', 0);
        if ($num < 0) {
            return $this->renderError('归还数量不能为负数');
        }
        // 归还数量不能超过购买件数(桶数)
        $totalNum = $this->getOrderBucketNum($order);
        if ($num > $totalNum) {
            return $this->renderError("归还数量不能超过购买件数 {$totalNum} 个");
        }
        $model = new OrderModel;
        if (!$model->updateWaterField($order_id, ['empty_bucket_num' => (int)$num])) {
            return $this->renderError('登记失败');
        }
        return $this->renderSuccess('已登记空桶归还数量');
    }

    /**
     * 计算订单桶数（按订单商品总件数）
     * 应还桶数 = 仅统计下单时"按桶计押金"的商品件数（与收押金口径一致）
     * @param array $order
     * @return int
     */
    private function getOrderBucketNum($order)
    {
        $totalNum = 0;
        foreach ($order['goods'] as $goods) {
            $payable = isset($goods['is_deposit_payable']) ? (int)$goods['is_deposit_payable'] : 1;
            $payable == 1 && $totalNum += (int)$goods['total_num'];
        }
        return $totalNum;
    }
}
