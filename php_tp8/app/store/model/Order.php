<?php
namespace app\store\model;

use think\facade\Db;
use think\Collection;
use app\api\service\OrderStateMachine as StateMachine;
use app\common\model\BaseModel;
use app\common\model\Region;
use app\store\service\order\Export as Exportservice;
use app\common\enum\order\ShopOrderStatus as ShopOrderStatusEnum;
use app\common\enum\DeliveryType as DeliveryTypeEnum;
use app\common\service\order\Refund as RefundService;
use app\store\model\UserCoupon as UserCouponModel;
use app\store\model\User as UserModel;
use app\common\model\UserWaterTicket;
use app\common\model\UserWaterTicketLog;

/**
 * 商家后台订单（P0：补 store 应用，支撑订单发货）
 *
 * 对应 php_my store/model/Order.php，只迁移「发货闭环」必需的能力：
 *   - getList()：按 dataType 筛选 + 分页（后台订单列表）
 *   - detail()：订单详情（含用户/商品/地址/物流公司）
 *   - delivery()：单个订单发货（校验条件 -> 写物流 -> 状态机改发货状态）
 *   - confirmCancel()：审核用户取消申请（order_status 21 -> 20，回退库存与销量）
 *
 * 状态迁移一律委托 OrderStateMachine，保持「状态机是唯一迁移引擎」的既有设计。
 * 重量级能力（CSV 批量发货、导出、微信发货信息录入、支付宝履约同步）本次未迁移，
 * 属于 P2 增强项，不影响发货主流程。
 */
class Order extends BaseModel
{
    protected $name = 'order';

    /** @var int 当前商家所属小程序 id（由 Auth 中间件注入） */
    protected $wxappId = 0;

    public function setWxappId($wxappId)
    {
        $this->wxappId = (int)$wxappId;
        return $this;
    }

    /**
     * 订单列表（后台）
     * @param string $dataType all|payment|delivery|received|finish|cancel
     * @param array  $query    order_no / start_time / end_time
     * @param int    $limit
     * @return \think\Paginator
     */
    public function getList($dataType = 'all', array $query = [], $limit = 15)
    {
        $where = $this->buildWhere($dataType, $query);
        return Db::name('order')
            ->where($where)
            ->order('order_id desc')
            ->paginate([
                'list_rows' => $limit,
                'query'     => $query,
            ]);
    }

    /**
     * 订单详情（后台）
     * @param int $orderId
     * @return array
     */
    public function detail($orderId)
    {
        $order = Db::name('order')->where('order_id', $orderId)->find();
        if (empty($order)) {
            return [];
        }
        $order['goods']   = Db::name('order_goods')->where('order_id', $orderId)->select()->toArray();
        $order['address'] = Db::name('order_address')->where('order_id', $orderId)->find() ?: [];
        $order['user']    = Db::name('user')->where('user_id', $order['user_id'])->find() ?: [];
        if (!empty($order['express_id'])) {
            $order['express'] = Db::name('express')->where('express_id', $order['express_id'])->find() ?: [];
        }
        foreach ($order['goods'] as &$goods) {
            $goods['image'] = BaseModel::getFileUrl($goods['image_id'] ?? 0);
        }
        unset($goods);
        return $order;
    }

    /**
     * 订单发货
     * @param int   $orderId
     * @param array $data express_id / express_no
     * @return bool
     */
    public function delivery($orderId, array $data)
    {
        $order = Db::name('order')->where('order_id', $orderId)->find();
        if (empty($order)) {
            $this->error = '订单不存在';
            return false;
        }
        if ($this->wxappId && (int)$order['wxapp_id'] !== $this->wxappId) {
            $this->error = '无权操作该订单';
            return false;
        }
        // 发货条件校验（对齐 php_my store/model/Order::verifyDelivery）
        if ((int)$order['pay_status'] !== StateMachine::PAY_SUCCESS) {
            $this->error = "订单号[{$order['order_no']}] 未付款，不满足发货条件";
            return false;
        }
        if ((int)$order['delivery_type'] !== 10) {
            $this->error = "订单号[{$order['order_no']}] 非快递配送订单（上门自提无需发货）";
            return false;
        }
        if ((int)$order['delivery_status'] !== StateMachine::DELIVERY_WAIT) {
            $this->error = "订单号[{$order['order_no']}] 已发货，请勿重复操作";
            return false;
        }
        if (empty($data['express_no'])) {
            $this->error = '请填写物流单号';
            return false;
        }

        $expressId = (int)($data['express_id'] ?? 0);
        $expressName = $expressId
            ? (string)(Db::name('express')->where('express_id', $expressId)->value('express_name') ?: '')
            : '';

        $sm = new StateMachine();
        if (!$sm->deliver($orderId, [
            'express_id'      => $expressId,
            'express_company' => $expressName,
            'express_no'      => trim((string)$data['express_no']),
        ])) {
            $this->error = $sm->getError() ?: '发货失败';
            return false;
        }
        return true;
    }

