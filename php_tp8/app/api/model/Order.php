<?php
namespace app\api\model;

use app\common\model\BaseModel;
use app\api\service\Payment;
use app\api\service\OrderStateMachine;
use app\common\enum\order\PayType;
use think\facade\Db;

/**
 * 订单模型（轻量版，对齐 RN 用户订单契约 user.order/*）
 * 设计要点（与第 1~4 批一致「轻量 + Db 直查」，不引入 TP5 getter 魔法）：
 *  - 所有对外结构均为普通数组（status 以 {value,text} 形式返回，等价于 php_my 的 getXxxAttr）
 *  - state_text 逻辑移植自 php_my common/model/Order::getStateTextAttr
 *  - 列表/详情/取消/收货/物流/支付，全部基于 order / order_goods / order_address 三表
 *  - 支付（pay）沿用轻量 strategy：余额同步扣款改单；第三方仅返回契约参数（SDK 未引入）
 */
class Order
{
    /** @var string 业务错误信息 */
    public $error = '';

    // 支付状态
    const PAY_PENDING = 10;
    const PAY_SUCCESS = 20;
    // 发货状态
    const DELIVERY_WAIT = 10;
    const DELIVERY_DONE = 20;
    // 收货状态
    const RECEIPT_WAIT = 10;
    const RECEIPT_DONE = 20;
    // 订单状态
    const ORDER_NORMAL     = 10;
    const ORDER_CANCELLED  = 20;
    const ORDER_APPLY_CANCEL = 21;
    const ORDER_COMPLETED  = 30;

    public function getError()
    {
        return $this->error;
    }

    /**
     * 用户订单列表
     * @param int $userId
     * @param string $dataType all/payment/delivery/received/comment
     * @return array ['list' => [...]]
     */
    public function getList($userId, $dataType = 'all')
    {
        $filter = [];
        switch ($dataType) {
            case 'payment':
                $filter['pay_status'] = self::PAY_PENDING;
                $filter['order_status'] = self::ORDER_NORMAL;
                break;
            case 'delivery':
                $filter['pay_status'] = self::PAY_SUCCESS;
                $filter['delivery_status'] = self::DELIVERY_WAIT;
                $filter['order_status'] = self::ORDER_NORMAL;
                break;
            case 'received':
                $filter['pay_status'] = self::PAY_SUCCESS;
                $filter['delivery_status'] = self::DELIVERY_DONE;
                $filter['receipt_status'] = self::RECEIPT_WAIT;
                $filter['order_status'] = self::ORDER_NORMAL;
                break;
            case 'comment':
                $filter['is_comment'] = 0;
                $filter['order_status'] = self::ORDER_COMPLETED;
                break;
        }
        $orders = Db::name('order')
            ->where('user_id', $userId)
            ->where($filter)
            ->where('is_delete', 0)
            ->order('create_time', 'desc')
            ->select()->toArray();
        $list = [];
        foreach ($orders as $o) {
            $list[] = $this->formatListOrder($o);
        }
        return ['list' => $list];
    }

    /**
     * 订单详情（含商品、地址、物流）
     * @param int $orderId
     * @param int $userId
     * @return array|false
     */
    public function getUserOrderDetail($orderId, $userId)
    {
        $order = Db::name('order')
            ->where('order_id', $orderId)
            ->where('user_id', $userId)
            ->where('is_delete', 0)
            ->find();
        if (empty($order)) {
            $this->error = '订单不存在';
            return false;
        }
        return $this->formatDetailOrder($order);
    }

    /**
     * 根据订单号（out_trade_no）获取订单主键，用于微信订单中心跳转
     * @return int
     */
    public function getOrderIdByNo($orderNo, $userId)
    {
        $order = Db::name('order')
            ->where('order_no', $orderNo)
            ->where('user_id', $userId)
            ->where('is_delete', 0)
            ->find();
        if (empty($order)) {
            $this->error = '订单不存在';
            return 0;
        }
        return (int)$order['order_id'];
    }

