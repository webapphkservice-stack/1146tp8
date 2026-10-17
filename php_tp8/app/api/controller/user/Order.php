<?php
namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\Order as OrderModel;
use app\common\enum\order\PayType;

/**
 * 用户订单控制器（对齐 RN order.ts：user.order/lists|detail|express|cancel|receipt|pay）
 */
class Order extends Controller
{
    public function initialize()
    {
        parent::initialize();
        $this->getUser(true); // 强制登录，填充 static::$user
    }

    /**
     * 订单列表
     * dataType: all/payment/delivery/received/comment
     */
    public function lists()
    {
        $dataType = $this->request->param('dataType', 'all');
        $model = new OrderModel;
        $list = $model->getList(static::$user['user_id'], $dataType);
        return $this->renderSuccess($list);
    }

    /**
     * 订单详情（支持 order_id 或 order_no）
     */
    public function detail()
    {
        $orderId = (int)$this->request->param('order_id', 0);
        $orderNo = $this->request->param('order_no', '');
        $model = new OrderModel;
        if (empty($orderId) && $orderNo !== '') {
            $orderId = $model->getOrderIdByNo($orderNo, static::$user['user_id']);
        }
        if (empty($orderId)) {
            return $this->renderError($model->getError() ?: '订单不存在');
        }
        $order = $model->getUserOrderDetail($orderId, static::$user['user_id']);
        if ($order === false) {
            return $this->renderError($model->getError());
        }
        return $this->renderSuccess([
            'order'   => $order,
            'setting' => ['points_name' => '积分'],
        ]);
    }

    /**
     * 物流信息（运单号不存在时返回错误，由 RN 提示）
     */
    public function express()
    {
        $orderId = (int)$this->request->param('order_id', 0);
        $model = new OrderModel;
        $order = $model->getUserOrderDetail($orderId, static::$user['user_id']);
        if ($order === false) {
            return $this->renderError($model->getError());
        }
        if (empty($order['express_no'])) {
            return $this->renderError('没有物流信息');
        }
        return $this->renderSuccess([
            'express' => [
                'express_company' => $order['express_company'],
                'express_no'      => $order['express_no'],
                'express_code'    => $order['express_code'] ?? '',
                'list'            => [], // 真实轨迹需接入第三方物流 API（不在本批范围）
            ],
        ]);
    }

    /**
     * 取消订单
     */
    public function cancel()
    {
        $orderId = (int)$this->request->param('order_id', 0);
        $model = new OrderModel;
        if ($model->cancel($orderId, static::$user['user_id'], static::$user)) {
            return $this->renderSuccess();
        }
        return $this->renderError($model->getError() ?: '订单取消失败');
    }

    /**
     * 确认收货
     */
    public function receipt()
    {
        $orderId = (int)$this->request->param('order_id', 0);
        $model = new OrderModel;
        if ($model->receipt($orderId, static::$user['user_id'])) {
            return $this->renderSuccess();
        }
        return $this->renderError($model->getError());
    }

    /**
     * 订单支付（微信/支付宝/Stripe/余额/好友代付）
     * RN 调用：orderApi.pay({ order_id })；pay_type 可选（默认微信 20）
     * H5 前端调用：orderApi.pay({ order_id, pay_type: 20, platform: 'h5', redirect_url: 'https://.../result' })
     *   → platform=h5 时返回 {trade_type:'MWEB', mweb_url, out_trade_no, total_fee}
     *     前端 window.location.href = payment.mweb_url 即可拉起微信支付
     *     （redirect_url 会自动拼到 mweb_url 上，支付完自动跳回）
     */
    public function pay()
    {
        $orderId    = (int)$this->request->param('order_id', 0);
        $payType    = (int)$this->request->param('pay_type', PayType::WECHAT);
        $platform   = trim((string)$this->request->param('platform', ''));
        $redirect   = trim((string)$this->request->param('redirect_url', ''));
        $model = new OrderModel;
        $result = $model->pay($orderId, static::$user['user_id'], static::$user, $payType, $platform, $redirect);
        if ($result === false) {
            return $this->renderError($model->getError() ?: '支付发起失败');
        }
        return $this->renderSuccess([
            'order_id' => $result['order_id'],
            'pay_type' => $result['pay_type'],
            'payment'  => $result['payment'],
        ]);
    }

    /**
     * 获取订单核销二维码（自提订单，微信小程序码完整方案）
     */
    public function extractQrcode()
    {
        $user = $this->getUser(true);
        $orderId = (int)$this->request->param('order_id', 0);
        $order = \think\facade\Db::name('order')->where('order_id', $orderId)
            ->where('user_id', $user['user_id'])->where('is_delete', 0)->find();
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        if ((int)$order['pay_status'] != 20 || (int)$order['delivery_type'] != 20) {
            return $this->renderError('该订单暂不可核销');
        }
        try {
            $qrcode = \app\common\service\qrcode\Extract::build(
                $this->getWxappId(), $user, $orderId, 10
            );
            return $this->renderSuccess(['qrcode' => $qrcode]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
}
