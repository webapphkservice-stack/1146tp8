<?php
namespace app\api\service\order;

use app\api\model\Cart as CartModel;
use app\api\model\UserAddress as UserAddressModel;
use app\api\model\UserCoupon as UserCouponModel;
use app\api\service\coupon\GoodsDeduct as CouponDeductService;
use app\api\service\points\GoodsDeduct as PointsDeductService;
use app\common\exception\BaseException;
use app\common\library\helper;
use app\common\model\BaseModel;
use app\common\model\Setting as SettingModel;
use app\common\model\user\Grade as GradeModel;
use think\facade\Db;

/**
 * 下单结算服务（对齐 RN CheckoutScreen 契约）
 * - checkout()：结算页数据（GET order/cart、order/buyNow）
 * - createOrder()：提交并创建订单（POST order/cart、order/buyNow）
 *
 * 设计要点（与第 1~3 批一致「轻量 + Db 直查」）：
 *  - 购物车数据沿用缓存方案（见 Cart 模型）
 *  - 规格表（yoshop_spec/spec_value）在本库为空，goods_attr 恒为 ''（规格解析预留）
 *  - 配送方式内置 [快递10/自提20/门店配送30]，运费当前统一 0（免运费）
 *  - 优惠券 / 积分抵扣已接入（P1），与 php_my api/service/order/Checkout 对齐：
 *      · 优惠券：可用券列表 → 适用范围校验 → 按商品金额权重分摊（分为单位）→ 订单抵扣额
 *      · 积　分：商品最大可抵扣积分 → 按占比分摊 → 换算抵扣金额 → 赠送积分
 *      · 分摊统一走 calcDiscount()，结算页与提交建单共用同一套计算，避免两处金额不一致
 *  - 门店配送（送水，delivery=30）：叠加 押桶押金 + 水票抵扣（见 calcWaterDelivery）
 */
class Checkout
{
    /** @var string 错误信息 */
    public $error = '';

    /** @var array 用户信息 */
    private $user;

    /** @var int wxapp_id */
    private $wxappId;

    /** @var array 请求参数 */
    private $param;

    public function __construct($user, $wxappId, $param = [])
    {
        $this->user = $user;
        $this->wxappId = $wxappId;
        $this->param = $param;
    }

    /**
     * 结算页数据
     * @param string $orderType cart / buyNow
     * @return array|false
     */
    public function checkout($orderType)
    {
        $goodsList = $this->getOrderGoodsList($orderType);
        if ($goodsList === []) {
            return false; // error 已设置
        }
        // 会员等级折扣（在优惠券/积分分摊前应用，直接影响 total_price）
        $this->setOrderGoodsGradeMoney($goodsList);
        // 商品总数量
        $orderTotalNum = 0;
        foreach ($goodsList as $item) {
            $orderTotalNum += $item['total_num'];
        }
        // 优惠券 / 积分抵扣（会写入 goodsList 各商品的分摊字段）
        $discount = $this->calcDiscount($goodsList);
        $orderTotalPrice = $discount['order_total_price']; // 优惠前商品总额
        $orderPrice = $discount['order_price'];            // 优惠后商品总额

        // 收货地址（指定 address_id 或默认）
        $addressId = isset($this->param['address_id']) ? (int)$this->param['address_id'] : 0;
        $address = $this->getAddress($addressId);
        $existAddress = !empty($address);
        // 配送方式（默认快递）
        $delivery = isset($this->param['delivery']) ? (int)$this->param['delivery'] : 10;
        // 送水：门店配送水票抵扣 + 押桶押金（会重算 order_price 并挂载 deposit_amount）
        $water = $this->calcWaterDelivery($goodsList, $discount, $delivery);
        if ($water === false) {
            return false; // error 已设置
        }
        $orderPrice = $discount['order_price']; // calcWaterDelivery 可能已用水票摊薄
        $expressPrice = $this->getExpressPrice($delivery);
        $intraRegion = true;
        // 应付金额 = 优惠后商品总额(已减水票) + 运费 + 押桶押金
        $orderPayPrice = bcadd(bcadd($orderPrice, $expressPrice, 2), $water['deposit_amount'], 2);
        // 是否虚拟商品（全部为虚拟品才算）
        $isVirtual = true;
        foreach ($goodsList as $item) {
            if (($item['goods_type'] ?? 10) != 20) {
                $isVirtual = false;
                break;
            }
        }
        // 默认支付类型（Stripe = 50，RN 当前唯一可用）
        $payType = 50;
        $setting = [
            'delivery' => [
                ['value' => 10, 'name' => '快递配送'],
                ['value' => 20, 'name' => '上门自提'],
                ['value' => 30, 'name' => '门店配送'],
            ],
            'points_name' => $discount['points_name'],
        ];

        // 自提信息：选中的自提点 + 记忆的联系方式
        $extractShop = [];
        $lastExtract = null;
        if ($delivery == 20) {
            $shopId = isset($this->param['shop_id']) ? (int)$this->param['shop_id'] : 0;
            if ($shopId > 0) {
                $extractShop = \app\api\model\store\Shop::detail($shopId);
            }
            $last = Db::name('order_extract')->where('user_id', $this->user['user_id'])
                ->order('id', 'desc')->find();
            if ($last) {
                $lastExtract = ['linkman' => $last['linkman'], 'phone' => $last['phone']];
            }
        }

        return [
            'goods_list'        => $goodsList,
            'order_total_num'   => $orderTotalNum,
            'order_total_price' => $orderTotalPrice,
            'order_price'       => $orderPrice,
            'order_pay_price'   => $orderPayPrice,
            'order_total_price_no' => $orderTotalPrice,
            'coupon_list'       => $discount['coupon_list'],
            'coupon_id'         => $discount['coupon_id'],
            'coupon_money'      => $discount['coupon_money'],
            'is_allow_points'   => $discount['is_allow_points'],
            'is_use_points'     => $discount['is_use_points'],
            'points_money'      => $discount['points_money'],
            'points_num'        => $discount['points_num'],
            'points_bonus'      => $discount['points_bonus'],
            'setting'           => $setting,
            'address'           => $address,
            'exist_address'     => $existAddress,
            'delivery'          => $delivery,
            'intra_region'      => $intraRegion,
            'express_price'     => $expressPrice,
            'extract_shop'      => $extractShop,
            'last_extract'      => $lastExtract,
            'is_virtual'        => $isVirtual,
            'has_error'         => $this->error !== '',
            'error_msg'         => $this->error,
            'pay_type'          => $payType,
            // 送水（门店配送）对账/展示字段
            'delivery_shop_id'   => $water['delivery_shop_id'],
            'delivery_shop_name' => $water['delivery_shop_name'],
            'water_ticket_num'   => $water['water_ticket_num'],
            'water_ticket_money' => $water['water_ticket_money'],
            'deposit_amount'     => $water['deposit_amount'],
            'bucket_num'         => $water['bucket_num'],
        ];
    }

