<?php
namespace app\store\model;

use app\common\model\BaseModel;
use app\common\service\order\Refund as RefundService;
use think\facade\Db;
use think\Collection;

/**
 * 售后单模型（服务端渲染版，移植自 TP5 store/model/OrderRefund.php）
 * 用 Db 直查自包含实现。
 *
 * 原路退款：receipt() 会调用 app\common\service\order\Refund::execute()，
 *   微信走 v3 /v3/refund/domestic/refunds、支付宝走 AlipayTrade::refund()、
 *   余额支付回退 user.balance 并写 user_balance_log(scene=40)。
 *   退款失败会整单回滚，避免「状态已退款但钱未退」。
 * 消息通知：receipt() 末尾调用 sendRefundMessage()，通知服务接入后自动生效。
 */
class OrderRefund extends BaseModel
{
    protected $name = 'order_refund';
    protected $pk = 'order_refund_id';
    protected $updateTime = false;

    /** 售后类型 */
    protected static $typeText = [10 => '退货退款', 20 => '换货'];
    /** 商家是否同意 */
    protected static $isAgreeText = [0 => '待审核', 10 => '已同意', 20 => '已拒绝'];
    /** 处理状态 */
    protected static $statusText = [0 => '进行中', 10 => '已拒绝', 20 => '已完成', 30 => '已取消'];

    /**
     * 售后单列表
     * @param array $query order_no / start_time / end_time / type / state
     * @return \think\Paginator
     */
    public function getList($query = [])
    {
        $q = Db::name('order_refund')->alias('m')
            ->join('order o', 'o.order_id = m.order_id')
            ->where('m.wxapp_id', self::getWxappId())
            ->field('m.*, o.order_no');
        if (!empty($query['order_no'])) {
            $q->whereLike('o.order_no', '%' . trim($query['order_no']) . '%');
        }
        if (!empty($query['start_time'])) {
            $q->where('m.create_time', '>=', strtotime($query['start_time']));
        }
        if (!empty($query['end_time'])) {
            $q->where('m.create_time', '<', strtotime($query['end_time']) + 86400);
        }
        if (!empty($query['type'])) {
            $q->where('m.type', '=', (int)$query['type']);
        }
        if (isset($query['state']) && is_numeric($query['state'])) {
            $q->where('m.status', '=', (int)$query['state']);
        }
        $list = $q->order('m.create_time', 'desc')
            ->paginate(10, false, ['query' => request()->param()]);
        $items = $list->items();
        foreach ($items as &$row) {
            $row = $this->enrich($row);
        }
        $list->setCollection(Collection::make($items));
        return $list;
    }

    /**
     * 售后单详情
     * @param int $orderRefundId
     * @return array
     */
    public static function detail($orderRefundId)
    {
        $row = Db::name('order_refund')->alias('m')
            ->join('order o', 'o.order_id = m.order_id')
            ->where('m.order_refund_id', $orderRefundId)
            ->field('m.*, o.order_no')
            ->find();
        if (empty($row)) {
            return [];
        }
        $model = new self;
        $row = $model->enrich($row);
        // 用户上传的凭证图片
        $imageIds = Db::name('order_refund_image')
            ->where('order_refund_id', $orderRefundId)
            ->column('image_id');
        $row['image'] = array_map(function ($id) {
            return ['file_path' => self::getFileUrl($id)];
        }, $imageIds);
        // 退货地址（同意售后时写入）
        $row['address'] = Db::name('order_refund_address')
            ->where('order_refund_id', $orderRefundId)->find() ?: [];
        return $row;
    }

    /**
     * 组装单条售后单展示数据
     */
    private function enrich(array $row)
    {
        $row['type'] = [
            'text'  => self::$typeText[$row['type']] ?? '未知',
            'value' => (int)$row['type'],
        ];
        $row['status'] = [
            'text'  => self::$statusText[$row['status']] ?? '未知',
            'value' => (int)$row['status'],
        ];
        $row['is_agree'] = [
            'text'  => self::$isAgreeText[$row['is_agree']] ?? '未知',
            'value' => (int)$row['is_agree'],
        ];
        // 售后商品
        $goods = Db::name('order_goods')
            ->where('order_goods_id', $row['order_goods_id'])->find() ?: [];
        if ($goods) {
            $goods['image'] = ['file_path' => BaseModel::getFileUrl($goods['image_id'] ?? 0)];
        }
        $row['order_goods'] = $goods;
        // 用户
        $row['user'] = Db::name('user')->where('user_id', $row['user_id'])
            ->field('user_id, nickName')->find()
            ?: ['user_id' => $row['user_id'], 'nickName' => ''];
        return $row;
    }

