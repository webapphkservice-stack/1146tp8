<?php
namespace app\api\service;

use think\facade\Db;
use app\common\enum\order\OrderSource;
use app\api\service\sharp\PaySuccess;
use app\api\service\dealer\OrderService as DealerOrderService;

/**
 * 订单状态机（第 7 批：订单状态机收口）
 *
 * 作为所有订单状态迁移的唯一引擎（single source of truth），替代原先散落在
 * Order 模型 cancel/receipt/onPaySuccess 中的内联更新逻辑，统一：
 *   1) 状态迁移守卫（非法迁移直接失败）
 *   2) 幂等（网关回调重试、重复操作均安全）
 *   3) 事务一致性（pay 改单 + 用户消费金额累计在一个事务内）
 *
 * 状态常量与 Order 模型保持一致；各事件允许的迁移见 transitionMap()。
 * 真实第三方 SDK（WxPay / 支付宝 aop / Stripe）缺失时，回调入口 Notify* 仅做
 * 结构/状态校验，签名验签处均有 TODO 标注待接入点。
 */
class OrderStateMachine
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
    const ORDER_NORMAL      = 10;
    const ORDER_CANCELLED   = 20;
    const ORDER_APPLY_CANCEL = 21;
    const ORDER_COMPLETED   = 30;

    // 事件
    const EVENT_PAY     = 'pay';
    const EVENT_CANCEL  = 'cancel';
    const EVENT_DELIVER = 'deliver';
    const EVENT_RECEIPT = 'receipt';

    public function getError()
    {
        return $this->error;
    }

    /**
     * 事件 -> 允许的状态迁移映射（from => to）。
     * 未列出的 (事件, from) 组合视为非法迁移。
     * 注：pay 事件只改 pay_status，不改变 order_status，故 order_status 迁移为空。
     */
    public static function transitionMap()
    {
        return [
            self::EVENT_PAY => [
                'pay_status'   => [self::PAY_PENDING => self::PAY_SUCCESS],
                'order_status' => [], // 支付不改变订单主状态
            ],
            self::EVENT_CANCEL => [
                'order_status' => [
                    self::ORDER_NORMAL => self::ORDER_CANCELLED, // 未付款取消
                    // 已付款取消进入「待取消」(退款流程外部处理)
                    self::PAY_SUCCESS => self::ORDER_APPLY_CANCEL, // 占位：cancel() 内部按 isPay 判定
                ],
            ],
            self::EVENT_DELIVER => [
                'delivery_status' => [self::DELIVERY_WAIT => self::DELIVERY_DONE],
            ],
            self::EVENT_RECEIPT => [
                'receipt_status' => [self::RECEIPT_WAIT => self::RECEIPT_DONE],
                'order_status'   => [self::ORDER_NORMAL => self::ORDER_COMPLETED],
            ],
        ];
    }

    /**
     * 生成「自减但不允许小于 0」的 SQL 表达式
     *
     * 背景：yoshop_goods.sales_actual 为 unsigned 整型，使用 ->dec() 自减时，
     * 若当前值小于减数（如销量 0 时回退 2 件），MySQL 会抛
     * 「BIGINT UNSIGNED value is out of range」导致整个事务失败。
     * 取消订单 / 自动关闭订单 / 审核取消 三处回退销量都必须走这个方法。
     *
     * @param string $field 字段名
     * @param int    $num   减数（已强制转为非负整数）
     * @return \think\db\Raw
     */
    public static function decFloorRaw($field, $num)
    {
        $num = max(0, (int)$num);
        // 注意：不能写成 GREATEST(0, `field` - N)：MySQL 会先计算内部的减法，
        // 该子表达式本身仍会触发 unsigned 下溢，GREATEST 无法兜底。
        // 必须用 IF 短路，保证减数大于当前值时根本不去执行减法。
        return Db::raw('IF(`' . $field . '` >= ' . $num . ', `' . $field . '` - ' . $num . ', 0)');
    }

    // -------------------- 支付成功（pay 事件） --------------------

    /**
     * 支付成功：pay_status 10 -> 20。
     * 幂等：已支付直接返回 true；非法订单态（已取消/待取消/已完成）返回 false。
     * 同步累加用户消费金额 expend_money（移植自 php_my PaySuccess::onPaySuccess）。
     * @param string $orderNo
     * @param int    $payType
     * @param string $transactionId 第三方交易号，余额支付可为空
     * @return bool
     */
    public function paySuccess($orderNo, $payType, $transactionId = '')
    {
        $order = Db::name('order')
            ->where('order_no', $orderNo)
            ->where('is_delete', 0)
            ->find();
        if (empty($order)) {
            $this->error = '未找到该订单';
            return false;
        }
        // 幂等：已支付直接成功（网关重试安全）
        if ($order['pay_status'] == self::PAY_SUCCESS) {
            return true;
        }
        // 状态机守卫：仅 NORMAL 订单可被支付成功
        if ((int)$order['order_status'] !== self::ORDER_NORMAL) {
            $this->error = '当前订单状态不可支付';
            return false;
        }
        return Db::transaction(function () use ($order, $payType, $transactionId) {
            Db::name('order')->where('order_id', $order['order_id'])->update([
                'pay_type'       => $payType,
                'pay_status'     => self::PAY_SUCCESS,
                'pay_time'       => time(),
                'transaction_id' => $transactionId,
                'update_time'    => time(),
            ]);
            Db::name('user')->where('user_id', $order['user_id'])
                ->inc('expend_money', $order['pay_price'])->update();
            // 各来源支付成功后续（秒杀：加销量 + 扣付款减库存）
            self::dispatchPaySuccess($order);
            // 分销订单创建（失败静默降级，不阻断支付）
            DealerOrderService::createOnPaySuccess($order);
            return true;
        });
    }

    /**
     * 支付成功后，按订单来源分派来源专属后续处理（如秒杀销量累计 / 付款减库存扣减）。
     * 注：Db::transaction 闭包内不可用 $this（普通闭包不绑定 $this），故为 static。
     * @param array $order
     */
    private static function dispatchPaySuccess($order)
    {
        if ((int)$order['order_source'] === OrderSource::SHARP) {
            PaySuccess::onPaySuccess($order);
        }
    }

    // -------------------- 取消订单（cancel 事件） --------------------

    /**
     * 取消订单（用户侧）。
     *  - 未付款（pay_status=10）：回退库存与销量，order_status -> 20（已取消）
     *  - 已付款（pay_status=20）：order_status -> 21（待取消，退款流程外部）
     * 幂等：已取消/待取消返回 true；已完成/已发货返回 false。
     * @return bool
     */
    public function cancel($orderId, $userId)
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
        // 幂等：已取消 / 待取消 直接成功
        if (in_array((int)$order['order_status'], [self::ORDER_CANCELLED, self::ORDER_APPLY_CANCEL])) {
            return true;
        }
        if ((int)$order['order_status'] === self::ORDER_COMPLETED) {
            $this->error = '订单已完成，不可取消';
            return false;
        }
        if ($order['delivery_status'] == self::DELIVERY_DONE) {
            $this->error = '已发货订单不可取消';
            return false;
        }
        $isPay = $order['pay_status'] == self::PAY_SUCCESS;
        return Db::transaction(function () use ($order, $isPay) {
            if (!$isPay) {
                // 未付款：回退库存与销量（按订单来源分派）
                $goodsList = Db::name('order_goods')
                    ->where('order_id', $order['order_id'])
                    ->select()->toArray();
                if ((int)$order['order_source'] === OrderSource::SHARP) {
                    // 秒杀：仅「下单减库存」回退 seckill_stock（不影响普通商品库存/销量）
                    PaySuccess::onCancel($order);
                } else {
                    foreach ($goodsList as $g) {
                        Db::name('goods_sku')->where('goods_sku_id', $g['goods_sku_id'])
                            ->inc('stock_num', $g['total_num'])->update();
                        // sales_actual 为 unsigned，直接 dec 在销量为 0 时会触发
                        // 「BIGINT UNSIGNED value is out of range」，故用 GREATEST 兜底
                        Db::name('goods')->where('goods_id', $g['goods_id'])
                            ->update(['sales_actual' => self::decFloorRaw('sales_actual', (int)$g['total_num'])]);
                    }
                }
                $status = self::ORDER_CANCELLED;
                // 退还优惠券与积分（下单时已核销/扣除，取消必须还原）
                self::rollbackAssets($order, '订单取消退还');
            } else {
                // 已付款：进入「待取消」（退款流程不在本批范围）
                $status = self::ORDER_APPLY_CANCEL;
            }
            Db::name('order')->where('order_id', $order['order_id'])
                ->update(['order_status' => $status, 'update_time' => time()]);
            return true;
        });
    }

    /**
     * 退还订单占用的优惠券与积分（取消订单 / 超时关闭订单时调用）
     *
     * 幂等：只依据订单快照上的 coupon_id / points_num 退还一次；
     *      重复调用会把已退过的券再次置为未使用（无副作用），
     *      但积分会重复加回，故调用方需自行保证同一订单只调用一次
     *      （cancel 有幂等守卫、task close 只处理 pay_status=10 的订单）。
     *
     * @param array  $order  订单行（需含 order_id / order_no / coupon_id / points_num / user_id / wxapp_id）
     * @param string $reason 流水描述前缀
     * @return bool 是否发生了退还
     */
    public static function rollbackAssets($order, $reason = '订单取消退还')
    {
        $rolled = false;
        // 退还优惠券：order.coupon_id 存的是 user_coupon_id
        if (!empty($order['coupon_id'])) {
            \app\api\model\UserCoupon::setIsUse((int)$order['coupon_id'], false);
            $rolled = true;
        }
        // 退还积分
        if (!empty($order['points_num'])) {
            \app\api\model\User::changePoints(
                (int)$order['user_id'],
                abs((int)$order['points_num']),
                $reason . '：' . $order['order_no'],
                (int)($order['wxapp_id'] ?? 0)
            );
            $rolled = true;
        }
        // 退还水票（下单时抵扣的桶数，取消订单需还原，场景=取消退回）
        if (!empty($order['water_ticket_num'])) {
            \app\common\model\UserWaterTicket::adjust(
                (int)$order['user_id'],
                abs((int)$order['water_ticket_num']),
                (int)($order['wxapp_id'] ?? 0),
                \app\common\model\UserWaterTicketLog::SCENE_REFUND,
                $order['order_no']
            );
            $rolled = true;
        }
        return $rolled;
    }

    // -------------------- 发货（deliver 事件，管理端调用） --------------------

    /**
     * 发货：delivery_status 10 -> 20。幂等。
     * @param int   $orderId
     * @param array $express 物流信息 express_id / express_company / express_no（商家后台发货时传入）
     * @return bool
     */
    public function deliver($orderId, array $express = [])
    {
        $order = Db::name('order')
            ->where('order_id', $orderId)
            ->where('is_delete', 0)
            ->find();
        if (empty($order)) {
            $this->error = '订单不存在';
            return false;
        }
        if ($order['pay_status'] != self::PAY_SUCCESS) {
            $this->error = '未支付订单不可发货';
            return false;
        }
        if ($order['delivery_status'] == self::DELIVERY_DONE) {
            return true; // 幂等
        }
        if ($order['delivery_status'] != self::DELIVERY_WAIT) {
            $this->error = '发货状态不合法';
            return false;
        }
        return Db::transaction(function () use ($order, $express) {
            $data = [
                'delivery_status' => self::DELIVERY_DONE,
                'delivery_time'   => time(),
                'update_time'     => time(),
            ];
            // 物流信息（express_id / express_company / express_no）由商家后台发货写入
            foreach (['express_id', 'express_company', 'express_no'] as $field) {
                if (isset($express[$field])) {
                    $data[$field] = $express[$field];
                }
            }
            Db::name('order')->where('order_id', $order['order_id'])->update($data);
            return true;
        });
    }

    // -------------------- 确认收货（receipt 事件） --------------------

    /**
     * 确认收货：receipt_status 10 -> 20 且 order_status 10 -> 30。幂等。
     * @return bool
     */
    public function receipt($orderId, $userId)
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
        // 幂等：已收货 / 已完成 直接成功
        if ($order['receipt_status'] == self::RECEIPT_DONE
            || (int)$order['order_status'] === self::ORDER_COMPLETED) {
            return true;
        }
        if ($order['delivery_status'] != self::DELIVERY_DONE
            || $order['receipt_status'] != self::RECEIPT_WAIT) {
            $this->error = '该订单不合法';
            return false;
        }
        return Db::transaction(function () use ($order) {
            // 并发保护：事务内加行锁二次确认，避免并发请求重复发放积分
            $fresh = Db::name('order')->where('order_id', $order['order_id'])
                ->lock(true)->find();
            if ($fresh['receipt_status'] == self::RECEIPT_DONE
                || (int)$fresh['order_status'] === self::ORDER_COMPLETED) {
                return true;
            }
            Db::name('order')->where('order_id', $order['order_id'])->update([
                'receipt_status' => self::RECEIPT_DONE,
                'receipt_time'   => time(),
                'order_status'   => self::ORDER_COMPLETED,
                'update_time'    => time(),
            ]);
            // 发放购物赠送积分（下行结算时只计算了数量，此处为实际到账时机，
            // 与 php_my 一致：确认收货后才发放）
            if (!empty($order['points_bonus'])) {
                \app\api\model\User::changePoints(
                    (int)$order['user_id'],
                    abs((int)$order['points_bonus']),
                    '订单完成赠送积分：' . $order['order_no'],
                    (int)($order['wxapp_id'] ?? 0)
                );
            }
            return true;
        });
    }
}