    /**
     * 创建订单，返回 order_id
     * @param string $orderType cart / buyNow
     * @return int|false
     */
    public function createOrder($orderType)
    {
        // 复用结算商品列表（再次校验库存）
        $goodsList = $this->getOrderGoodsList($orderType);
        if ($goodsList === []) {
            return false;
        }
        // 会员等级折扣（与结算页共用同一套计算，保证金额一致）
        $this->setOrderGoodsGradeMoney($goodsList);
        // 商品总数量
        $orderTotalNum = 0;
        foreach ($goodsList as $item) {
            $orderTotalNum += $item['total_num'];
        }
        // 优惠券 / 积分抵扣（与结算页共用同一套计算，保证金额一致）
        $discount = $this->calcDiscount($goodsList);
        if ($this->error !== '') {
            return false; // 优惠券不合法等
        }
        $orderTotalPrice = $discount['order_total_price']; // 优惠前
        $orderPrice      = $discount['order_price'];      // 优惠后
        $delivery = isset($this->param['delivery']) ? (int)$this->param['delivery'] : 10;
        // 送水：门店配送水票抵扣 + 押桶押金
        $water = $this->calcWaterDelivery($goodsList, $discount, $delivery);
        if ($water === false) {
            return false;
        }
        $orderPrice = $discount['order_price'];
        $expressPrice = $this->getExpressPrice($delivery);
        $orderPayPrice = bcadd(bcadd($orderPrice, $expressPrice, 2), $water['deposit_amount'], 2);
        $payType = isset($this->param['pay_type']) ? (int)$this->param['pay_type'] : 50;
        $couponId = $discount['coupon_id'];
        $remark = isset($this->param['remark']) ? trim($this->param['remark']) : '';
        // 是否实际使用积分抵扣（同时满足：允许抵扣 + 用户勾选 + 抵扣数量 > 0）
        $usePoints = $discount['is_allow_points'] && $discount['is_use_points'] && $discount['points_num'] > 0;
        $pointsNum = $usePoints ? $discount['points_num'] : 0;
        $pointsMoney = $usePoints ? $discount['points_money'] : '0.00';
        $isVirtual = true;
        foreach ($goodsList as $item) {
            if (($item['goods_type'] ?? 10) != 20) {
                $isVirtual = false;
                break;
            }
        }
        $goodsType = $isVirtual ? 20 : 10;

        // 收货地址（自提订单无需收货地址）
        $addressId = isset($this->param['address_id']) ? (int)$this->param['address_id'] : 0;
        $addrRow = $this->getAddressRow($addressId);
        if ($delivery != 20 && empty($addrRow)) {
            $this->error = '很抱歉，请先选择收货地址';
            return false;
        }
        // 自提订单校验：门店 + 联系人/电话
        $extractShopId = 0;
        $extractLinkman = '';
        $extractPhone = '';
        if ($delivery == 20) {
            $extractShopId = isset($this->param['shop_id']) ? (int)$this->param['shop_id'] : 0;
            $extractLinkman = trim($this->param['linkman'] ?? '');
            $extractPhone = trim($this->param['phone'] ?? '');
            if ($extractShopId <= 0) {
                $this->error = '请选择自提门店';
                return false;
            }
            if ($extractLinkman === '' || $extractPhone === '') {
                $this->error = '请填写自提联系人和电话';
                return false;
            }
        }

        $orderNo = $this->genOrderNo();
        $userId = $this->user['user_id'];
        $wxappId = $this->wxappId;
        $now = time();

        $orderId = Db::transaction(function () use (
            $goodsList, $orderTotalNum, $orderTotalPrice, $orderPrice, $expressPrice, $orderPayPrice,
            $delivery, $payType, $couponId, $discount, $usePoints, $pointsNum, $pointsMoney,
            $remark, $goodsType, $addrRow,
            $orderNo, $userId, $wxappId, $now, $orderType,
            $extractShopId, $extractLinkman, $extractPhone, $water
        ) {
            // 1. 主订单
            $orderId = Db::name('order')->insertGetId([
                'order_no'        => $orderNo,
                'total_price'     => $orderTotalPrice, // 优惠前
                'order_price'     => $orderPrice,      // 优惠后
                'coupon_id'       => $couponId,
                'coupon_money'    => $discount['coupon_money'],
                'points_money'    => $pointsMoney,
                'points_num'      => $pointsNum,
                'pay_price'       => $orderPayPrice,
                'update_price'    => '0.00',
                'buyer_remark'    => $remark,
                'pay_type'        => $payType,
                'pay_status'      => 10,
                'pay_time'        => 0,
                'delivery_type'   => $delivery,
                'goods_type'      => $goodsType,
                'virtual_content' => '',
                'extract_shop_id' => $extractShopId,
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
                'points_bonus'    => $discount['points_bonus'],
                'is_settled'      => 0,
                'transaction_id'  => '',
                'is_comment'      => 0,
                'order_source'    => 10,
                'order_source_id' => 0,
                // 送水（门店配送）字段
                'delivery_shop_id'     => $water['delivery_shop_id'],
                'delivery_shop_name'   => $water['delivery_shop_name'],
                'shop_order_status'    => $water['shop_order_status'],
                'deposit_amount'       => $water['deposit_amount'],
                'empty_bucket_num'     => $water['empty_bucket_num'],
                'deposit_refund_status'=> $water['deposit_refund_status'],
                'delivery_clerk_id'    => 0,
                'water_ticket_num'     => $water['water_ticket_num'],
                'water_ticket_money'   => $water['water_ticket_money'],
                'user_id'         => $userId,
                'is_delete'       => 0,
                'wxapp_id'        => $wxappId,
                'create_time'     => $now,
                'update_time'     => $now,
                'is_vip'          => 0,
            ]);

            // 2. 地址快照（自提订单记录联系方式，不写收货地址）
            if ($delivery == 20) {
                Db::name('order_extract')->insert([
                    'order_id'    => $orderId,
                    'linkman'     => $extractLinkman,
                    'phone'       => $extractPhone,
                    'user_id'     => $userId,
                    'wxapp_id'    => $wxappId,
                    'create_time' => $now,
                ]);
            } else {
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
            }

            // 3. 订单商品 + 扣库存/加销量
            foreach ($goodsList as $item) {
                Db::name('order_goods')->insert([
                    'goods_id'          => $item['goods_id'],
                    'goods_name'        => $item['goods_name'],
                    'image_id'          => $item['image_id'],
                    'deduct_stock_type' => $item['deduct_stock_type'],
                    'spec_type'         => $item['spec_type'],
                    'spec_sku_id'       => $item['spec_sku_id'],
                    'goods_sku_id'      => $item['goods_sku_id'],
                    'goods_attr'        => $item['goods_attr'],
                    'content'           => '',
                    'goods_no'          => $item['goods_no'],
                    'goods_price'       => $item['goods_price'],
                    'line_price'        => $item['line_price'],
                    'goods_weight'      => $item['goods_weight'],
                    'is_user_grade'     => $item['is_user_grade'] ? 1 : 0,
                    'grade_ratio'       => $item['grade_ratio'],
                    'grade_goods_price' => $item['grade_goods_price'],
                    'grade_total_money' => $item['grade_total_money'],
                    'coupon_money'      => $item['coupon_money'],
                    'points_money'      => $usePoints ? $item['points_money'] : '0.00',
                    'points_num'        => $usePoints ? (int)$item['points_num'] : 0,
                    'points_bonus'      => (int)$item['points_bonus'],
                    'total_num'         => $item['total_num'],
                    'total_price'       => $item['total_price'],
                    'total_pay_price'   => $item['total_pay_price'],
                    'is_ind_dealer'     => 0,
                    'dealer_money_type' => 10,
                    'first_money'       => '0.00',
                    'second_money'      => '0.00',
                    'third_money'       => '0.00',
                    'is_comment'        => 0,
                    'order_id'          => $orderId,
                    'user_id'           => $userId,
                    'goods_source_id'   => 0,
                    'wxapp_id'          => $wxappId,
                    'create_time'       => $now,
                ]);
                // 扣减 SKU 库存（已校验充足），增加销量
                Db::name('goods_sku')->where('goods_sku_id', $item['goods_sku_id'])
                    ->dec('stock_num', $item['total_num'])->update();
                Db::name('goods')->where('goods_id', $item['goods_id'])
                    ->inc('sales_actual', $item['total_num'])->update();
            }

            // 4. 核销优惠券（与 php_my 一致：下单即核销；取消/超时关闭时由退还逻辑置回未使用）
            if ($couponId > 0) {
                UserCouponModel::setIsUse($couponId, true);
            }
            // 5. 扣除使用的积分并写积分流水
            if ($usePoints && $pointsNum > 0) {
                \app\api\model\User::changePoints(
                    $userId,
                    -$pointsNum,
                    '用户消费：' . $orderNo,
                    $wxappId
                );
            }
            // 5.5 扣减水票（门店配送且用户使用了水票抵扣，场景=下单抵扣）
            if ($water['water_ticket_num'] > 0) {
                if (!\app\common\model\UserWaterTicket::adjust(
                    $userId,
                    -$water['water_ticket_num'],
                    $wxappId,
                    \app\common\model\UserWaterTicketLog::SCENE_ORDER,
                    $orderNo
                )) {
                    throw new \Exception('水票扣减失败');
                }
            }

            // 6. 购物车来源：清空已下单商品
            if ($orderType === 'cart') {
                $cartModel = new CartModel($this->user);
                $cartIds = $this->param['cart_ids'] ?? '';
                $cartModel->delete($cartIds);
            }

            return $orderId;
        });

        return $orderId;
    }