    /**
     * 取消订单（委托订单状态机）
     * @return bool
     */
    public function cancel($orderId, $userId, $user)
    {
        $sm = new OrderStateMachine;
        if (!$sm->cancel($orderId, $userId)) {
            $this->error = $sm->getError();
            return false;
        }
        return true;
    }

    /**
     * 确认收货（委托订单状态机）
     * @return bool
     */
    public function receipt($orderId, $userId)
    {
        $sm = new OrderStateMachine;
        if (!$sm->receipt($orderId, $userId)) {
            $this->error = $sm->getError();
            return false;
        }
        return true;
    }

    // -------------------- 支付（第 6 批） --------------------

    /**
     * 支付前置校验：订单是否可支付
     * @return array|false
     */
    private function getPayableOrder($orderId, $userId)
    {
        $order = Db::name('order')
            ->where('order_id', $orderId)
            ->where('user_id', $userId)
            ->where('is_delete', 0)
            ->find();
        if (empty($order)) {
            $this->error = '订单不存在';
            return false;
        }
        if ($order['pay_status'] == self::PAY_SUCCESS) {
            $this->error = '该订单已支付';
            return false;
        }
        if ($order['order_status'] != self::ORDER_NORMAL) {
            $this->error = '当前订单状态不可支付';
            return false;
        }
        return $order;
    }

    /**
     * 订单支付入口（对齐 RN user.order/pay）
     * @param int    $orderId
     * @param int    $userId
     * @param array  $user
     * @param int    $payType PayType 枚举值，默认微信
     * @param string $platform 支付平台：android / ios / h5（H5 场景强制 MWEB 走 mweb_url 跳转）
     * @param string $redirectUrl MWEB 支付完成后的回跳 URL（前端支付结果页）
     * @return array|false 成功返回 [order_id, pay_type, payment]，失败返回 false
     */
    public function pay($orderId, $userId, $user, $payType = PayType::WECHAT, $platform = '', $redirectUrl = '')
    {
        $order = $this->getPayableOrder($orderId, $userId);
        if ($order === false) {
            return false;
        }
        // 1) 构建支付参数（无副作用；第三方 SDK 缺失时返回契约结构，配置缺失会抛异常）
        $payment = (new Payment)->getParams($payType, $order, $user, null, $platform, $redirectUrl);
        // 2) 余额支付：同步扣款 + 改单状态（无异步回调，直接完成）
        if ($payType == PayType::BALANCE) {
            if (!$this->onPayByBalance($user['user_id'], $order['pay_price'])) {
                return false;
            }
            if (!$this->onPaySuccess($order['order_no'], $payType, '')) {
                return false;
            }
        }
        return [
            'order_id' => $order['order_id'],
            'pay_type' => $payType,
            'payment'  => $payment,
        ];
    }

    /**
     * 余额支付扣款
     * @return bool
     */
    private function onPayByBalance($userId, $payPrice)
    {
        $userInfo = Db::name('user')->where('user_id', $userId)->where('is_delete', 0)->find();
        if (empty($userInfo)) {
            $this->error = '用户不存在';
            return false;
        }
        if ((float)$userInfo['balance'] < (float)$payPrice) {
            $this->error = '用户余额不足，无法使用余额支付';
            return false;
        }
        return Db::transaction(function () use ($userId, $payPrice) {
            Db::name('user')->where('user_id', $userId)
                ->dec('balance', $payPrice)->update();
            return true;
        });
    }

    /**
     * 支付成功：变更订单状态（委托订单状态机 OrderStateMachine::paySuccess）
     * 由支付网关回调（微信/支付宝/Stripe notify）或余额同步支付调用。
     * 保留为门面方法以便 pay() 余额路径与历史调用方复用。
     * @param string $orderNo
     * @param int    $payType
     * @param string $transactionId 第三方交易号，余额支付可为空
     * @return bool
     */
    public function onPaySuccess($orderNo, $payType, $transactionId = '')
    {
        $sm = new OrderStateMachine;
        if (!$sm->paySuccess($orderNo, $payType, $transactionId)) {
            $this->error = $sm->getError();
            return false;
        }
        return true;
    }