    /**
     * 审核：用户取消订单（order_status 21 待取消 -> 20 已取消）
     * 未付款订单回退库存与销量
     * @param int $orderId
     * @return bool
     */
    public function confirmCancel($orderId)
    {
        $order = Db::name('order')->where('order_id', $orderId)->find();
        if (empty($order)) {
            $this->error = '订单不存在';
            return false;
        }
        if ($this->wxappId && (int)$order['wxapp_id'] !== $this->wxappId) {
            $this->error = '无权操作该订单';
            return false;
        }
        if ((int)$order['order_status'] !== StateMachine::ORDER_APPLY_CANCEL) {
            $this->error = "订单号[{$order['order_no']}] 不处于待取消状态";
            return false;
        }
        return Db::transaction(function () use ($order) {
            // 未付款的取消申请：回退库存与销量（与用户侧取消一致）
            if ((int)$order['pay_status'] !== StateMachine::PAY_SUCCESS) {
                $goodsList = Db::name('order_goods')->where('order_id', $order['order_id'])->select()->toArray();
                foreach ($goodsList as $g) {
                    Db::name('goods_sku')->where('goods_sku_id', $g['goods_sku_id'])
                        ->inc('stock_num', $g['total_num'])->update();
                    // sales_actual 为 unsigned，用 GREATEST 兜底，避免销量为 0 时自减下溢
                    Db::name('goods')->where('goods_id', $g['goods_id'])
                        ->update(['sales_actual' => StateMachine::decFloorRaw('sales_actual', (int)$g['total_num'])]);
                }
            }
            Db::name('order')->where('order_id', $order['order_id'])->update([
                'order_status' => StateMachine::ORDER_CANCELLED,
                'update_time'  => time(),
            ]);
            return true;
        });
    }

    /* ==================== 服务端渲染（后台页面）专用扩展 ==================== */

    /** 状态文案表：付款状态 */
    protected static $payStatusText = [10 => '待付款', 20 => '已付款'];
    /** 状态文案表：发货状态 */
    protected static $deliveryStatusText = [10 => '待发货', 20 => '已发货'];
    /** 状态文案表：收货状态 */
    protected static $receiptStatusText = [10 => '待收货', 20 => '已收货'];
    /** 状态文案表：订单状态 */
    protected static $orderStatusText = [10 => '进行中', 20 => '已取消', 21 => '待取消', 30 => '已完成'];
    /** 状态文案表：配送方式 */
    protected static $deliveryTypeText = [10 => '快递配送', 20 => '上门自提'];
    /** 状态文案表：支付方式 */
    protected static $payTypeText = [10 => '余额支付', 20 => '微信支付', 30 => '好友代付', 40 => '支付宝支付', 50 => 'Stripe支付'];

    /**
     * 后台订单列表（带商品/用户/状态文案）
     * @param string $dataType delivery|receipt|pay|complete|cancel|all|virtual
     * @param array  $query
     * @param int    $limit
     * @return \think\Paginator
     */
    public function getListForAdmin($dataType = 'all', array $query = [], $limit = 15)
    {
        // TP5 页面 dataType -> 模型内部 dataType
        $map = ['pay' => 'payment', 'receipt' => 'received', 'complete' => 'finish', 'virtual' => 'all'];
        $dataType = $map[$dataType] ?? $dataType;

        $list = $this->getList($dataType, $query, $limit);
        $items = $list->items();
        foreach ($items as &$order) {
            $order = $this->enrichOrder($order);
        }
        $list->setCollection(Collection::make($items));
        return $list;
    }