    // ==================== 优惠券 / 积分抵扣（P1） ====================

    /**
     * 统一计算优惠券与积分抵扣
     *
     * 结算页（checkout）与提交建单（createOrder）共用本方法，保证「看到的金额」与
     * 「实际落库的金额」完全一致，避免两处逻辑漂移导致用户被多扣款。
     *
     * @param array $goodsList 商品列表（引用传入，会写入以下字段）
     *                         coupon_money / points_money / points_num / points_bonus / total_pay_price
     * @return array 订单级优惠数据
     */
    public function calcDiscount(&$goodsList)
    {
        // 积分设置
        $pointsSetting = SettingModel::getItem('points');
        $pointsName = !empty($pointsSetting['points_name']) ? $pointsSetting['points_name'] : '积分';

        $result = [
            'order_total_price' => '0.00',   // 优惠前商品总额
            'order_price'       => '0.00',   // 优惠后商品总额
            'coupon_list'       => [],
            'coupon_id'         => 0,
            'coupon_money'      => '0.00',
            'is_allow_points'   => false,
            'is_use_points'     => (int)($this->param['is_use_points'] ?? 0) > 0,
            'points_money'      => '0.00',
            'points_num'        => 0,
            'points_bonus'      => 0,
            'points_name'       => $pointsName,
        ];

        // 1. 订单商品总金额（优惠前）
        foreach ($goodsList as $item) {
            $result['order_total_price'] = helper::bcadd($result['order_total_price'], $item['total_price']);
        }

        // 2. 当前用户可用优惠券列表（含适用范围校验）
        $couponList = UserCouponModel::getUserCouponList(
            $this->user['user_id'],
            (float)$result['order_total_price']
        );
        $couponList = UserCouponModel::couponListApplyRange(
            $couponList,
            helper::getArrayColumn($goodsList, 'goods_id')
        );
        $result['coupon_list'] = $couponList;

        // 3. 优惠券抵扣（分摊到各商品）
        $this->setOrderCouponMoney($goodsList, $couponList, $result);
        // 4. 积分抵扣（分摊到各商品）
        $this->setOrderPoints($goodsList, $result, $pointsSetting);
        // 5. 各商品实付金额 = 总价 - 优惠券 - 积分
        $this->setOrderGoodsPayPrice($goodsList, $result);
        // 6. 订单积分赠送数量
        $this->setOrderPointsBonus($goodsList, $result, $pointsSetting);

        // 7. 优惠后商品总额
        $orderPrice = '0.00';
        foreach ($goodsList as $item) {
            $orderPrice = helper::bcadd($orderPrice, $item['total_pay_price']);
        }
        $result['order_price'] = $orderPrice;
        return $result;
    }

