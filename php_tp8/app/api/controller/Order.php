<?php
namespace app\api\controller;

use app\api\service\order\Checkout;
use app\common\enum\order\PayType;
use think\facade\Db;

/**
 * 订单控制器
 * - order/cart：购物车结算（GET 结算页 / POST 提交）
 * - order/buyNow：立即购买结算（GET 结算页 / POST 提交）
 * 支付参数（payment）由第 6 批补充，本批提交仅建单并返回 payment=null
 */
class Order extends Controller
{
    /** 购物车结算 */
    public function cart()
    {
        $user = $this->getUser(true);
        $model = new Checkout($user, $this->getWxappId(), $this->request->param());
        if ($this->request->isPost()) {
            $orderId = $model->createOrder('cart');
            if ($orderId === false) {
                return $this->renderError($model->getError() ?: '下单失败');
            }
            return $this->renderSuccess($this->buildSubmitResult($orderId));
        }
        $info = $model->checkout('cart');
        if ($info === false) {
            return $this->renderError($model->getError() ?: '结算失败');
        }
        return $this->renderSuccess($info);
    }

    /** 立即购买结算 */
    public function buyNow()
    {
        $user = $this->getUser(true);
        $model = new Checkout($user, $this->getWxappId(), $this->request->param());
        if ($this->request->isPost()) {
            $orderId = $model->createOrder('buyNow');
            if ($orderId === false) {
                return $this->renderError($model->getError() ?: '下单失败');
            }
            return $this->renderSuccess($this->buildSubmitResult($orderId));
        }
        $info = $model->checkout('buyNow');
        if ($info === false) {
            return $this->renderError($model->getError() ?: '结算失败');
        }
        return $this->renderSuccess($info);
    }

    /**
     * 组装下单提交结果
     * 好友代付（pay_type=30）只建单、不发起支付，额外返回可分享给好友的代付链接；
     * 其余支付方式返回 payment=null（支付参数由 user.order/pay 另行获取）。
     * @param int $orderId
     * @return array
     */
    private function buildSubmitResult($orderId)
    {
        $payType = (int)($this->request->param('pay_type') ?: 50);
        $result  = [
            'order_id' => $orderId,
            'pay_type' => $payType,
            'payment'  => null,
        ];
        if ($payType === PayType::FRIEND) {
            $result['order_no']       = (string)(Db::name('order')
                ->where('order_id', $orderId)->value('order_no') ?: '');
            $result['friend_pay_url'] = 'pages/order/friend-pay/index?order_id=' . $orderId;
        }
        return $result;
    }
}
