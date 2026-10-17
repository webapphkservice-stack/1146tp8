<?php

namespace app\api\service\sharp;

use app\api\model\UserAddress as UserAddressModel;
use app\common\enum\order\OrderSource;
use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 整点秒杀-下单服务（迁移自 php_my api/controller/sharp/Order + api/service/sharp/Order）
 *
 * 设计说明（对齐 TP8「轻量 + Db 直查」风格，不复用购物车 Checkout 的 source 扩展机制）：
 *  - 秒杀商品库存独立存储在 sharp_goods.seckill_stock / sharp_goods_sku.seckill_stock，
 *    与普通商品库存（goods_sku.stock_num）隔离，故建单扣减走 seckill_stock。
 *  - 订单主表 order_source = OrderSource::SHARP(30)，order_source_id = active_time_id，
 *    用于支付成功 / 取消时按来源分派（见 app/api/service/sharp/PaySuccess）。
 *  - 秒杀不参与优惠券 / 积分 / 会员等级折扣。
 *  - 并发控制：建单事务内对 sharp_goods_sku 加悲观行锁（lock(true)）重新校验并扣减，
 *    替代 php_my 的 app/common/library/Lock（TP8 未迁移该库）。
 *
 * 注意：thinkphp 的 Db::transaction 闭包内不可用 $this（普通闭包不绑定 $this），
 * 故所有可在事务内复用的方法均设计为 static，错误经 self::$lastError 传递。
 */
class Order
{
    // 减库存方式（与 goods.deduct_stock_type 同义）
    const DEDUCT_CREATE = 10; // 下单减库存
    const DEDUCT_PAY    = 20; // 付款减库存

    /** @var string 错误信息（请求级） */
    private static $lastError = '';

    public static function getError()
    {
        return self::$lastError;
    }

    private static function setError($msg)
    {
        self::$lastError = $msg;
    }

    /**
     * 获取某用户在某秒杀商品上「已下单且未取消」的累计件数
     * @param int $userId
     * @param int $goodsId 主商品 id
     * @return float|int
     */
    public static function getAlreadyBuyNum($userId, $goodsId)
    {
        return (float)Db::name('order')
            ->alias('o')
            ->join('order_goods og', 'og.order_id = o.order_id')
            ->where('og.user_id', '=', $userId)
            ->where('og.goods_id', '=', $goodsId)
            ->where('o.order_source', '=', OrderSource::SHARP)
            ->where('o.order_status', '<>', 20) // 已取消不计
            ->where('o.is_delete', '=', 0)
            ->sum('og.total_num');
    }