    /**
     * 优惠券抵扣：校验所选券 → 按商品金额权重分摊
     */
    private function setOrderCouponMoney(&$goodsList, $couponList, &$result)
    {
        helper::setDataAttribute($goodsList, ['coupon_money' => 0], true);
        $couponId = (int)($this->param['coupon_id'] ?? 0);
        if ($couponId <= 0 || empty($couponList)) {
            return false;
        }
        // 校验券是否存在且适用
        $couponInfo = null;
        foreach ($couponList as $item) {
            if ((int)$item['user_coupon_id'] === $couponId) {
                $couponInfo = $item;
                break;
            }
        }
        // 券不存在或不适用的处理与 php_my 一致：直接抛业务异常中断结算，
        // 而不是静默忽略（否则用户会以为券已生效、实际支付原价）。
        if (!$couponInfo) {
            throw new BaseException('未找到优惠券信息');
        }
        if (empty($couponInfo['is_apply'])) {
            throw new BaseException($couponInfo['not_apply_info'] ?? '该优惠券不支持当前商品');
        }
        // 按商品金额权重分摊（内部以「分」为单位，避免四舍五入导致分摊总额对不上券面额）
        $goodsListTemp = helper::getArrayColumns($goodsList, ['total_price']);
        $deduct = new CouponDeductService;
        $completed = $deduct->setGoodsCouponMoney($goodsListTemp, $couponInfo['reduced_price']);
        // 分摊结果（分）转回元，回填到各商品
        foreach ($goodsList as $key => &$goods) {
            $goods['coupon_money'] = helper::bcdiv($completed[$key]['coupon_money'], 100);
        }
        unset($goods);
        $result['coupon_id']    = $couponId;
        $result['coupon_money'] = helper::number2(helper::bcdiv($deduct->getActualReducedMoney(), 100));
        return true;
    }