    /**
     * 组装单条订单的展示数据（商品/用户/状态文案）
     * @param array $order
     * @return array
     */
    public function enrichOrder(array $order)
    {
        $orderId = (int)$order['order_id'];
        $goods = Db::name('order_goods')->where('order_id', $orderId)->select()->toArray();
        foreach ($goods as &$g) {
            $g['image'] = ['file_path' => BaseModel::getFileUrl($g['image_id'] ?? 0)];
        }
        unset($g);
        $order['goods'] = $goods;
        $order['user'] = Db::name('user')->where('user_id', $order['user_id'])
            ->field('user_id, nickName')->find()
            ?: ['user_id' => $order['user_id'], 'nickName' => ''];
        return $this->withStatusText($order);
    }

    /**
     * 把 6 个状态字段转成视图需要的 ['text'=>.., 'value'=>..]
     * @param array $order
     * @return array
     */
    public function withStatusText(array $order)
    {
        $order['pay_status'] = [
            'text'  => self::$payStatusText[$order['pay_status']] ?? '未知',
            'value' => (int)$order['pay_status'],
        ];
        $order['delivery_status'] = [
            'text'  => self::$deliveryStatusText[$order['delivery_status']] ?? '未知',
            'value' => (int)$order['delivery_status'],
        ];
        $order['receipt_status'] = [
            'text'  => self::$receiptStatusText[$order['receipt_status']] ?? '未知',
            'value' => (int)$order['receipt_status'],
        ];
        $order['order_status'] = [
            'text'  => self::$orderStatusText[$order['order_status']] ?? '未知',
            'value' => (int)$order['order_status'],
        ];
        $order['delivery_type'] = [
            'text'  => self::$deliveryTypeText[$order['delivery_type']] ?? '未知',
            'value' => (int)$order['delivery_type'],
        ];
        $order['pay_type'] = [
            'text'  => self::$payTypeText[$order['pay_type']] ?? '未知',
            'value' => (int)$order['pay_type'],
        ];
        // 后台改价：视图按 symbol/value 展示（+/- + 两位小数绝对值）
        $updatePrice = (float)($order['update_price'] ?? 0);
        $order['update_price'] = [
            'symbol' => $updatePrice < 0 ? '-' : '+',
            'value'  => sprintf('%.2f', abs($updatePrice)),
        ];
        return $order;
    }

    /**
     * 后台订单详情（状态文案 + 地址地区 + 自提信息）
     * @param int $orderId
     * @return array
     */
    public function detailForAdmin($orderId)
    {
        $order = $this->detail($orderId);
        if (empty($order)) {
            return [];
        }
        // 商品图片统一成嵌套结构
        foreach ($order['goods'] as &$g) {
            $g['image'] = ['file_path' => BaseModel::getFileUrl($g['image_id'] ?? 0)];
        }
        unset($g);
        // 收货地址：解析地区名称
        if (!empty($order['address'])) {
            $order['address']['region'] = $this->resolveRegion($order['address']);
        }
        // 自提信息
        $order['extract'] = Db::name('order_extract')->where('order_id', $orderId)->find() ?: [];
        $shopId = (int)($order['extract_shop_id'] ?? 0);
        $clerkId = (int)($order['extract_clerk_id'] ?? 0);
        $shopRow = $shopId
            ? Db::name('store_shop')->where('shop_id', $shopId)->find()
            : [];
        if (!empty($shopRow)) {
            // 视图按嵌套关系访问 logo.file_path 与 region.{province,city,region}
            $shopRow['logo'] = [
                'file_path' => BaseModel::getFileUrl($shopRow['logo_image_id'] ?? 0),
            ];
            $shopRow['region'] = [
                'province' => Region::getNameById($shopRow['province_id'] ?? 0),
                'city'     => Region::getNameById($shopRow['city_id'] ?? 0),
                'region'   => ($shopRow['region_id'] ?? 0) == 0 ? '' : Region::getNameById($shopRow['region_id']),
            ];
        }
        $order['extract_shop'] = $shopRow ?: [];
        $order['extract_clerk'] = $clerkId
            ? (Db::name('store_shop_clerk')->where('clerk_id', $clerkId)->find() ?: [])
            : [];
        return $this->withStatusText($order);
    }

