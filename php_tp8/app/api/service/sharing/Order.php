<?php

namespace app\api\service\sharing;

use app\api\service\order\Checkout as CheckoutCalc;
use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 拼团订单服务（完整移植 php sharing order Checkout + model/sharing/Order 用户端语义）
 *
 * 订单落库：独立 sharing_order / sharing_order_goods 表；
 * 结算复用普通 Checkout 的会员折扣/优惠券/积分分摊计算；
 * 支付成功后由 onPaySuccess 开团/入团并判成团（sharing_active）。
 */
class Order
{
    public static $lastError = '';

    public static function setError($msg)
    {
        self::$lastError = $msg;
        return false;
    }

    /**
     * 结算商品（拼团价 + 主商品校验 + 库存 + 加入已有团校验）
     */
    public static function getCheckoutGoodsList($goodsId, $goodsSkuId, $goodsNum, $activeId, $userId, $wxappId, $lock = false)
    {
        self::$lastError = '';
        $goodsId = (int)$goodsId;
        $goodsNum = max(1, (int)$goodsNum);
        $sharingGoods = Db::name('sharing_goods')->where('goods_id', $goodsId)
            ->where('goods_status', 10)->where('is_delete', 0)
            ->where('wxapp_id', $wxappId)->find();
        if (empty($sharingGoods)) {
            return self::setError('很抱歉，该拼团商品不存在或已下架');
        }
        if ($activeId > 0) {
            $active = Db::name('sharing_active')->where('active_id', $activeId)
                ->where('status', 10)->where('wxapp_id', $wxappId)->find();
            if (empty($active)) {
                return self::setError('很抱歉，该拼团活动已结束');
            }
            if ((int)$active['goods_id'] !== $goodsId) {
                return self::setError('很抱歉，拼团活动与商品不匹配');
            }
            if ((int)$active['actual_people'] >= (int)$active['people']) {
                return self::setError('很抱歉，该团已满员');
            }
            if ($active['end_time'] > 0 && $active['end_time'] < time()) {
                return self::setError('很抱歉，该团已超时');
            }
        }
        $goods = Db::name('goods')->where('goods_id', $goodsId)
            ->where('goods_status', 10)->where('is_delete', 0)
            ->field('goods_id,goods_name,goods_type,spec_type')->find();
        if (empty($goods)) {
            return self::setError('很抱歉，商品不存在或已下架');
        }
        $skuQuery = Db::name('sharing_goods_sku')->where('goods_id', $goodsId)
            ->where('spec_sku_id', (string)$goodsSkuId);
        if ($lock) {
            $skuQuery->lock(true);
        }
        $sku = $skuQuery->find();
        if (empty($sku)) {
            return self::setError('很抱歉，该商品规格不存在');
        }
        if ($goodsNum > (int)$sku['stock_num']) {
            return self::setError('很抱歉，商品「' . $goods['goods_name'] . '」库存不足');
        }
        $mainSku = Db::name('goods_sku')->where('goods_id', $goodsId)
            ->where('spec_sku_id', (string)$goodsSkuId)
            ->field('goods_no,line_price,goods_weight,image_id')->find();
        $imageId = !empty($mainSku['image_id'])
            ? (int)$mainSku['image_id']
            : (int)Db::name('goods_image')->where('goods_id', $goodsId)->order('id', 'asc')->value('image_id');
        return [
            'goods_id' => $goodsId,
            'goods_name' => $sharingGoods['goods_name'] ?: $goods['goods_name'],
            'selling_point' => (string)$sharingGoods['selling_point'],
            'people' => (int)$sharingGoods['people'],
            'group_time' => (int)$sharingGoods['group_time'],
            'is_alone' => (int)$sharingGoods['is_alone'],
            'image_id' => $imageId,
            'goods_image' => BaseModel::getFileUrl($imageId),
            'spec_type' => (int)($goods['spec_type'] ?? 10),
            'spec_sku_id' => (string)$goodsSkuId,
            'goods_sku_id' => (int)$sku['goods_sku_id'],
            'goods_no' => $mainSku['goods_no'] ?? '',
            'line_price' => (float)($mainSku['line_price'] ?? 0),
            'goods_weight' => (float)($mainSku['goods_weight'] ?? 0),
            'goods_price' => (float)$sku['sharing_price'],
            'total_num' => $goodsNum,
            'total_price' => bcmul((string)$sku['sharing_price'], (string)$goodsNum, 2),
            'deduct_stock_type' => (int)$sharingGoods['deduct_stock_type'],
            'stock_num' => (int)$sku['stock_num'],
            'goods_type' => (int)$goods['goods_type'],
            'is_points_gift' => (int)$sharingGoods['is_points_gift'],
            'is_points_discount' => (int)$sharingGoods['is_points_discount'],
            'is_enable_grade' => (int)$sharingGoods['is_enable_grade'],
            'is_alone_grade' => (int)$sharingGoods['is_alone_grade'],
            'alone_grade_equity' => $sharingGoods['alone_grade_equity'] ? json_decode($sharingGoods['alone_grade_equity'], true) : [],
        ];
    }