    /**
     * 积分抵扣：最大可抵扣积分 → 按占比分摊 → 换算金额
     */
    private function setOrderPoints(&$goodsList, &$result, $pointsSetting)
    {
        // 默认各商品积分抵扣信息
        helper::setDataAttribute($goodsList, [
            'max_points_num' => 0,
            'points_num'     => 0,
            'points_money'   => '0.00',
        ], true);

        // 条件一：后台开启下单使用积分抵扣
        if (empty($pointsSetting['is_shopping_discount'])) {
            return false;
        }
        // 条件二：订单金额满足最低门槛
        $fullOrderPrice = $pointsSetting['discount']['full_order_price'] ?? 0;
        if (helper::bccomp($fullOrderPrice, $result['order_total_price']) === 1) {
            return false;
        }
        // 条件三：用户积分 > 0
        $userPoints = (int)($this->user['points'] ?? 0);
        if ($userPoints < 1) {
            return false;
        }

        // 各商品最多可抵扣的积分数量
        $maxRatio = (float)($pointsSetting['discount']['max_money_ratio'] ?? 0) / 100;
        $discountRatio = $pointsSetting['discount']['discount_ratio'] ?? 0;
        foreach ($goodsList as &$goods) {
            if (empty($goods['is_points_discount'])) {
                continue;
            }
            // 最多可抵扣金额 =（商品总价 - 优惠券已抵扣）* 最大抵扣比例
            $totalPayPrice = helper::bcsub($goods['total_price'], $goods['coupon_money']);
            $maxPointsMoney = helper::bcmul($totalPayPrice, $maxRatio);
            $goods['max_points_num'] = (int)helper::bcdiv($maxPointsMoney, $discountRatio, 0);
        }
        unset($goods);

        $maxPointsNumCount = 0;
        foreach ($goodsList as $goods) {
            $maxPointsNumCount += (int)$goods['max_points_num'];
        }
        // 实际可抵扣积分 = min(订单最大可抵扣, 用户持有积分)
        $actualPointsNum = min($maxPointsNumCount, $userPoints);
        if ($actualPointsNum < 1) {
            return false;
        }

        // 按占比分摊到各商品，并换算抵扣金额
        $deduct = new PointsDeductService($goodsList);
        $goodsList = $deduct->setGoodsPoints($maxPointsNumCount, $actualPointsNum);

        $result['points_money']    = helper::number2(helper::getArrayColumnSum($goodsList, 'points_money'));
        $result['points_num']      = $actualPointsNum;
        $result['is_allow_points'] = true;
        return true;
    }

    /**
     * 会员等级折扣计算（B3 会员等级折扣）
     * 依赖：用户 grade_id + 等级默认折扣，或商品单独设置的折扣
     * 计算出的会员价直接改写 total_price，再交由 calcDiscount 参与优惠券/积分分摊
     */
    public function setOrderGoodsGradeMoney(&$goodsList)
    {
        // 默认数据
        helper::setDataAttribute($goodsList, [
            'is_user_grade'     => false,
            'grade_ratio'       => 0,
            'grade_goods_price' => 0.00,
            'grade_total_money' => 0.00,
        ], true);

        // 用户未设置会员等级则不参与折扣
        $gradeId = (int)($this->user['grade_id'] ?? 0);
        if ($gradeId <= 0) {
            return true;
        }
        // 取用户等级默认折扣率（0-10，如 9.5=9.5折）
        $defaultRatio = GradeModel::getDiscountRatio($gradeId);
        if ($defaultRatio <= 0) {
            return true;
        }

        foreach ($goodsList as &$goods) {
            // 商品未开启会员折扣
            if (empty($goods['is_enable_grade'])) {
                continue;
            }
            // 商品单独设置了会员折扣（按等级单独定价）
            if (!empty($goods['is_alone_grade']) && isset($goods['alone_grade_equity'][$gradeId])) {
                $ratioTen = (float)$goods['alone_grade_equity'][$gradeId];
            } else {
                $ratioTen = $defaultRatio;
            }
            // 计算用比例（0-1）；grade_ratio 按 schema 语义存 0-10 制（9=9折、9.5=9.5折），供后台/前端读取展示
            $discountRatio = helper::bcdiv($ratioTen, 10);
            if ($discountRatio > 0) {
                $gradeTotalPrice = max(0.01, (float)bcmul($goods['total_price'], $discountRatio, 2));
                helper::setDataAttribute($goods, [
                    'is_user_grade'     => true,
                    'grade_ratio'       => $ratioTen,
                    'grade_goods_price' => helper::number2(bcmul($goods['goods_price'], $discountRatio, 2)),
                    'grade_total_money' => helper::number2(bcsub((string)$goods['total_price'], (string)$gradeTotalPrice, 2)),
                    'total_price'       => $gradeTotalPrice,
                ], false);
            }
        }
        unset($goods);
        return true;
    }

