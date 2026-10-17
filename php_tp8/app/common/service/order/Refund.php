<?php
namespace app\common\service\order;

use think\facade\Db;
use think\facade\Log;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\library\pay\PayConfig;
use app\common\library\pay\WxPayV3;
use app\common\library\pay\AlipayTrade;

/**
 * 订单退款服务类：执行「原路退款」（真金白银退回用户）
 *
 * 移植自 php application/common/service/order/Refund.php，并按 TP8 的能力做了调整：
 *   - 微信：只走 v3（/v3/refund/domestic/refunds）。TP8 的 WxPay（v2）未实现 refund，
 *     v2 退款所需的双向证书也未迁移，因此 v3 未配置完成时直接报错提示，避免静默失败。
 *   - 支付宝：AlipayTrade::refund() 已具备，直接接入。
 *   - 余额：回退 user.balance 并写 user_balance_log（scene=40 订单退款）。
 *   - Stripe：当前不支持，提示线下处理。
 *
 * 幂等：调用方应传入固定的 $outRefundNo，微信侧以该单号做幂等键，重复提交不会重复退钱。
 */
class Refund
{
    /** @var string 余额变动场景：订单退款（对齐 user\balanceLog\Scene::REFUND） */
    const BALANCE_SCENE_REFUND = 40;

    private $error = '';

    /** @var string 本次退款的商户退款单号 */
    private $outRefundNo = '';

    /** @var array 网关原始返回 */
    private $result = [];

    public function getError()
    {
        return $this->error;
    }

    public function getOutRefundNo()
    {
        return $this->outRefundNo;
    }

    public function getResult()
    {
        return $this->result;
    }

    private function setError($msg)
    {
        $this->error = $msg;
        return false;
    }

    /**
     * 执行订单退款
     * @param array       $order 订单数组（需含 order_no / pay_price / pay_type / wxapp_id / user_id）
     * @param float|null  $money 退款金额，不传则默认退实付款全额
     * @param string      $outRefundNo 商户退款单号（幂等键），不传则自动生成
     * @return bool
     */
    public function execute($order, $money = null, $outRefundNo = '')
    {
        $money = is_null($money) ? (float)$order['pay_price'] : (float)$money;
        if ($money <= 0) {
            return $this->setError('退款金额必须大于 0');
        }
        if ((float)$order['pay_price'] <= 0) {
            return $this->setError('订单实付金额为 0，无需退款');
        }
        // 兼容 pay_type 为 ['text'=>..,'value'=>..] 与标量两种形态
        $payType = is_array($order['pay_type'] ?? null)
            ? (int)$order['pay_type']['value']
            : (int)($order['pay_type'] ?? 0);

        $this->outRefundNo = $outRefundNo !== ''
            ? $outRefundNo
            : 'R' . date('YmdHis') . strtoupper(substr(uniqid(), -6));

        switch ($payType) {
            case PayTypeEnum::WECHAT:
            case PayTypeEnum::FRIEND:   // 好友代付最终也是微信支付，退款走同一通道
                return $this->wxpay($order, $money);
            case PayTypeEnum::ALIPAY:
                return $this->alipay($order, $money);
            case PayTypeEnum::BALANCE:
                return $this->balance($order, $money);
            default:
                return $this->setError('当前支付方式不支持在线退款，请线下处理');
        }
    }

    /**
     * 微信原路退款（v3）
     */
    private function wxpay($order, $money)
    {
        $wxappId = (int)$order['wxapp_id'];
        if (!PayConfig::wechatV3Ready($wxappId)) {
            // v2 退款在 TP8 未实现（缺少双向证书与 refund 方法），不静默吞掉
            return $this->setError('微信支付 v3 配置不完整（mchid / 证书序列号 / 商户私钥 / APIv3 密钥），无法原路退款');
        }
        try {
            $wxPay = new WxPayV3(PayConfig::wechatV3($wxappId));
            $this->result = $wxPay->refund(
                $order['order_no'],
                $this->outRefundNo,
                (float)$order['pay_price'],
                $money,
                '订单退款'
            );
            return true;
        } catch (\Throwable $e) {
            Log::write('微信退款失败[' . $order['order_no'] . ']：' . $e->getMessage(), 'error');
            return $this->setError('微信退款失败：' . $e->getMessage());
        }
    }

    /**
     * 支付宝原路退款
     */
    private function alipay($order, $money)
    {
        try {
            $trade = new AlipayTrade(PayConfig::alipay((int)$order['wxapp_id']));
            $this->result = $trade->refund(
                $order['order_no'],
                $order['transaction_id'] ?? '',
                $money,
                $this->outRefundNo,
                '订单退款'
            );
            return true;
        } catch (\Throwable $e) {
            Log::write('支付宝退款失败[' . $order['order_no'] . ']：' . $e->getMessage(), 'error');
            return $this->setError('支付宝退款失败：' . $e->getMessage());
        }
    }

    /**
     * 余额退款：回退账户余额并写余额明细
     */
    private function balance($order, $money)
    {
        try {
            Db::name('user')
                ->where('user_id', $order['user_id'])
                ->inc('balance', $money)
                ->update();
            Db::name('user_balance_log')->insert([
                'user_id'     => $order['user_id'],
                'scene'       => self::BALANCE_SCENE_REFUND,
                'money'       => $money,
                'describe'    => '订单退款：' . $order['order_no'],
                'remark'      => '',
                'wxapp_id'    => $order['wxapp_id'],
                'create_time' => time(),
            ]);
            $this->result = ['channel' => 'balance', 'refund_fee' => $money];
            return true;
        } catch (\Throwable $e) {
            Log::write('余额退款失败[' . $order['order_no'] . ']：' . $e->getMessage(), 'error');
            return $this->setError('余额退款失败：' . $e->getMessage());
        }
    }
}
