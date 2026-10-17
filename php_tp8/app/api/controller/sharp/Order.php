<?php

namespace app\api\controller\sharp;

use app\api\controller\Controller;
use app\api\service\sharp\Order as SharpOrderService;
use app\api\service\Payment;
use think\facade\Db;

/**
 * 整点秒杀-下单端点
 *
 * 路由：sharp/order（GET 结算页 / POST 提交建单）
 * 对齐 RN sharpApi.createOrder() 与结算页契约。
 *
 * 校验：强制登录（下单需用户），wxapp_id 由 Token 中间件注入。
 */
class Order extends Controller
{
    /**
     * 秒杀订单结算
     *  - GET：返回结算页数据（商品 + 地址 + 价格）
     *  - POST：校验 → 建单 → 返回 order_id / pay_type / payment
     */
    public function checkout()
    {
        // 与全站一致：登录态在方法内取局部变量。
        // 不可声明 private $user —— 基类 Controller 已有 protected static $user，
        // 子类用非静态同名属性覆盖会触发 PHP Fatal error，导致整个控制器无法加载。
        $user = $this->getUser(true);
        $params = $this->request->param();
        $activeTimeId = (int)($params['active_time_id'] ?? 0);
        $sharpGoodsId = (int)($params['sharp_goods_id'] ?? 0);
        $goodsSkuId   = (string)($params['goods_sku_id'] ?? '0');
        $goodsNum     = max(1, (int)($params['goods_num'] ?? 1));
        $wxappId      = $this->getWxappId();
        $userId       = (int)$user['user_id'];

        $service = new SharpOrderService;

        // GET：结算页
        if ($this->request->isGet()) {
            $goods = $service::getCheckoutGoodsList(
                $activeTimeId, $sharpGoodsId, $goodsSkuId, $goodsNum, $userId, $wxappId, false
            );
            if ($goods === false) {
                return $this->renderError($service->getError() ?: '结算失败');
            }
            $address = $this->formatAddress(
                $service::getAddressRow($userId, (int)($params['address_id'] ?? 0))
            );
            return $this->renderSuccess([
                'goods_list'        => [$goods],
                'order_total_num'   => $goods['total_num'],
                'order_total_price' => $goods['total_price'],
                'order_price'       => $goods['total_price'],
                'order_pay_price'   => $goods['total_price'],
                'address'           => $address,
                'exist_address'     => !empty($address),
                'delivery'          => 10,
                'express_price'     => '0.00',
                'pay_type'          => 50,
                'has_error'         => false,
                'error_msg'         => '',
            ]);
        }

        // POST：提交建单
        $orderId = $service->createOrder($user, $wxappId, $params);
        if ($orderId === false) {
            return $this->renderError($service->getError() ?: '下单失败');
        }
        $order = Db::name('order')->where('order_id', $orderId)->find();
        if (empty($order)) {
            return $this->renderError('订单创建失败');
        }
        $payType = (int)($params['pay_type'] ?? 50);
        $payment = (new Payment)->getParams($payType, $order, $user);
        return $this->renderSuccess([
            'order_id'  => $orderId,
            'pay_type'  => $payType,
            'payment'   => $payment,
        ]);
    }

    /**
     * 地址行 → RN 契约结构
     */
    private function formatAddress($row)
    {
        if (empty($row)) {
            return null;
        }
        return [
            'address_id' => $row['address_id'],
            'name'       => $row['name'],
            'phone'      => $row['phone'],
            'region'     => [
                'province' => $row['state'],
                'city'     => $row['city'],
                'region'   => $row['district'],
            ],
            'detail'     => $row['detail'],
        ];
    }
}