    /**
     * 各商品实付金额 = 商品总价 - 优惠券抵扣 - 积分抵扣
     */
    private function setOrderGoodsPayPrice(&$goodsList, $result)
    {
        $usePoints = $result['is_allow_points'] && $result['is_use_points'];
        foreach ($goodsList as &$goods) {
            $value = helper::bcsub($goods['total_price'], $goods['coupon_money']);
            if ($usePoints) {
                $value = helper::bcsub($value, $goods['points_money']);
            }
            // 兜底：实付不得低于 0
            if (helper::bccomp($value, '0') === -1) {
                $value = '0.00';
            }
            $goods['total_pay_price'] = helper::number2($value);
        }
        unset($goods);
    }

    /**
     * 门店配送（送水）计算：押桶押金 + 水票抵扣
     *
     * 仅在 delivery=30（门店配送）时生效；其他配送方式返回全 0（不影响原逻辑）。
     * - 桶数 bucketNum = Σ(total_num)，仅 is_deposit_payable=1 的桶装水商品计入
     * - 押金 deposit_amount = 门店单桶押金 deposit_amount * 桶数（门店未开启 deposit_enabled 则为 0）
     * - 水票抵扣：用户选择 water_ticket_num 张，实际用票 = min(选择, 余额, 桶数)；
     *   抵扣金额按桶装水「原价每桶均价」计算，并分摊到各桶装水商品 total_pay_price，
     *   再重算 order_price（与结算页/建单共用，金额一致）
     *
     * @param array $goodsList 商品列表（引用；水票抵扣会改写 total_pay_price）
     * @param array $result    calcDiscount 结果（引用；水票抵扣会重算 order_price）
     * @param int   $delivery  配送方式
     * @return array|false 送水信息；校验失败返回 false（error 已设置）
     */
    private function calcWaterDelivery(&$goodsList, &$result, $delivery)
    {
        $info = [
            'delivery_shop_id'      => 0,
            'delivery_shop_name'    => '',
            'shop_order_status'     => 10,
            'deposit_amount'        => '0.00',
            'water_ticket_num'      => 0,
            'water_ticket_money'    => '0.00',
            'bucket_num'            => 0,
            'empty_bucket_num'      => 0,
            'deposit_refund_status' => 0,
        ];
        if ($delivery != 30) {
            return $info;
        }
        $shopId = (int)($this->param['shop_id'] ?? 0);
        if ($shopId <= 0) {
            $this->error = '请选择派送门店';
            return false;
        }
        $shop = Db::name('store_shop')->where('shop_id', $shopId)
            ->where('wxapp_id', $this->wxappId)->find();
        if (empty($shop) || empty($shop['delivery_enabled'])) {
            $this->error = '该门店不支持配送';
            return false;
        }
        $info['delivery_shop_id'] = $shopId;
        $info['delivery_shop_name'] = $shop['shop_name'] ?? '';

        // 桶装水桶数 + 原价合计（仅 is_deposit_payable=1）
        $bucketNum = 0;
        $bucketPriceTotal = '0.00';
        foreach ($goodsList as $g) {
            if (!empty($g['is_deposit_payable'])) {
                $bucketNum += (int)$g['total_num'];
                $bucketPriceTotal = helper::bcadd($bucketPriceTotal, $g['total_price']);
            }
        }
        $info['bucket_num'] = $bucketNum;

        // 押桶押金：单桶押金 * 桶数
        if (!empty($shop['deposit_enabled']) && $bucketNum > 0 && !empty($shop['deposit_amount'])) {
            $info['deposit_amount'] = helper::number2(
                bcmul((string)$shop['deposit_amount'], (string)$bucketNum, 2)
            );
        }

        // 水票抵扣：门店开启水票且用户选择票数
        if (!empty($shop['water_ticket_enabled']) && $bucketNum > 0) {
            $want = (int)($this->param['water_ticket_num'] ?? 0);
            if ($want > 0) {
                $balance = \app\common\model\UserWaterTicket::getBalance(
                    $this->user['user_id'], $this->wxappId);
                $actual = min($want, $balance, $bucketNum);
                if ($actual > 0) {
                    // 每桶均价（桶装水原价合计 / 桶数）
                    $avg = $bucketNum > 0 ? helper::bcdiv($bucketPriceTotal, (string)$bucketNum, 2) : '0.00';
                    $money = helper::number2(bcmul($avg, (string)$actual, 2));
                    // 抵扣金额不得超过当前优惠后商品总额
                    if (helper::bccomp($money, $result['order_price']) === 1) {
                        $money = $result['order_price'];
                    }
                    $info['water_ticket_num'] = $actual;
                    $info['water_ticket_money'] = $money;
                    // 把水票金额按桶数权重摊到桶装水商品 total_pay_price，并重算 order_price
                    $this->spreadWaterTicket($goodsList, $money, $actual);
                    $orderPrice = '0.00';
                    foreach ($goodsList as $g) {
                        $orderPrice = helper::bcadd($orderPrice, $g['total_pay_price']);
                    }
                    $result['order_price'] = $orderPrice;
                }
            }
        }
        return $info;
    }