    /**
     * 构建秒杀结算商品列表 + 校验（活动关联 / 下架 / 库存 / 限购）
     * @param int    $activeTimeId
     * @param int    $sharpGoodsId
     * @param string $goodsSkuId
     * @param int    $goodsNum
     * @param int    $userId
     * @param int    $wxappId
     * @param bool   $lock 是否对 sharp_goods_sku 加悲观行锁（建单事务内传 true）
     * @return array|false 商品数组；校验失败返回 false（self::$lastError 已设置）
     */
    public static function getCheckoutGoodsList(
        $activeTimeId,
        $sharpGoodsId,
        $goodsSkuId,
        $goodsNum,
        $userId,
        $wxappId,
        $lock = false
    ) {
        self::$lastError = '';
        $activeTimeId = (int)$activeTimeId;
        $sharpGoodsId = (int)$sharpGoodsId;
        $goodsSkuId   = (string)$goodsSkuId;
        $goodsNum     = max(1, (int)$goodsNum);

        // 1. 活动商品关联
        $activeGoods = Db::name('sharp_active_goods')
            ->where('active_time_id', '=', $activeTimeId)
            ->where('sharp_goods_id', '=', $sharpGoodsId)
            ->where('wxapp_id', '=', $wxappId)
            ->find();
        if (empty($activeGoods)) {
            self::setError('很抱歉，该秒杀活动不存在或已结束');
            return false;
        }
        // 2. 秒杀商品（下架校验）
        $sharpGoods = Db::name('sharp_goods')
            ->where('sharp_goods_id', '=', $sharpGoodsId)
            ->where('status', '=', 10)
            ->where('is_delete', '=', 0)
            ->find();
        if (empty($sharpGoods)) {
            self::setError('很抱歉，该秒杀商品已下架');
            return false;
        }
        // 3. 主商品
        $goods = Db::name('goods')
            ->where('goods_id', '=', $sharpGoods['goods_id'])
            ->where('goods_status', '=', 10)
            ->where('is_delete', '=', 0)
            ->field('goods_id,goods_name,goods_type,spec_type')
            ->find();
        if (empty($goods)) {
            self::setError('很抱歉，商品不存在或已下架');
            return false;
        }
        // 4. 秒杀 SKU（可加锁）
        $skuQuery = Db::name('sharp_goods_sku')
            ->where('sharp_goods_id', '=', $sharpGoodsId)
            ->where('spec_sku_id', '=', $goodsSkuId);
        if ($lock) {
            $skuQuery->lock(true);
        }
        $sku = $skuQuery->find();
        if (empty($sku)) {
            self::setError('很抱歉，该商品规格不存在');
            return false;
        }
        // 5. 库存校验
        if ($goodsNum > (int)$sku['seckill_stock']) {
            self::setError('很抱歉，商品「' . $goods['goods_name'] . '」库存不足');
            return false;
        }
        // 6. 限购校验
        if (!empty($sharpGoods['limit_num']) && $sharpGoods['limit_num'] > 0) {
            $already = self::getAlreadyBuyNum($userId, $sharpGoods['goods_id']);
            if ($already >= $sharpGoods['limit_num']) {
                self::setError('很抱歉，该商品限购' . $sharpGoods['limit_num']
                    . '件，您已下单' . $already . '件，无法再购买');
                return false;
            }
            if (($already + $goodsNum) > $sharpGoods['limit_num']) {
                self::setError('很抱歉，该商品限购' . $sharpGoods['limit_num']
                    . '件，您最多还能购买' . ($sharpGoods['limit_num'] - $already) . '件');
                return false;
            }
        }
        // 7. 主商品 SKU（取 goods_no / line_price / goods_weight / image_id）
        $mainSku = Db::name('goods_sku')
            ->where('goods_id', '=', $sharpGoods['goods_id'])
            ->where('spec_sku_id', '=', $goodsSkuId)
            ->field('goods_no,line_price,goods_weight,image_id')
            ->find();
        $imageId = !empty($mainSku['image_id'])
            ? (int)$mainSku['image_id']
            : (int)Db::name('goods_image')->where('goods_id', $sharpGoods['goods_id'])
                ->order('id', 'asc')->value('image_id');

        return [
            'active_time_id'    => $activeTimeId,
            'sharp_goods_id'    => $sharpGoodsId,
            'goods_id'          => (int)$sharpGoods['goods_id'],
            'goods_name'        => $goods['goods_name'],
            'image_id'          => $imageId,
            'goods_image'       => BaseModel::getFileUrl($imageId),
            'spec_type'         => (int)($goods['spec_type'] ?? 10),
            'spec_sku_id'       => $goodsSkuId,
            'goods_sku_id'      => (int)$sku['goods_sku_id'],
            'goods_attr'        => self::buildGoodsAttr($goodsSkuId),
            'goods_no'          => $mainSku['goods_no'] ?? '',
            'line_price'         => (float)($mainSku['line_price'] ?? 0),
            'goods_weight'       => (float)($mainSku['goods_weight'] ?? 0),
            'seckill_price'      => (float)$sku['seckill_price'],
            'total_num'          => $goodsNum,
            'total_price'        => bcmul((string)$sku['seckill_price'], (string)$goodsNum, 2),
            'deduct_stock_type'  => (int)$sharpGoods['deduct_stock_type'],
            'limit_num'          => (int)$sharpGoods['limit_num'],
            'seckill_stock'      => (int)$sku['seckill_stock'],
            'goods_type'         => (int)$goods['goods_type'],
        ];
    }

