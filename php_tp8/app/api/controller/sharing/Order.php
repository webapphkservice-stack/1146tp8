<?php

namespace app\api\controller\sharing;

use app\api\controller\Controller;
use app\api\service\sharing\Order as SharingOrderService;
use app\api\service\Payment;
use think\facade\Db;

/**
 * 拼团订单控制器（完整移植 php api/controller/sharing/Order）
 *
 * 小程序调用点：sharing.order/{checkout,lists,detail,express,pay,receipt,cancel,extractQrcode}
 * checkout：GET=结算页 / POST=提交建单并返回支付参数
 */
class Order extends Controller
{
    /**
     * 订单结算台（GET 结算页 / POST 提交）
     */
    public function checkout()
    {
        $user = $this->getUser(true);
        $wxappId = $this->getWxappId();
        $params = $this->request->param();
        $service = new SharingOrderService;
        $goodsId = (int)($params['goods_id'] ?? 0);
        $goodsSkuId = (string)($params['goods_sku_id'] ?? '0');
        $goodsNum = max(1, (int)($params['goods_num'] ?? 1));
        $activeId = (int)($params['active_id'] ?? 0);
        $goods = SharingOrderService::getCheckoutGoodsList($goodsId, $goodsSkuId, $goodsNum, $activeId, (int)$user['user_id'], $wxappId);
        if ($goods === false) {
            return $this->renderError(SharingOrderService::$lastError ?: '结算失败');
        }
        $address = null;
        $delivery = (int)($params['delivery'] ?? 10);
        if ($delivery != 20) {
            $addrRow = $service->getAddressRow((int)$user['user_id'], (int)($params['address_id'] ?? 0));
            if (!empty($addrRow)) {
                $address = [
                    'address_id' => (int)$addrRow['address_id'],
                    'name' => $addrRow['name'],
                    'phone' => $addrRow['phone'],
                    'region' => ['province' => $addrRow['state'], 'city' => $addrRow['city'], 'region' => $addrRow['district']],
                    'detail' => $addrRow['detail'],
                ];
            }
        }
        $member = Db::name('user')->where('user_id', $user['user_id'])->find();
        $calc = new \app\api\service\order\Checkout($member, $wxappId, [
            'coupon_id' => (int)($params['coupon_id'] ?? 0),
            'is_use_points' => (int)($params['is_use_points'] ?? 0),
        ]);
        $goodsList = [$goods];
        $calc->setOrderGoodsGradeMoney($goodsList);
        $discount = $calc->calcDiscount($goodsList);
        $goods = $goodsList[0];
        $payPrice = bcadd((string)$goods['total_pay_price'], '0.00', 2);
        $orderInfo = [
            'goods_list' => [$goods],
            'order_total_num' => $goods['total_num'],
            'order_total_price' => $discount['order_total_price'],
            'order_price' => $goods['total_pay_price'],
            'order_pay_price' => $payPrice,
            'coupon_list' => $discount['coupon_list'],
            'coupon_id' => $discount['coupon_id'],
            'coupon_money' => $discount['coupon_money'],
            'is_allow_points' => $discount['is_allow_points'],
            'is_use_points' => $discount['is_use_points'],
            'points_money' => $discount['points_money'],
            'points_num' => $discount['points_num'],
            'points_bonus' => $discount['points_bonus'],
            'address' => $address,
            'exist_address' => !empty($address),
            'delivery' => $delivery,
            'express_price' => '0.00',
            'active_id' => $activeId,
            'people' => $goods['people'],
            'group_time' => $goods['group_time'],
            'pay_type' => (int)($params['pay_type'] ?? 50),
            'has_error' => false,
            'error_msg' => '',
        ];
        if ($this->request->isGet()) {
            return $this->renderSuccess($orderInfo);
        }
        // POST：建单
        $orderId = $service->createOrder($user, $wxappId, $params);
        if ($orderId === false) {
            return $this->renderError(SharingOrderService::$lastError ?: '订单创建失败');
        }
        $order = Db::name('sharing_order')->where('order_id', $orderId)->find();
        $payType = (int)$order['pay_type'];
        $payment = null;
        if ($payType == 10) {
            $member = Db::name('user')->where('user_id', $user['user_id'])->find();
            if ((float)$member['balance'] < (float)$order['pay_price']) {
                return $this->renderError('用户余额不足，无法使用余额支付');
            }
            Db::name('user')->where('user_id', $user['user_id'])->dec('balance', $order['pay_price'])->update();
            if (!$service->onPaySuccess($order['order_no'], $payType, 'balance_' . $order['order_no'])) {
                return $this->renderError(SharingOrderService::$lastError ?: '订单支付失败');
            }
        } else {
            $payment = (new Payment)->getParams($payType, [
                'order_no' => $order['order_no'],
                'pay_price' => $order['pay_price'],
            ], $user);
        }
        return $this->renderSuccess([
            'order_id' => $orderId,
            'pay_type' => $payType,
            'payment' => $payment,
        ]);
    }