    /**
     * 将水票抵扣金额按桶装水桶数权重分摊到各商品 total_pay_price（分单位避免误差）
     * @param array  $goodsList 商品列表（引用）
     * @param string $money     水票抵扣总额（元）
     * @param int    $ticketNum 实际使用水票数（= 桶数）
     */
    private function spreadWaterTicket(&$goodsList, $money, $ticketNum)
    {
        $buckets = [];
        $totalBucket = 0;
        foreach ($goodsList as $k => $g) {
            if (!empty($g['is_deposit_payable'])) {
                $buckets[$k] = (int)$g['total_num'];
                $totalBucket += (int)$g['total_num'];
            }
        }
        if ($totalBucket <= 0) {
            return;
        }
        $moneyFen = (int)round((float)$money * 100);
        $assigned = 0;
        $keys = array_keys($buckets);
        $count = count($keys);
        foreach ($keys as $i => $k) {
            if ($i === $count - 1) {
                $part = $moneyFen - $assigned; // 尾差归最后一项
            } else {
                $part = (int)floor($moneyFen * $buckets[$k] / $totalBucket);
                $assigned += $part;
            }
            if ($part <= 0) {
                continue;
            }
            $new = bcsub($goodsList[$k]['total_pay_price'], bcdiv((string)$part, '100', 2), 2);
            if (helper::bccomp($new, '0') === -1) {
                $new = '0.00';
            }
            $goodsList[$k]['total_pay_price'] = helper::number2($new);
        }
    }

    /**
     * 订单积分赠送数量（下单送积分，收货后到账；此处仅计算数量）
     */
    private function setOrderPointsBonus(&$goodsList, &$result, $pointsSetting)
    {
        helper::setDataAttribute($goodsList, ['points_bonus' => 0], true);
        if (empty($pointsSetting['is_shopping_gift'])) {
            return false;
        }
        $ratio = (float)($pointsSetting['gift_ratio'] ?? 0) / 100;
        foreach ($goodsList as &$goods) {
            $goods['points_bonus'] = empty($goods['is_points_gift'])
                ? 0
                : (int)helper::bcmul($goods['total_pay_price'], $ratio, 0);
        }
        unset($goods);
        $result['points_bonus'] = (int)helper::getArrayColumnSum($goodsList, 'points_bonus');
        return true;
    }

    /**
     * 组装订单商品列表（cart / buyNow 通用）
     * @return array 商品列表；校验失败时返回 [] 且 $this->error 已设置
     */
    private function getOrderGoodsList($orderType)
    {
        if ($orderType === 'buyNow') {
            $items = [[
                'goods_id'     => (int)($this->param['goods_id'] ?? 0),
                'goods_num'    => max(1, (int)($this->param['goods_num'] ?? 1)),
                'goods_sku_id' => (string)($this->param['goods_sku_id'] ?? '0'),
            ]];
        } else {
            $cartModel = new CartModel($this->user);
            $cartIds = $this->param['cart_ids'] ?? '';
            $items = $cartModel->getCartList($cartIds);
        }
        if (empty($items)) {
            $this->error = '很抱歉，请选择要结算的商品';
            return [];
        }
        return $this->buildGoodsList($items);
    }