    // -------------------- 格式化 --------------------

    private function formatListOrder($order)
    {
        return [
            'order_id'        => (int)$order['order_id'],
            'order_no'        => $order['order_no'],
            'state_text'      => $this->getStateText($order),
            'goods'           => $this->getOrderGoods($order['order_id']),
            'pay_price'       => $order['pay_price'],
            'pay_status'      => $this->payStatus($order['pay_status']),
            'delivery_status' => $this->deliveryStatus($order['delivery_status']),
            'receipt_status'  => $this->receiptStatus($order['receipt_status']),
            'order_status'    => $this->orderStatus($order['order_status']),
            'is_comment'      => (int)$order['is_comment'],
            // 门店配送（送水）状态
            'shop_order_status' => $this->shopOrderStatus($order['shop_order_status'] ?? 0),
        ];
    }

    private function formatDetailOrder($order)
    {
        $detail = $this->formatListOrder($order);
        $detail['create_time']    = date('Y-m-d H:i:s', $order['create_time']);
        $detail['total_price']    = $order['total_price'];
        $detail['express_price']  = $order['express_price'];
        $detail['coupon_money']   = $order['coupon_money'];
        $detail['points_money']   = $order['points_money'];
        $detail['goods_type']     = (int)$order['goods_type'];
        $detail['delivery_type']  = $this->deliveryType($order['delivery_type']);
        $detail['delivery']       = $this->deliveryType($order['delivery_type']);
        $detail['pay_type']       = $this->payType($order['pay_type']);
        $detail['address']        = $this->getOrderAddress($order['order_id']);
        $detail['express']        = $this->getOrderExpress($order);
        // 自提订单：返回自提点信息与提货凭证（核销码 = 订单号）
        $detail['extract']        = $this->getOrderExtract($order);
        // 暴露原始物流字段（供 user.order/express 动作读取；RN 详情只读 $detail.express，无副作用）
        $detail['express_no']      = $order['express_no'];
        $detail['express_company'] = $order['express_company'];
        $detail['express_code']    = $order['express_code'] ?? '';
        // 门店配送（送水）对账/展示字段
        $detail['delivery_shop_id']     = (int)$order['delivery_shop_id'];
        $detail['delivery_shop_name']   = $order['delivery_shop_name'] ?? '';
        $detail['water_ticket_num']     = (int)$order['water_ticket_num'];
        $detail['water_ticket_money']   = (float)$order['water_ticket_money'];
        $detail['deposit_amount']       = (float)$order['deposit_amount'];
        $detail['empty_bucket_num']     = (int)$order['empty_bucket_num'];
        $detail['deposit_refund_status']= (int)$order['deposit_refund_status'];
        $detail['shop_order_status']    = $this->shopOrderStatus($order['shop_order_status'] ?? 0);
        return $detail;
    }

    /**
     * 状态文字（移植自 php_my common/model/Order::getStateTextAttr）
     */
    private function getStateText($data)
    {
        if (in_array((int)$data['order_status'], [self::ORDER_CANCELLED, self::ORDER_COMPLETED])) {
            $map = [self::ORDER_CANCELLED => '已取消', self::ORDER_COMPLETED => '已完成'];
            return $map[$data['order_status']];
        }
        if ($data['pay_status'] == self::PAY_PENDING) {
            return '待付款';
        }
        if ($data['delivery_status'] == self::DELIVERY_WAIT) {
            return '已付款，待发货';
        }
        if ($data['receipt_status'] == self::RECEIPT_WAIT) {
            return '已发货，待收货';
        }
        return '';
    }

    private function payStatus($v)
    {
        $map = [self::PAY_PENDING => '待付款', self::PAY_SUCCESS => '已付款'];
        return ['value' => (int)$v, 'text' => $map[$v] ?? ''];
    }

    private function deliveryStatus($v)
    {
        $map = [self::DELIVERY_WAIT => '待发货', self::DELIVERY_DONE => '已发货'];
        return ['value' => (int)$v, 'text' => $map[$v] ?? ''];
    }

