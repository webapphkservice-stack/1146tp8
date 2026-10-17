<?php
namespace app\api\controller\order;

use app\api\controller\Controller;
use app\api\service\Payment;
use app\common\enum\order\PayType;
use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 好友代付（对齐 RN payment 选择"好友代付"流程）
 *  - detail 代付人查看订单与买家信息（脱敏）
 *  - pay    代付人发起支付（生成临时 out_trade_no 的支付参数）
 *
 * 【闭环原理】代付时提交给微信的是「临时商户单号」= 真实单号 + 'F' + 随机，
 * 而 $order['order_no']（真实单号）会由 Payment::getParams() 的第 4 个参数机制
 * 写入微信下单的 attach 字段。支付成功后，Notify::resolveOrderNo() 按
 * 「精确匹配 -> 剥离 F 后缀 -> 解析 attach」三级兜底还原真实订单号，
 * 再走 OrderStateMachine::paySuccess() 完成状态流转。
 *
 * 因此不需要额外的「代付映射表」，与 php（TP5）的实现方式一致。
 */
class Friendpay extends Controller
{
    /**
     * 代付详情
     */
    public function detail()
    {
        $user = $this->getUser();
        $order = $this->getOrder();
        if (is_string($order)) {
            return $this->renderError($order);
        }

        $buyer = Db::name('user')->where('user_id', $order['user_id'])
            ->field('nickName, avatarUrl')->find() ?: [];
        $isSelf = (int)$order['user_id'] === (int)$user['user_id'];

        $goods = Db::name('order_goods')->where('order_id', $order['order_id'])
            ->field('goods_name, image_id, goods_attr, total_num, total_pay_price')
            ->select()->toArray();
        foreach ($goods as &$g) {
            $g['goods_image'] = BaseModel::getFileUrl($g['image_id'] ?? 0);
        }
        unset($g);

        // 只返回代付页需要的字段，避免泄露收货地址 / 手机号 / transaction_id
        $orderInfo = [
            'order_id'       => (int)$order['order_id'],
            'order_no'       => $order['order_no'],
            'total_price'    => $order['total_price'],
            'pay_price'      => $order['pay_price'],
            'express_price'  => $order['express_price'],
            'create_time'    => $order['create_time'],
            'pay_status'     => (int)$order['pay_status'],
            'order_status'   => (int)$order['order_status'],
            'delivery_type'  => (int)$order['delivery_type'],
            'buyer_remark'   => $order['buyer_remark'] ?? '',
        ];

        return $this->renderSuccess([
            'order'   => $orderInfo,
            'buyer'   => $buyer,
            'is_self' => $isSelf,
            'goods'   => $goods,
        ]);
    }

    /**
     * 发起代付支付
     */
    public function pay()
    {
        $user = $this->getUser();
        $order = $this->getOrder();
        if (is_string($order)) {
            return $this->renderError($order);
        }
        if ((int)$order['user_id'] === (int)$user['user_id']) {
            return $this->renderError('不能为自己代付');
        }
        if ((int)$order['pay_status'] === 20) {
            return $this->renderError('该订单已支付');
        }
        // 订单必须处于「进行中」，已取消/已完成的订单不允许代付
        if ((int)$order['order_status'] !== 10) {
            return $this->renderError('该订单当前状态不可代付');
        }

        // 临时商户单号：真实单号 + 'F' + 随机（回调时据此还原真实订单）
        $tempOrderNo = $order['order_no'] . 'F' . mt_rand(1000, 9999);
        // 注意：$order['order_no'] 保持真实单号（写入 attach），
        // 第 4 个参数才是实际提交给微信的商户单号
        $payment = (new Payment())->getParams(PayType::WECHAT, $order, $user, $tempOrderNo);

        return $this->renderSuccess([
            'order_id' => (int)$order['order_id'],
            'pay_type' => PayType::WECHAT,
            'payment'  => $payment,
        ]);
    }

    /**
     * 取当前请求的订单（失败返回错误文案字符串）
     * @return array|string
     */
    private function getOrder()
    {
        $orderId = (int)$this->request->param('order_id');
        $order = Db::name('order')
            ->where('order_id', $orderId)
            ->where('wxapp_id', BaseModel::getWxappId())
            ->where('is_delete', 0)
            ->find();
        if (!$order) {
            return '订单不存在';
        }
        return $order;
    }
}