    private function buildGoodsList($items)
    {
        $list = [];
        foreach ($items as $it) {
            $goodsId = (int)$it['goods_id'];
            $goodsNum = max(1, (int)$it['goods_num']);
            $goodsSkuId = (string)$it['goods_sku_id'];
            $goods = $this->getGoodsInfo($goodsId);
            if (!$goods) {
                $this->error = '很抱歉，商品不存在或已下架';
                return [];
            }
            $sku = $this->getSku($goodsId, $goodsSkuId);
            if (!$sku) {
                $this->error = '很抱歉，该商品规格不存在';
                return [];
            }
            if ($goodsNum > (int)$sku['stock_num']) {
                $this->error = '很抱歉，商品「' . $goods['goods_name'] . '」库存不足';
                return [];
            }
            $price = $sku['goods_price'];
            $imageId = $sku['image_id'] ?: $goods['image_id'];
            $list[] = [
                'goods_id'       => $goodsId,
                'goods_name'     => $goods['goods_name'],
                'goods_image'    => $goods['goods_image'],
                'goods_sku_id'   => $sku['goods_sku_id'],
                'spec_sku_id'    => $sku['spec_sku_id'],
                'goods_attr'     => $this->buildGoodsAttr($sku['spec_sku_id']),
                'goods_price'    => $price,
                'total_num'      => $goodsNum,
                'total_price'    => bcmul((string)$price, (string)$goodsNum, 2),
                // 会员等级折扣相关（由 setOrderGoodsGradeMoney 计算填充）
                'is_enable_grade'    => $goods['is_enable_grade'],
                'is_alone_grade'     => $goods['is_alone_grade'],
                'alone_grade_equity' => $goods['alone_grade_equity'],
                'is_user_grade'      => 0,
                'grade_goods_price'  => $price,
                // 积分抵扣 / 赠送开关（来自商品配置，供 calcDiscount 使用）
                'is_points_discount' => (int)($goods['is_points_discount'] ?? 0),
                'is_points_gift'     => (int)($goods['is_points_gift'] ?? 0),
                // 按桶计押金标记（送水：1票=1桶，押金按桶收）
                'is_deposit_payable' => (int)($goods['is_deposit_payable'] ?? 0),
                // 建单扩展字段
                'image_id'           => $imageId,
                'goods_no'           => $sku['goods_no'],
                'goods_weight'       => $sku['goods_weight'],
                'spec_type'          => $goods['spec_type'],
                'line_price'         => $sku['line_price'],
                'goods_type'         => $goods['goods_type'],
                'deduct_stock_type'  => $goods['deduct_stock_type'] ?? 20,
            ];
        }
        return $list;
    }

    private function getGoodsInfo($goodsId)
    {
        $goods = Db::name('goods')->where('goods_id', $goodsId)
            ->where('goods_status', 10)
            ->where('is_delete', 0)
            ->field('goods_id,goods_name,goods_type,spec_type,deduct_stock_type,is_points_discount,is_points_gift,is_enable_grade,is_alone_grade,alone_grade_equity,is_deposit_payable')
            ->find();
        if (!$goods) {
            return false;
        }
        $imageId = (int)Db::name('goods_image')->where('goods_id', $goodsId)
            ->order('id', 'asc')->value('image_id');
        $goods['image_id'] = $imageId;
        $goods['goods_image'] = BaseModel::getFileUrl($imageId);
        $goods['is_enable_grade'] = (int)($goods['is_enable_grade'] ?? 0);
        $goods['is_alone_grade'] = (int)($goods['is_alone_grade'] ?? 0);
        $goods['alone_grade_equity'] = $goods['alone_grade_equity'] ? json_decode($goods['alone_grade_equity'], true) : [];
        $goods['is_deposit_payable'] = (int)($goods['is_deposit_payable'] ?? 0);
        return $goods;
    }

    private function getSku($goodsId, $goodsSkuId)
    {
        return Db::name('goods_sku')->where('goods_id', $goodsId)
            ->where('spec_sku_id', $goodsSkuId)
            ->field('goods_sku_id,spec_sku_id,goods_no,goods_price,line_price,goods_weight,stock_num,image_id')
            ->find();
    }

    /**
     * 规格属性文本（规格表为空时返回 ''）
     */
    private function buildGoodsAttr($specSkuId)
    {
        if (empty($specSkuId) || $specSkuId === '0') {
            return '';
        }
        $ids = array_filter(explode('_', $specSkuId), 'strlen');
        if (empty($ids)) {
            return '';
        }
        $rows = Db::name('spec_value')->alias('sv')
            ->join('spec s', 's.spec_id=sv.spec_id')
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

    /**
     * 取收货地址原始行（带回退：指定 > 默认 > 最新一条）
     */
    private function getAddressRow($addressId)
    {
        $addrModel = new UserAddressModel;
        $uid = $this->user['user_id'];
        $row = null;
        if ($addressId > 0) {
            $row = Db::name('user_address')->where('user_id', $uid)
                ->where('address_id', $addressId)->find();
        }
        if (empty($row)) {
            $defaultId = $addrModel->getDefaultId($uid);
            $row = $defaultId ? Db::name('user_address')->where('user_id', $uid)
                ->where('address_id', $defaultId)->find() : null;
        }
        if (empty($row)) {
            $row = Db::name('user_address')->where('user_id', $uid)
                ->order('address_id', 'desc')->find();
        }
        return $row;
    }

    private function getAddress($addressId)
    {
        $row = $this->getAddressRow($addressId);
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

    private function getExpressPrice($delivery)
    {
        // 本批无运费配置，统一免运费
        return '0.00';
    }

    private function genOrderNo()
    {
        return date('YmdHis') . str_pad(mt_rand(0, 9999), 4, '0');
    }

    public function getError()
    {
        return $this->error;
    }
}