    /**
     * 创建秒杀订单，返回 order_id
     * @param array $user    当前登录用户
     * @param int   $wxappId
     * @param array $param   请求参数（active_time_id / sharp_goods_id / goods_sku_id / goods_num / address_id / delivery / pay_type / remark）
     * @return int|false
     */
    public function createOrder($user, $wxappId, $param)
    {
        $activeTimeId = (int)($param['active_time_id'] ?? 0);
        $sharpGoodsId = (int)($param['sharp_goods_id'] ?? 0);
        $goodsSkuId   = (string)($param['goods_sku_id'] ?? '0');
        $goodsNum     = max(1, (int)($param['goods_num'] ?? 1));
        $userId       = (int)$user['user_id'];

        try {
            $orderId = Db::transaction(function () use (
                $activeTimeId, $sharpGoodsId, $goodsSkuId, $goodsNum, $userId, $wxappId, $param
            ) {
                // 事务内悲观锁重新校验（并发安全）
                $goods = self::getCheckoutGoodsList(
                    $activeTimeId, $sharpGoodsId, $goodsSkuId, $goodsNum, $userId, $wxappId, true
                );
                if ($goods === false) {
                    throw new \Exception(self::$lastError ?: '下单失败');
                }
                // 收货地址
                $addrRow = self::getAddressRow($userId, (int)($param['address_id'] ?? 0));
                if (empty($addrRow)) {
                    throw new \Exception('很抱歉，请先选择收货地址');
                }

                $delivery   = (int)($param['delivery'] ?? 10);
                $payType    = (int)($param['pay_type'] ?? 50);
                $remark     = trim($param['remark'] ?? '');
                $expressPrice = '0.00'; // 秒杀免运费（与 Checkout 一致）
                $payPrice   = bcadd($goods['total_price'], $expressPrice, 2);
                $now        = time();
                $orderNo    = self::genOrderNo();

                // 1. 主订单
                $orderId = Db::name('order')->insertGetId([
                    'order_no'        => $orderNo,
                    'total_price'     => $goods['total_price'],
                    'order_price'     => $goods['total_price'],
                    'coupon_id'       => 0,
                    'coupon_money'    => '0.00',
                    'points_money'    => '0.00',
                    'points_num'      => 0,
                    'pay_price'       => $payPrice,
                    'update_price'    => '0.00',
                    'buyer_remark'    => $remark,
                    'pay_type'        => $payType,
                    'pay_status'      => 10,
                    'pay_time'        => 0,
                    'delivery_type'   => $delivery,
                    'goods_type'      => $goods['goods_type'],
                    'virtual_content' => '',
                    'extract_shop_id' => 0,
                    'extract_clerk_id'=> 0,
                    'express_price'   => $expressPrice,
                    'express_id'      => 0,
                    'express_company' => '',
                    'delivery_id'     => 0,
                    'express_no'      => '',
                    'delivery_status' => 10,
                    'delivery_time'   => 0,
                    'receipt_status'  => 10,
                    'receipt_time'    => 0,
                    'order_status'    => 10,
                    'points_bonus'    => 0,
                    'is_settled'      => 0,
                    'transaction_id'  => '',
                    'is_comment'      => 0,
                    'order_source'    => OrderSource::SHARP,
                    'order_source_id' => $activeTimeId,
                    'user_id'         => $userId,
                    'is_delete'       => 0,
                    'wxapp_id'        => $wxappId,
                    'create_time'     => $now,
                    'update_time'     => $now,
                    'is_vip'          => 0,
                ]);

                // 2. 地址快照
                Db::name('order_address')->insert([
                    'name'        => $addrRow['name'],
                    'phone'       => $addrRow['phone'],
                    'province_id' => $addrRow['province_id'],
                    'city_id'     => $addrRow['city_id'],
                    'region_id'   => $addrRow['region_id'],
                    'district'    => $addrRow['district'],
                    'country'     => $addrRow['country'] ?: 'CN',
                    'state'       => $addrRow['state'],
                    'city'        => $addrRow['city'],
                    'detail'      => $addrRow['detail'],
                    'order_id'    => $orderId,
                    'user_id'     => $userId,
                    'wxapp_id'    => $wxappId,
                    'create_time' => $now,
                ]);

                // 3. 订单商品（goods_source_id 记 sharp_goods_id，供支付成功/取消钩子定位）
                Db::name('order_goods')->insert([
                    'goods_id'          => $goods['goods_id'],
                    'goods_name'        => $goods['goods_name'],
                    'image_id'          => $goods['image_id'],
                    'deduct_stock_type' => $goods['deduct_stock_type'],
                    'spec_type'         => $goods['spec_type'],
                    'spec_sku_id'       => $goods['spec_sku_id'],
                    'goods_sku_id'      => $goods['goods_sku_id'],
                    'goods_attr'        => $goods['goods_attr'],
                    'content'           => '',
                    'goods_no'          => $goods['goods_no'],
                    'goods_price'       => $goods['seckill_price'],
                    'line_price'        => $goods['line_price'],
                    'goods_weight'      => $goods['goods_weight'],
                    'is_user_grade'     => 0,
                    'grade_ratio'       => 0,
                    'grade_goods_price' => 0,
                    'grade_total_money' => 0,
                    'coupon_money'      => '0.00',
                    'points_money'      => '0.00',
                    'points_num'        => 0,
                    'points_bonus'      => 0,
                    'total_num'         => $goods['total_num'],
                    'total_price'       => $goods['total_price'],
                    'total_pay_price'   => $goods['total_price'],
                    'is_ind_dealer'     => 0,
                    'dealer_money_type' => 10,
                    'first_money'       => '0.00',
                    'second_money'      => '0.00',
                    'third_money'       => '0.00',
                    'is_comment'        => 0,
                    'order_id'          => $orderId,
                    'user_id'           => $userId,
                    'goods_source_id'   => $sharpGoodsId,
                    'wxapp_id'          => $wxappId,
                    'create_time'       => $now,
                ]);

                // 4. 下单减库存：扣 seckill_stock
                if ($goods['deduct_stock_type'] == self::DEDUCT_CREATE) {
                    Db::name('sharp_goods')->where('sharp_goods_id', $sharpGoodsId)
                        ->dec('seckill_stock', $goods['total_num'])->update();
                    Db::name('sharp_goods_sku')
                        ->where('sharp_goods_id', $sharpGoodsId)
                        ->where('spec_sku_id', $goodsSkuId)
                        ->dec('seckill_stock', $goods['total_num'])->update();
                }

                return $orderId;
            });
        } catch (\Exception $e) {
            if (self::$lastError === '') {
                self::$lastError = $e->getMessage();
            }
            return false;
        }
        return $orderId;
    }