    /**
     * 商家审核
     * @param int   $orderRefundId
     * @param array $data is_agree / refuse_desc / address_id
     * @return bool
     */
    public function audit($orderRefundId, array $data)
    {
        $row = Db::name('order_refund')->where('order_refund_id', $orderRefundId)->find();
        if (empty($row)) {
            $this->error = '售后单不存在';
            return false;
        }
        $isAgree = (int)$data['is_agree'];
        if ($isAgree === 20 && empty($data['refuse_desc'])) {
            $this->error = '请输入拒绝原因';
            return false;
        }
        if ($isAgree === 10 && empty($data['address_id'])) {
            $this->error = '请选择退货地址';
            return false;
        }
        // 拒绝 -> 已拒绝；同意换货 -> 已完成；同意退货退款 -> 保持进行中（等待收货退款）
        $status = (int)$row['status'];
        if ($isAgree === 20) {
            $status = 10;
        } elseif ($isAgree === 10 && (int)$row['type'] === 20) {
            $status = 20;
        }
        Db::startTrans();
        try {
            Db::name('order_refund')->where('order_refund_id', $orderRefundId)->update([
                'is_agree'    => $isAgree,
                'refuse_desc' => $data['refuse_desc'] ?? '',
                'status'      => $status,
                'update_time' => time(),
            ]);
            // 同意售后：记录退货地址
            if ($isAgree === 10) {
                $this->saveRefundAddress($orderRefundId, (int)$data['address_id']);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 确认收货并退款
     * @param int   $orderRefundId
     * @param array $data refund_money
     * @return bool
     */
    public function receipt($orderRefundId, array $data)
    {
        $row = Db::name('order_refund')->where('order_refund_id', $orderRefundId)->find();
        if (empty($row)) {
            $this->error = '售后单不存在';
            return false;
        }
        $order = Db::name('order')->where('order_id', $row['order_id'])->find();
        $goods = Db::name('order_goods')
            ->where('order_goods_id', $row['order_goods_id'])->find() ?: [];
        $maxRefund = min((float)$order['pay_price'], (float)($goods['total_pay_price'] ?? 0));
        if ((float)$data['refund_money'] > $maxRefund) {
            $this->error = '退款金额不能大于商品实付款金额';
            return false;
        }
        Db::startTrans();
        try {
            Db::name('order_refund')->where('order_refund_id', $orderRefundId)->update([
                'refund_money' => (float)$data['refund_money'],
                'is_receipt'   => 1,
                'status'       => 20,
                'update_time'  => time(),
            ]);
            // 原路退款（真金白银退回用户）：
            // 以售后单 id 生成固定的商户退款单号，重复提交时微信/支付宝侧幂等，不会重复退钱
            $refundMoney = (float)$data['refund_money'];
            $outRefundNo = 'RF' . $orderRefundId . strtoupper(substr(md5((string)$orderRefundId), 0, 6));
            $RefundService = new RefundService;
            if (!$RefundService->execute($order, $refundMoney, $outRefundNo)) {
                // 退款失败则整单回滚，避免出现「状态已退款但钱没退」的账实不符
                throw new \Exception($RefundService->getError() ?: '退款失败');
            }
            // 解析网关返回的对账信息，写入售后单便于财务对账
            //   refund_no  : 我方商户退款单号（网关幂等键）
            //   refund_id  : 网关退款单号（微信 refund_id / 支付宝 trade_no，余额渠道为空）
            //   refund_time: 退款成功时间（微信 success_time / 支付宝 gmt_refund_pay，余额即时）
            // 说明：微信 v3 退款为异步，受理瞬间可能仍 PROCESSING（无 success_time），
            //       此处置受理时间兜底；如后续接入退款结果通知/查询可再精确更新。
            $gwResult     = $RefundService->getResult();
            $gwRefundId   = $gwResult['refund_id'] ?? ($gwResult['trade_no'] ?? '');
            $gwRefundTime = $gwResult['success_time'] ?? ($gwResult['gmt_refund_pay'] ?? '');
            $gwRefundTime = $gwRefundTime !== ''
                ? (strtotime((string)$gwRefundTime) ?: time())
                : time();
            // 写入对账字段（与上面首部 update 同处一个事务，退款失败整体回滚）
            Db::name('order_refund')->where('order_refund_id', $orderRefundId)->update([
                'refund_no'   => $outRefundNo,
                'refund_id'   => $gwRefundId,
                'refund_time' => $gwRefundTime,
            ]);
            // 已结算订单：消减用户累计消费额（对齐 TP5 的 setDecUserExpend）
            if (!empty($order['is_settled'])) {
                Db::name('user')
                    ->where('user_id', $order['user_id'])
                    ->dec('expend_money', $refundMoney)
                    ->update();
            }
            // 售后消息通知（订阅消息未配置时静默跳过，不影响退款主流程）
            $this->sendRefundMessage($row, $order, $refundMoney);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 记录退货地址（从退货地址库复制一份到售后单）
     */
    private function saveRefundAddress($orderRefundId, $addressId)
    {
        $addr = Db::name('return_address')->where('address_id', $addressId)->find();
        if (empty($addr)) {
            return;
        }
        Db::name('order_refund_address')->insert([
            'order_refund_id' => $orderRefundId,
            'name'            => $addr['name'] ?? '',
            'phone'           => $addr['phone'] ?? '',
            'detail'          => $addr['detail'] ?? '',
            'wxapp_id'        => self::getWxappId(),
            'create_time'     => time(),
        ]);
    }

    /**
     * 售后结果通知（微信订阅消息）
     *
     * 采用 class_exists 探测：app\common\service\Message 尚未迁移时静默跳过，
     * 不阻断退款主流程；通知服务接入后本方法自动生效，无需再改此处。
     *
     * @param array $refund 售后单
     * @param array $order  订单
     * @param float $money  退款金额
     */
    private function sendRefundMessage(array $refund, array $order, $money)
    {
        if (!class_exists(\app\common\service\Message::class)) {
            return;
        }
        try {
            \app\common\service\Message::send('order.refund', [
                'refund' => $refund,
                'order'  => $order,
                'money'  => $money,
            ]);
        } catch (\Throwable $e) {
            // 通知失败不影响已完成的退款
            log_write('售后通知发送失败[order_refund_id=' . ($refund['order_refund_id'] ?? 0) . ']：' . $e->getMessage());
        }
    }
}