    /**
     * 解析收货地址的省/市/区名称（马来西亚走 state/city 直取）
     * @param array $address
     * @return array
     */
    private function resolveRegion(array $address)
    {
        if (($address['country'] ?? '') === 'MY') {
            return [
                'province' => $address['state'] ?? '',
                'city'     => $address['city'] ?? '',
                'region'   => '',
            ];
        }
        return [
            'province' => $this->regionName($address['province_id'] ?? 0),
            'city'     => $this->regionName($address['city_id'] ?? 0),
            'region'   => $this->regionName($address['region_id'] ?? 0),
        ];
    }

    private function regionName($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return '';
        }
        return (string)(Db::name('region')->where('id', $id)->value('name') ?: '');
    }

    /**
     * 修改订单价格（改价：同步 order_price / pay_price + 记录 update_price）
     * @param int   $orderId
     * @param array $data order[update_price]、order[express_price]
     * @return bool
     */
    public function updatePrice($orderId, array $data)
    {
        $order = Db::name('order')->where('order_id', $orderId)->find();
        if (empty($order)) {
            $this->error = '订单不存在';
            return false;
        }
        if ($this->wxappId && (int)$order['wxapp_id'] !== $this->wxappId) {
            $this->error = '无权操作该订单';
            return false;
        }
        // 已付款订单不允许改价
        if ((int)$order['pay_status'] === StateMachine::PAY_SUCCESS) {
            $this->error = "订单号[{$order['order_no']}] 已付款，不允许修改价格";
            return false;
        }
        $updatePrice = isset($data['update_price']) ? (float)$data['update_price'] : 0;
        $expressPrice = isset($data['express_price']) ? (float)$data['express_price'] : (float)$order['express_price'];
        if ($updatePrice < 0 || $expressPrice < 0) {
            $this->error = '价格不能为负数';
            return false;
        }
        Db::name('order')->where('order_id', $orderId)->update([
            'update_price'  => $updatePrice,
            'express_price' => $expressPrice,
            'order_price'   => (float)$order['total_price'] + $expressPrice,
            'pay_price'     => max(0, (float)$order['total_price'] + $expressPrice - $updatePrice),
            'update_time'   => time(),
        ]);
        return true;
    }

    /**
     * 门店自提核销（移植自 TP5 common/model/Order::verificationOrder）
     * 条件：已付款 + 上门自提 + 未核销 + 订单未取消
     * 成功后：发货/收货状态置为已完成，订单完成，并写核销流水
     * @param int $orderId
     * @param int $clerkId 核销员id
     * @return bool
     */
    public function verificationOrder($orderId, $clerkId)
    {
        $order = Db::name('order')->where('order_id', $orderId)->find();
        if (empty($order)) {
            $this->error = '订单不存在';
            return false;
        }
        if ($this->wxappId && (int)$order['wxapp_id'] !== $this->wxappId) {
            $this->error = '无权操作该订单';
            return false;
        }
        if ((int)$order['pay_status'] !== 20
            || (int)$order['delivery_type'] !== 20
            || (int)$order['delivery_status'] === 20
            || in_array((int)$order['order_status'], [20, 21], true)) {
            $this->error = '该订单不满足核销条件';
            return false;
        }
        Db::startTrans();
        try {
            Db::name('order')->where('order_id', $orderId)->update([
                'extract_clerk_id' => $clerkId,
                'delivery_status'  => 20,
                'delivery_time'    => time(),
                'receipt_status'   => 20,
                'receipt_time'     => time(),
                'order_status'     => 30,
                'update_time'      => time(),
            ]);
            // 核销流水（store_shop_order）
            Db::name('store_shop_order')->insert([
                'order_id'   => $orderId,
                'shop_id'    => (int)$order['extract_shop_id'],
                'clerk_id'   => $clerkId,
                'order_type' => 10,
                'wxapp_id'   => (int)$order['wxapp_id'],
                'create_time' => time(),
            ]);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 构建列表查询条件
     * @param string $dataType
     * @param array  $query
     * @return array
     */
    private function buildWhere($dataType, array $query)
    {
        $where = [
            ['is_delete', '=', 0],
        ];
        if ($this->wxappId) {
            $where[] = ['wxapp_id', '=', $this->wxappId];
        }
        switch ($dataType) {
            case 'payment':  // 待付款
                $where[] = ['pay_status', '=', 10];
                $where[] = ['order_status', '=', 10];
                break;
            case 'delivery': // 待发货
                $where[] = ['pay_status', '=', 20];
                $where[] = ['delivery_status', '=', 10];
                $where[] = ['order_status', '=', 10];
                break;
            case 'received': // 待收货
                $where[] = ['pay_status', '=', 20];
                $where[] = ['delivery_status', '=', 20];
                $where[] = ['receipt_status', '=', 10];
                $where[] = ['order_status', '=', 10];
                break;
            case 'finish':   // 已完成
                $where[] = ['order_status', '=', 30];
                break;
            case 'cancel':   // 已取消
                $where[] = ['order_status', 'in', [20, 21]];
                break;
        }
        if (!empty($query['order_no'])) {
            $where[] = ['order_no', 'like', '%' . trim($query['order_no']) . '%'];
        }
        if (!empty($query['start_time'])) {
            $where[] = ['create_time', '>=', strtotime($query['start_time'])];
        }
        if (!empty($query['end_time'])) {
            $where[] = ['create_time', '<=', strtotime($query['end_time'] . ' 23:59:59')];
        }
        return $where;
    }

    /**
     * 获取订单列表（不分页，供 CSV 导出使用）
     * 与 getList() 共用检索条件，并批量补齐导出所需的关联数据（商品/地址/自提/买家/物流）
     * @param string $dataType
     * @param array  $query
     * @return array
     */
    public function getListAll($dataType = 'all', array $query = [])
    {
        $where  = $this->buildWhere($dataType, $query);
        $orders = Db::name('order')->where($where)->order('order_id desc')->select()->toArray();
        if (empty($orders)) {
            return [];
        }
        $orderIds = array_column($orders, 'order_id');
        $userIds  = array_unique(array_filter(array_column($orders, 'user_id')));
        $expressIds = array_unique(array_filter(array_column($orders, 'express_id')));
        $shopIds    = array_unique(array_filter(array_column($orders, 'extract_shop_id')));

        // 批量查询，避免逐单 N+1
        $goodsMap = $addressMap = $extractMap = [];
        foreach (Db::name('order_goods')->whereIn('order_id', $orderIds)->select()->toArray() as $row) {
            $goodsMap[$row['order_id']][] = $row;
        }
        foreach (Db::name('order_address')->whereIn('order_id', $orderIds)->select()->toArray() as $row) {
            $addressMap[$row['order_id']] = $row;
        }
        foreach (Db::name('order_extract')->whereIn('order_id', $orderIds)->select()->toArray() as $row) {
            $extractMap[$row['order_id']] = $row;
        }
        $userMap = [];
        if ($userIds) {
            foreach (Db::name('user')->whereIn('user_id', $userIds)->field('user_id, nickName')->select()->toArray() as $row) {
                $userMap[$row['user_id']] = $row;
            }
        }
        $expressMap = [];
        if ($expressIds) {
            foreach (Db::name('express')->whereIn('express_id', $expressIds)->select()->toArray() as $row) {
                $expressMap[$row['express_id']] = $row;
            }
        }
        $shopMap = [];
        if ($shopIds) {
            foreach (Db::name('store_shop')->whereIn('shop_id', $shopIds)->select()->toArray() as $row) {
                $shopMap[$row['shop_id']] = $row;
            }
        }

        foreach ($orders as &$order) {
            $orderId = (int)$order['order_id'];
            $order['goods']        = $goodsMap[$orderId] ?? [];
            $order['address']      = $addressMap[$orderId] ?? [];
            $order['extract']      = $extractMap[$orderId] ?? [];
            $order['extract_shop'] = $shopMap[(int)($order['extract_shop_id'] ?? 0)] ?? [];
            $order['user']         = $userMap[(int)($order['user_id'] ?? 0)] ?? ['nickName' => ''];
            $order['express']      = $expressMap[(int)($order['express_id'] ?? 0)] ?? [];
            $order = $this->withStatusText($order);
        }
        unset($order);
        return $orders;
    }

    /**
     * 订单导出（CSV）
     * @param string $dataType
     * @param array  $query
     * @return array ['filename' => string, 'csv' => string]
     */
    public function exportList($dataType = 'all', array $query = [])
    {
        $list = $this->getListAll($dataType, $query);
        return [
            'filename' => 'order-' . date('YmdHis') . '.csv',
            'csv'      => (new Exportservice)->orderList($list),
        ];
    }

    /**
     * 批量发货模板（CSV，仅表头）
     * @return array ['filename' => string, 'csv' => string]
     */
    public function deliveryTpl()
    {
        return [
            'filename' => 'delivery-' . date('YmdHis') . '.csv',
            'csv'      => (new Exportservice)->deliveryTpl(),
        ];
    }

    /**
     * 获取已付款订单总数（可指定日期区间）
     * @param string|null $startDate
     * @param string|null $endDate
     * @return int
     */
    public function getPayOrderTotal($startDate = null, $endDate = null)
    {
        $query = Db::name('order')
            ->where('is_delete', '=', 0)
            ->where('pay_status', '=', 20)
            ->where('order_status', '<>', 20);
        if ($this->wxappId) {
            $query->where('wxapp_id', '=', $this->wxappId);
        }
        if (!is_null($startDate) && !is_null($endDate)) {
            $query->where('pay_time', '>=', strtotime($startDate))
                ->where('pay_time', '<', strtotime($endDate) + 86400);
        }
        return (int)$query->count();
    }

    /**
     * 获取销售额（可指定日期区间）
     * @param string|null $startDate
     * @param string|null $endDate
     * @return float
     */
    public function getOrderTotalPrice($startDate = null, $endDate = null)
    {
        $query = Db::name('order')
            ->where('is_delete', '=', 0)
            ->where('pay_status', '=', 20)
            ->where('order_status', '<>', 20);
        if ($this->wxappId) {
            $query->where('wxapp_id', '=', $this->wxappId);
        }
        if (!is_null($startDate) && !is_null($endDate)) {
            $query->where('pay_time', '>=', strtotime($startDate))
                ->where('pay_time', '<', strtotime($endDate) + 86400);
        }
        return (float)$query->sum('pay_price');
    }

    /**
     * 获取某天的下单用户数（去重）
     * @param string $day
     * @return int
     */
    public function getPayOrderUserTotal($day)
    {
        $startTime = strtotime($day);
        $query = Db::name('order')
            ->where('is_delete', '=', 0)
            ->where('pay_status', '=', 20)
            ->where('pay_time', '>=', $startTime)
            ->where('pay_time', '<', $startTime + 86400);
        if ($this->wxappId) {
            $query->where('wxapp_id', '=', $this->wxappId);
        }
        return count($query->distinct(true)->column('user_id'));
    }

    /* ==================== 送水订单（门店配送）专用 ==================== */
    // 注：TP8 的 Order 为数组/Db 风格（detail 返回数组、字段裸值），送水相关方法同样保持数组风格，
    // 不依赖模型对象与枚举获取器。订单状态/支付状态使用 StateMachine 常量（TP8 未迁移 OrderStatus/PayStatus 枚举）。

    /**
     * 送水订单列表（门店配送）
     * @param array $query
     * @return \think\Paginator
     */
    public function getWaterList(array $query = [])
    {
        $model = Db::name('order')->alias('o')
            ->join('user u', 'u.user_id = o.user_id')
            ->leftJoin('store_shop s', 's.shop_id = o.delivery_shop_id')
            ->leftJoin('store_shop_clerk c', 'c.clerk_id = o.delivery_clerk_id')
            ->where('o.delivery_type', '=', DeliveryTypeEnum::SHOP_DELIVERY)
            ->where('o.is_delete', '=', 0);
        if ($this->wxappId) {
            $model->where('o.wxapp_id', '=', $this->wxappId);
        }
        if (!empty($query['delivery_shop_id'])) {
            $model->where('o.delivery_shop_id', '=', (int)$query['delivery_shop_id']);
        }
        if (!empty($query['shop_order_status'])) {
            $model->where('o.shop_order_status', '=', (int)$query['shop_order_status']);
        }
        if (!empty($query['search'])) {
            $keyword = trim($query['search']);
            $model->where('o.order_no|u.nickName', 'like', "%{$keyword}%");
        }
        $list = $model->field('o.*, u.nickName, s.shop_name as delivery_shop_name, c.real_name as clerk_name')
            ->order(['o.shop_order_status' => 'asc', 'o.create_time' => 'desc'])
            ->paginate(15, false, ['query' => request()->param()]);
        $items = $list->items();
        foreach ($items as &$order) {
            $order = $this->enrichWaterOrder($order);
        }
        unset($order);
        $list->setCollection(Collection::make($items));
        return $list;
    }

    /**
     * 送水订单：待接单数量
     * @return int
     */
    public static function getWaterWaitAcceptCount()
    {
        return (new static)
            ->where('delivery_type', '=', DeliveryTypeEnum::SHOP_DELIVERY)
            ->where('shop_order_status', '=', ShopOrderStatusEnum::WAIT_ACCEPT)
            ->where('order_status', '=', StateMachine::ORDER_NORMAL)
            ->where('is_delete', '=', 0)
            ->count();
    }

    /**
     * 送水订单详情（裸字段，供 WaterOrder 控制器使用）
     * @param int $orderId
     * @return array
     */
    public static function detailWater($orderId)
    {
        $order = Db::name('order')->where('order_id', $orderId)->find();
        if (empty($order)) {
            return [];
        }
        return (new static)->enrichWaterOrder($order);
    }

    /**
     * 补充送水订单展示字段（商品/用户/地址/门店名/配送员）
     * @param array $order
     * @return array
     */
    private function enrichWaterOrder(array $order)
    {
        $orderId = (int)$order['order_id'];
        $goods = Db::name('order_goods')->where('order_id', $orderId)->select()->toArray();
        foreach ($goods as &$g) {
            $g['image'] = ['file_path' => BaseModel::getFileUrl($g['image_id'] ?? 0)];
        }
        unset($g);
        $order['goods'] = $goods;
        if (empty($order['address'])) {
            $order['address'] = Db::name('order_address')->where('order_id', $orderId)->find() ?: [];
        }
        if (!isset($order['nickName'])) {
            $order['user'] = Db::name('user')->where('user_id', $order['user_id'])
                ->field('user_id, nickName')->find()
                ?: ['user_id' => $order['user_id'], 'nickName' => ''];
        } else {
            $order['user'] = ['user_id' => $order['user_id'], 'nickName' => $order['nickName']];
        }
        $order['deliveryClerk'] = !empty($order['clerk_name'])
            ? ['real_name' => $order['clerk_name']]
            : [];
        if (!isset($order['delivery_shop_name'])) {
            $order['delivery_shop_name'] = '';
        }
        return $order;
    }

    /**
     * 更新门店配送状态（接单/指派配送员）
     * @param int $orderId
     * @param int $status
     * @param int $clerkId
     * @return bool
     */
    public function updateShopOrderStatus($orderId, $status, $clerkId = 0)
    {
        $data = ['shop_order_status' => (int)$status, 'update_time' => time()];
        if ($clerkId > 0) {
            $data['delivery_clerk_id'] = (int)$clerkId;
        }
        return Db::name('order')->where('order_id', $orderId)->update($data) !== false;
    }

    /**
     * 完成送水配送
     * @param int $orderId
     * @return bool
     */
    public function finishWaterOrder($orderId)
    {
        return Db::name('order')->where('order_id', $orderId)->update([
            'shop_order_status' => ShopOrderStatusEnum::COMPLETED,
            'delivery_status'   => 20,
            'receipt_status'    => 20,
            'receipt_time'      => time(),
            'order_status'      => StateMachine::ORDER_COMPLETED,
            'update_time'       => time(),
        ]) !== false;
    }

    /**
     * 退还押桶押金（控制器已做幂等/超退防护）
     * @param int   $orderId
     * @param array $order
     * @return bool
     */
    public function refundDeposit($orderId, array $order)
    {
        $refund = new RefundService();
        if (!$refund->execute($order, (float)$order['deposit_amount'])) {
            $this->error = $refund->getError() ?: '押金退还失败';
            return false;
        }
        Db::name('order')->where('order_id', $orderId)->update([
            'deposit_refund_status' => 1,
            'deposit_refund_time'  => time(),
            'update_time'          => time(),
        ]);
        return true;
    }

    /**
     * 更新送水订单字段（登记空桶 / 拒绝取消申请）
     * @param int   $orderId
     * @param array $data
     * @return bool
     */
    public function updateWaterField($orderId, array $data)
    {
        $data['update_time'] = time();
        return Db::name('order')->where('order_id', $orderId)->update($data) !== false;
    }

    /**
     * 取消送水订单（联动主订单：已付款自动退款+回库存+回券/积分+回水票）
     * @param int $orderId
     * @return bool
     */
    public function cancelWaterOrder($orderId)
    {
        $order = Db::name('order')->where('order_id', $orderId)->find();
        if (empty($order)) {
            $this->error = '订单不存在';
            return false;
        }
        try {
            return Db::transaction(function () use ($order) {
                if ((int)$order['pay_status'] === StateMachine::PAY_SUCCESS) {
                    $refundMoney = (float)$order['pay_price'];
                    if ((float)$order['deposit_amount'] > 0 && (int)$order['deposit_refund_status'] === 1) {
                        $refundMoney = (float)$order['pay_price'] - (float)$order['deposit_amount'];
                    }
                    if ($refundMoney > 0) {
                        $refund = new RefundService();
                        if (!$refund->execute($order, $refundMoney)) {
                            throw new \Exception($refund->getError() ?: '退款失败');
                        }
                    }
                    // 回退商品库存
                    $goodsList = Db::name('order_goods')->where('order_id', $order['order_id'])->select()->toArray();
                    foreach ($goodsList as $g) {
                        Db::name('goods_sku')->where('goods_sku_id', $g['goods_sku_id'])
                            ->inc('stock_num', $g['total_num'])->update();
                        Db::name('goods')->where('goods_id', $g['goods_id'])
                            ->update(['sales_actual' => StateMachine::decFloorRaw('sales_actual', (int)$g['total_num'])]);
                    }
                    // 回退用户优惠券
                    if ((int)$order['coupon_id'] > 0) {
                        Db::name('user_coupon')->where('coupon_id', (int)$order['coupon_id'])
                            ->update(['is_use' => 0]);
                    }
                    // 回退用户积分
                    if ((int)$order['points_num'] > 0) {
                        Db::name('user')->where('user_id', $order['user_id'])
                            ->inc('points', (int)$order['points_num'])->update();
                        Db::name('user_points_log')->insert([
                            'user_id'     => $order['user_id'],
                            'points'      => (int)$order['points_num'],
                            'describe'    => "订单取消：{$order['order_no']}",
                            'wxapp_id'    => (int)$order['wxapp_id'],
                            'create_time' => time(),
                        ]);
                    }
                    // 回退用户水票（按订单归属 wxapp_id）
                    if ((int)$order['water_ticket_num'] > 0) {
                        $wxappId = (int)($order['wxapp_id'] ?? 0);
                        if (!UserWaterTicket::adjust(
                            $order['user_id'],
                            (int)$order['water_ticket_num'],
                            $wxappId,
                            UserWaterTicketLog::SCENE_REFUND,
                            $order['order_no']
                        )) {
                            throw new \Exception('水票回退失败');
                        }
                    }
                }
                Db::name('order')->where('order_id', $order['order_id'])->update([
                    'shop_order_status'     => ShopOrderStatusEnum::CANCELLED,
                    'order_status'          => StateMachine::ORDER_CANCELLED,
                    'deposit_refund_status' => 1,
                    'update_time'           => time(),
                ]);
                return true;
            });
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}