    /**
     * 取收货地址原始行（指定 > 默认 > 最新一条）
     */
    public static function getAddressRow($userId, $addressId)
    {
        $addrModel = new UserAddressModel;
        $row = null;
        if ($addressId > 0) {
            $row = Db::name('user_address')->where('user_id', $userId)
                ->where('address_id', $addressId)->find();
        }
        if (empty($row)) {
            $defaultId = $addrModel->getDefaultId($userId);
            $row = $defaultId ? Db::name('user_address')->where('user_id', $userId)
                ->where('address_id', $defaultId)->find() : null;
        }
        if (empty($row)) {
            $row = Db::name('user_address')->where('user_id', $userId)
                ->order('address_id', 'desc')->find();
        }
        return $row;
    }

    /**
     * 规格属性文本（规格表为空时返回 ''）
     */
    private static function buildGoodsAttr($specSkuId)
    {
        if (empty($specSkuId) || $specSkuId === '0') {
            return '';
        }
        $ids = array_filter(explode('_', $specSkuId), 'strlen');
        if (empty($ids)) {
            return '';
        }
        $rows = Db::name('spec_value')->alias('sv')
            ->join('spec s', 's.spec_id = sv.spec_id')
            ->whereIn('sv.spec_value_id', $ids)
            ->field('sv.spec_value, s.spec_name')
            ->select()->toArray();
        if (empty($rows)) {
            return '';
        }
        $parts = [];
        foreach ($rows as $r) {
            $parts[] = $r['spec_name'] . ':' . $r['spec_value'];
        }
        return implode(' ', $parts);
    }

    private static function genOrderNo()
    {
        return date('YmdHis') . str_pad(mt_rand(0, 9999), 4, '0');
    }
}