    /**
     * 我的拼团订单列表
     */
    public function lists()
    {
        $user = $this->getUser(true);
        $dataType = (string)$this->request->param('dataType', 'all');
        $list = SharingOrderService::getList((int)$user['user_id'], $dataType, $this->getWxappId());
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 订单详情
     */
    public function detail()
    {
        $user = $this->getUser(true);
        $orderId = (int)$this->request->param('order_id', 0);
        $order = SharingOrderService::getUserOrderDetail($orderId, (int)$user['user_id'], $this->getWxappId());
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        return $this->renderSuccess([
            'order' => $order,
            'setting' => ['points_name' => '积分'],
        ]);
    }

    /**
     * 获取物流信息
     */
    public function express()
    {
        $user = $this->getUser(true);
        $orderId = (int)$this->request->param('order_id', 0);
        $order = SharingOrderService::getUserOrderDetail($orderId, (int)$user['user_id'], $this->getWxappId());
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        if (empty($order['express_no'])) {
            return $this->renderError('没有物流信息');
        }
        return $this->renderSuccess([
            'express' => [
                'express_company' => $order['express_company'],
                'express_no' => $order['express_no'],
            ],
        ]);
    }

    /**
     * 立即支付（余额同步闭环 / 第三方返回支付参数）
     */
    public function pay()
    {
        $user = $this->getUser(true);
        $orderId = (int)$this->request->param('order_id', 0);
        $payType = (int)$this->request->param('payType', 50);
        $order = Db::name('sharing_order')->where('order_id', $orderId)
            ->where('user_id', $user['user_id'])->where('is_delete', 0)->find();
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        if ((int)$order['pay_status'] == 20) {
            return $this->renderError('该订单已支付，请勿重复支付');
        }
        $service = new SharingOrderService;
        $payment = null;
        if ($payType == 10) {
            $member = Db::name('user')->where('user_id', $user['user_id'])->find();
            if ((float)$member['balance'] < (float)$order['pay_price']) {
                return $this->renderError('用户余额不足，无法使用余额支付');
            }
            Db::name('user')->where('user_id', $user['user_id'])->dec('balance', $order['pay_price'])->update();
            if (!$service->onPaySuccess($order['order_no'], $payType, 'balance_' . $order['order_no'])) {
                return $this->renderError(SharingOrderService::$lastError ?: '订单支付失败');
            }
        } else {
            $payment = (new Payment)->getParams($payType, [
                'order_no' => $order['order_no'],
                'pay_price' => $order['pay_price'],
            ], $user);
        }
        return $this->renderSuccess([
            'order_id' => (int)$order['order_id'],
            'pay_type' => $payType,
            'payment' => $payment,
        ]);
    }

    /**
     * 确认收货
     */
    public function receipt()
    {
        $user = $this->getUser(true);
        $orderId = (int)$this->request->param('order_id', 0);
        $order = Db::name('sharing_order')->where('order_id', $orderId)
            ->where('user_id', $user['user_id'])->where('is_delete', 0)->find();
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        $service = new SharingOrderService;
        if (!$service->receipt($order)) {
            return $this->renderError(SharingOrderService::$lastError);
        }
        return $this->renderSuccess([], '收货成功');
    }

    /**
     * 取消订单
     */
    public function cancel()
    {
        $user = $this->getUser(true);
        $orderId = (int)$this->request->param('order_id', 0);
        $order = Db::name('sharing_order')->where('order_id', $orderId)
            ->where('user_id', $user['user_id'])->where('is_delete', 0)->find();
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        $service = new SharingOrderService;
        if (!$service->cancel($order, $this->getWxappId())) {
            return $this->renderError(SharingOrderService::$lastError);
        }
        return $this->renderSuccess([], '订单取消成功');
    }

    /**
     * 获取订单核销二维码（完整方案：微信小程序码落盘 temp 目录）
     */
    public function extractQrcode()
    {
        $user = $this->getUser(true);
        $orderId = (int)$this->request->param('order_id', 0);
        $order = Db::name('sharing_order')->where('order_id', $orderId)
            ->where('user_id', $user['user_id'])->where('is_delete', 0)->find();
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        if ((int)$order['pay_status'] != 20 || (int)$order['delivery_type'] != 20) {
            return $this->renderError('该订单暂不可核销');
        }
        try {
            $qrcode = \app\common\service\qrcode\Extract::build(
                $this->getWxappId(), $user, $orderId, 30
            );
            return $this->renderSuccess(['qrcode' => $qrcode]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
}