    /**
     * 创建拼团订单，返回 sharing_order.order_id
     */
    public function createOrder($user, $wxappId, $param)
    {
        $goodsId = (int)($param['goods_id'] ?? 0);
        $goodsSkuId = (string)($param['goods_sku_id'] ?? '0');
        $goodsNum = max(1, (int)($param['goods_num'] ?? 1));
        $activeId = (int)($param['active_id'] ?? 0);
        $userId = (int)$user['user_id'];
        $delivery = (int)($param['delivery'] ?? 10);
        $payType = (int)($param['pay_type'] ?? 50);
        $remark = trim((string)($param['remark'] ?? ''));
        $extractShopId = 0;
        $linkman = trim((string)($param['linkman'] ?? ''));
        $phone = trim((string)($param['phone'] ?? ''));
        if ($delivery == 20) {
            $extractShopId = (int)($param['shop_id'] ?? 0);
            if ($extractShopId <= 0) {
                return self::setError('请选择自提门店');
            }
            if ($linkman === '' || $phone === '') {
                return self::setError('请填写自提联系人和电话');
            }
        }
        try {
            $that = $this;
            $orderId = Db::transaction(function () use (
                $that, $goodsId, $goodsSkuId, $goodsNum, $activeId, $userId, $wxappId,
                $delivery, $payType, $remark, $param, $extractShopId, $linkman, $phone
            ) {
                $goods = self::getCheckoutGoodsList($goodsId, $goodsSkuId, $goodsNum, $activeId, $userId, $wxappId, true);
                if ($goods === false) {
                    throw new \Exception(self::$lastError ?: '下单失败');
                }
                $addrRow = null;
                if ($delivery != 20) {
                    $addrRow = $that->getAddressRow($userId, (int)($param['address_id'] ?? 0));
                    if (empty($addrRow)) {
                        throw new \Exception('很抱歉，请先选择收货地址');
                    }
                }
                $userRow = Db::name('user')->where('user_id', $userId)->find();
                // 会员折扣 + 优惠券/积分分摊（复用普通结算计算）
                $calc = new CheckoutCalc($userRow, $wxappId, [
                    'coupon_id' => (int)($param['coupon_id'] ?? 0),
                    'is_use_points' => (int)($param['is_use_points'] ?? 0),
                ]);
                $goodsList = [$goods];
                $calc->setOrderGoodsGradeMoney($goodsList);
                $discount = $calc->calcDiscount($goodsList);
                $goods = $goodsList[0];
                $expressPrice = '0.00';
                $orderPrice = (string)$goods['total_pay_price'];
                $payPrice = bcadd($orderPrice, $expressPrice, 2);
                $now = time();
                $orderNo = 'PSH' . date('YmdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
                $usePoints = $discount['is_allow_points'] && $discount['is_use_points'];
                $orderId = Db::name('sharing_order')->insertGetId([
                    'order_no' => $orderNo,
                    'total_price' => $discount['order_total_price'],
                    'order_price' => $orderPrice,
                    'order_type' => $activeId > 0 ? 20 : 10,
                    'active_id' => $activeId,
                    'coupon_id' => $discount['coupon_id'],
                    'coupon_money' => $discount['coupon_money'],
                    'points_money' => $discount['points_money'],
                    'points_num' => $usePoints ? (int)$discount['points_num'] : 0,
                    'pay_price' => $payPrice,
                    'update_price' => '0.00',
                    'buyer_remark' => $remark,
                    'pay_type' => $payType,
                    'pay_status' => 10,
                    'pay_time' => 0,
                    'delivery_type' => $delivery,
                    'extract_shop_id' => $extractShopId,
                    'extract_clerk_id' => 0,
                    'express_price' => $expressPrice,
                    'express_id' => 0,
                    'express_company' => '',
                    'express_no' => '',
                    'delivery_status' => 10,
                    'delivery_time' => 0,
                    'receipt_status' => 10,
                    'receipt_time' => 0,
                    'order_status' => 10,
                    'points_bonus' => $discount['points_bonus'],
                    'is_settled' => 0,
                    'is_refund' => 0,
                    'transaction_id' => '',
                    'is_comment' => 0,
                    'user_id' => $userId,
                    'is_delete' => 0,
                    'wxapp_id' => $wxappId,
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
                Db::name('sharing_order_goods')->insert([
                    'goods_id' => $goods['goods_id'],
                    'goods_name' => $goods['goods_name'],
                    'image_id' => $goods['image_id'],
                    'selling_point' => $goods['selling_point'],
                    'people' => $goods['people'],
                    'group_time' => $goods['group_time'],
                    'is_alone' => $goods['is_alone'],
                    'deduct_stock_type' => $goods['deduct_stock_type'],
                    'spec_type' => $goods['spec_type'],
                    'spec_sku_id' => $goods['spec_sku_id'],
                    'goods_sku_id' => $goods['goods_sku_id'],
                    'goods_attr' => '',
                    'content' => '',
                    'goods_no' => $goods['goods_no'],
                    'goods_price' => $goods['goods_price'],
                    'line_price' => $goods['line_price'],
                    'goods_weight' => $goods['goods_weight'],
                    'is_user_grade' => !empty($goods['is_user_grade']) ? 1 : 0,
                    'grade_ratio' => 0,
                    'grade_goods_price' => $goods['goods_price'],
                    'grade_total_money' => '0.00',
                    'coupon_money' => $goods['coupon_money'],
                    'points_money' => $usePoints ? $goods['points_money'] : '0.00',
                    'points_num' => $usePoints ? (int)$goods['points_num'] : 0,
                    'points_bonus' => (int)$goods['points_bonus'],
                    'total_num' => $goods['total_num'],
                    'total_price' => $goods['total_price'],
                    'total_pay_price' => $goods['total_pay_price'],
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'wxapp_id' => $wxappId,
                    'create_time' => $now,
                ]);
                if ($delivery == 20) {
                    Db::name('order_extract')->insert([
                        'order_id' => $orderId, 'linkman' => $linkman, 'phone' => $phone,
                        'user_id' => $userId, 'wxapp_id' => $wxappId, 'create_time' => $now,
                    ]);
                }
                Db::name('sharing_goods_sku')->where('goods_sku_id', $goods['goods_sku_id'])
                    ->dec('stock_num', $goodsNum)->update();
                Db::name('sharing_goods')->where('goods_id', $goodsId)
                    ->inc('sales_actual', $goodsNum)->update();
                if ($discount['coupon_id'] > 0) {
                    Db::name('user_coupon')->where('user_coupon_id', $discount['coupon_id'])
                        ->update(['is_use' => 1, 'use_time' => $now]);
                }
                if ($usePoints && $discount['points_num'] > 0) {
                    \app\api\model\User::changePoints($userId, -(int)$discount['points_num'], '拼团消费：' . $orderNo, $wxappId);
                }
                return $orderId;
            });
            return $orderId;
        } catch (\Exception $e) {
            return self::setError($e->getMessage());
        }
    }

    public function getAddressRow($userId, $addressId)
    {
        $row = null;
        if ($addressId > 0) {
            $row = Db::name('user_address')->where('user_id', $userId)->where('address_id', $addressId)->find();
        }
        if (empty($row)) {
            $defaultId = Db::name('user_address')->where('user_id', $userId)
                ->where('is_default', 1)->value('address_id');
            if ($defaultId) {
                $row = Db::name('user_address')->where('address_id', $defaultId)->find();
            }
        }
        if (empty($row)) {
            $row = Db::name('user_address')->where('user_id', $userId)->order('address_id', 'desc')->find();
        }
        return $row;
    }

    /**
     * 我的拼团订单列表
     */
    public static function getList($userId, $dataType, $wxappId)
    {
        $query = Db::name('sharing_order')->where('user_id', $userId)
            ->where('is_delete', 0)->where('wxapp_id', $wxappId);
        switch ($dataType) {
            case 'pay': $query->where('pay_status', 10)->where('order_status', 10); break;
            case 'delivery': $query->where('pay_status', 20)->where('delivery_status', 10)->where('receipt_status', 10); break;
            case 'receipt': $query->where('pay_status', 20)->where('receipt_status', 10)->where('delivery_status', 20); break;
            case 'complete': $query->where('order_status', 30); break;
        }
        $rows = $query->order('create_time', 'desc')->limit(50)->select()->toArray();
        $list = [];
        foreach ($rows as $row) {
            $list[] = self::formatOrder($row);
        }
        return $list;
    }

    /**
     * 订单详情（校验归属）
     */
    public static function getUserOrderDetail($orderId, $userId, $wxappId)
    {
        $order = Db::name('sharing_order')->where('order_id', $orderId)
            ->where('user_id', $userId)->where('is_delete', 0)
            ->where('wxapp_id', $wxappId)->find();
        return $order ? self::formatOrder($order, true) : null;
    }

    public static function formatOrder($order, $isDetail = false)
    {
        $goodsRows = Db::name('sharing_order_goods')->where('order_id', $order['order_id'])->select()->toArray();
        $goodsList = [];
        foreach ($goodsRows as $g) {
            $goodsList[] = [
                'order_goods_id' => (int)$g['order_goods_id'],
                'goods_id' => (int)$g['goods_id'],
                'goods_name' => $g['goods_name'],
                'image' => BaseModel::getFileUrl($g['image_id']),
                'goods_attr' => $g['goods_attr'] ?: '',
                'goods_price' => $g['goods_price'],
                'total_num' => (int)$g['total_num'],
                'total_price' => $g['total_price'],
                'total_pay_price' => $g['total_pay_price'],
                'people' => (int)$g['people'],
            ];
        }
        $stateMap = [10 => '进行中', 20 => '已取消', 30 => '已完成'];
        $data = [
            'order_id' => (int)$order['order_id'],
            'order_no' => $order['order_no'],
            'order_type' => (int)$order['order_type'],
            'active_id' => (int)$order['active_id'],
            'goods' => $goodsList,
            'total_price' => $order['total_price'],
            'order_price' => $order['order_price'],
            'coupon_money' => $order['coupon_money'],
            'points_money' => $order['points_money'],
            'points_num' => (int)$order['points_num'],
            'pay_price' => $order['pay_price'],
            'express_price' => $order['express_price'],
            'pay_type' => (int)$order['pay_type'],
            'pay_status' => (int)$order['pay_status'],
            'delivery_status' => (int)$order['delivery_status'],
            'receipt_status' => (int)$order['receipt_status'],
            'order_status' => (int)$order['order_status'],
            'delivery_type' => (int)$order['delivery_type'],
            'express_no' => $order['express_no'],
            'express_company' => $order['express_company'],
            'is_comment' => (int)$order['is_comment'],
            'is_refund' => (int)$order['is_refund'],
            'create_time' => date('Y-m-d H:i:s', $order['create_time']),
            'state_text' => $stateMap[$order['order_status']] ?? '',
        ];
        if ($order['active_id'] > 0) {
            $active = Db::name('sharing_active')->where('active_id', $order['active_id'])->find();
            $data['active'] = $active ? [
                'active_id' => (int)$active['active_id'],
                'people' => (int)$active['people'],
                'actual_people' => (int)$active['actual_people'],
                'status' => (int)$active['status'],
                'end_time_text' => $active['end_time'] > 0 ? date('Y-m-d H:i', $active['end_time']) : '',
            ] : null;
        }
        if ($isDetail && (int)$order['delivery_type'] == 20) {
            $extract = Db::name('order_extract')->where('order_id', $order['order_id'])->find();
            $shop = $order['extract_shop_id'] > 0
                ? Db::name('store_shop')->where('shop_id', $order['extract_shop_id'])->find() : [];
            $data['extract'] = [
                'code' => $order['order_no'],
                'linkman' => $extract['linkman'] ?? '',
                'phone' => $extract['phone'] ?? '',
                'shop' => $shop ? [
                    'shop_name' => $shop['shop_name'], 'address' => $shop['address'],
                    'phone' => $shop['phone'], 'linkman' => $shop['linkman'],
                ] : null,
            ];
        }
        return $data;
    }

    /**
     * 取消订单（仅待付款）：状态、库存、优惠券、积分回退
     */
    public function cancel($order, $wxappId)
    {
        if ((int)$order['pay_status'] != 10) {
            return self::setError('该订单不可取消');
        }
        try {
            Db::transaction(function () use ($order, $wxappId) {
                Db::name('sharing_order')->where('order_id', $order['order_id'])
                    ->update(['order_status' => 20, 'update_time' => time()]);
                foreach (Db::name('sharing_order_goods')->where('order_id', $order['order_id'])->select()->toArray() as $g) {
                    if ((int)$g['deduct_stock_type'] == 20) {
                        Db::name('sharing_goods_sku')->where('goods_sku_id', $g['goods_sku_id'])
                            ->inc('stock_num', (int)$g['total_num'])->update();
                        Db::name('sharing_goods')->where('goods_id', $g['goods_id'])
                            ->dec('sales_actual', (int)$g['total_num'])->update();
                    }
                }
                if ($order['coupon_id'] > 0) {
                    Db::name('user_coupon')->where('user_coupon_id', $order['coupon_id'])
                        ->update(['is_use' => 0, 'use_time' => null]);
                }
                if ((int)$order['points_num'] > 0) {
                    \app\api\model\User::changePoints((int)$order['user_id'], (int)$order['points_num'], '拼团取消退还：' . $order['order_no'], $wxappId);
                }
            });
            return true;
        } catch (\Exception $e) {
            return self::setError('订单取消失败');
        }
    }

    /**
     * 确认收货
     */
    public function receipt($order)
    {
        if ((int)$order['pay_status'] != 20 || (int)$order['delivery_status'] != 20) {
            return self::setError('该订单未发货，无法确认收货');
        }
        Db::name('sharing_order')->where('order_id', $order['order_id'])->update([
            'receipt_status' => 20,
            'receipt_time' => time(),
            'order_status' => 30,
            'update_time' => time(),
        ]);
        return true;
    }

    /**
     * 支付成功：开团/入团 + 成团判断（余额同步支付或第三方回调调用）
     */
    public function onPaySuccess($orderNo, $payType, $transactionId = '')
    {
        $order = Db::name('sharing_order')->where('order_no', $orderNo)->find();
        if (empty($order)) {
            return self::setError('订单不存在');
        }
        if ((int)$order['pay_status'] == 20) {
            return true;
        }
        $active = null;
        Db::startTrans();
        try {
            $now = time();
            Db::name('sharing_order')->where('order_id', $order['order_id'])->update([
                'pay_status' => 20, 'pay_time' => $now,
                'transaction_id' => (string)$transactionId, 'update_time' => $now,
            ]);
            if ((int)$order['order_type'] == 10) {
                $goods = Db::name('sharing_order_goods')->where('order_id', $order['order_id'])->find();
                $activeId = Db::name('sharing_active')->insertGetId([
                    'goods_id' => $goods['goods_id'],
                    'people' => $goods['people'],
                    'actual_people' => 1,
                    'creator_id' => $order['user_id'],
                    'end_time' => (int)$goods['group_time'] > 0 ? $now + (int)$goods['group_time'] * 3600 : 0,
                    'status' => 10,
                    'wxapp_id' => $order['wxapp_id'],
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
                Db::name('sharing_order')->where('order_id', $order['order_id'])
                    ->update(['active_id' => $activeId, 'update_time' => $now]);
            } else {
                Db::name('sharing_active')->where('active_id', $order['active_id'])
                    ->inc('actual_people', 1)->update(['update_time' => $now]);
            }
            $activeId = Db::name('sharing_order')->where('order_id', $order['order_id'])->value('active_id');
            $active = Db::name('sharing_active')->where('active_id', $activeId)->find();
            if ($active && (int)$active['actual_people'] >= (int)$active['people']) {
                Db::name('sharing_active')->where('active_id', $active['active_id'])
                    ->update(['status' => 20, 'update_time' => $now]);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return self::setError('支付确认失败：' . $e->getMessage());
        }
    }
}