    private function receiptStatus($v)
    {
        $map = [self::RECEIPT_WAIT => '待收货', self::RECEIPT_DONE => '已收货'];
        return ['value' => (int)$v, 'text' => $map[$v] ?? ''];
    }

    private function orderStatus($v)
    {
        $map = [
            self::ORDER_NORMAL       => '进行中',
            self::ORDER_CANCELLED    => '已取消',
            self::ORDER_APPLY_CANCEL => '待取消',
            self::ORDER_COMPLETED    => '已完成',
        ];
        return ['value' => (int)$v, 'text' => $map[$v] ?? ''];
    }

    private function shopOrderStatus($v)
    {
        $map = [
            10 => '待接单',
            20 => '配送中',
            30 => '已完成',
            40 => '已取消',
        ];
        return ['value' => (int)$v, 'text' => $map[$v] ?? ''];
    }

    private function deliveryType($v)
    {
        $map = [10 => '快递配送', 20 => '上门自提', 30 => '门店配送'];
        return ['value' => (int)$v, 'text' => $map[$v] ?? ''];
    }

    private function payType($v)
    {
        $map = [10 => '余额支付', 20 => '微信支付', 30 => '好友代付', 40 => '支付宝', 50 => '银行卡(Stripe)'];
        return ['value' => (int)$v, 'text' => $map[$v] ?? ''];
    }

    /**
     * 订单商品列表（image 由 image_id 转换 URL）
     */
    private function getOrderGoods($orderId)
    {
        $rows = Db::name('order_goods')
            ->where('order_id', $orderId)
            ->order('order_goods_id', 'asc')
            ->select()->toArray();
        $list = [];
        foreach ($rows as $g) {
            $list[] = [
                'goods_id'     => (int)$g['goods_id'],
                'goods_name'   => $g['goods_name'],
                'goods_sku_id' => (int)$g['goods_sku_id'],
                'goods_attr'   => $g['goods_attr'],
                'goods_price'  => $g['goods_price'],
                'total_num'    => (int)$g['total_num'],
                'image'        => BaseModel::getFileUrl($g['image_id']),
            ];
        }
        return $list;
    }

    /**
     * 自提订单信息（门店 + 提货凭证 + 联系人）
     * 门店端核销动作在 store 应用，不在此 api 应用范围内；此处仅向用户端返回提货所需数据。
     */
    private function getOrderExtract($order)
    {
        if ((int)$order['delivery_type'] != 20) {
            return null;
        }
        $extract = Db::name('order_extract')->where('order_id', $order['order_id'])->find();
        $shop = $order['extract_shop_id'] > 0
            ? Db::name('store_shop')->where('shop_id', $order['extract_shop_id'])->find()
            : [];
        return [
            'code'    => $order['order_no'],                 // 提货凭证（核销码）
            'linkman' => $extract['linkman'] ?? '',
            'phone'   => $extract['phone'] ?? '',
            'shop'    => $shop ? [
                'shop_name' => $shop['shop_name'],
                'linkman'   => $shop['linkman'],
                'phone'     => $shop['phone'],
                'address'   => $shop['address'],
            ] : null,
        ];
    }

    /**
     * 订单收货地址快照
     */
    private function getOrderAddress($orderId)
    {
        $addr = Db::name('order_address')
            ->where('order_id', $orderId)
            ->find();
        if (empty($addr)) {
            return [];
        }
        return [
            'name'    => $addr['name'],
            'phone'   => $addr['phone'],
            'region'  => [
                'province' => $addr['state'],
                'city'     => $addr['city'],
                'region'   => $addr['district'],
            ],
            'detail'  => $addr['detail'],
        ];
    }

    /**
     * 订单物流信息（运单号存在时返回，否则空数组由 RN 隐藏物流块）
     */
    private function getOrderExpress($order)
    {
        if (empty($order['express_no'])) {
            return [];
        }
        return [
            'express_company' => $order['express_company'],
            'express_no'      => $order['express_no'],
            'express_code'    => $order['express_code'] ?? '',
        ];
    }
}
