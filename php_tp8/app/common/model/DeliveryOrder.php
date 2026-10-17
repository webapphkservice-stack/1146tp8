<?php
namespace app\common\model;

/**
 * 外送订单模型（移植自 TP5 common/model/DeliveryOrder.php）
 *
 * 状态：0待接单 1已接单(配送中) 2已完成 3已取消
 * 订单由商家后台创建，小程序端展示/确认收货。
 */
class DeliveryOrder extends BaseModel
{
    protected $name = 'delivery_order';
    protected $pk = 'order_id';

    /**
     * 状态文字
     */
    public static $statusTextMap = [
        0 => '待接单',
        1 => '已接单',
        2 => '已完成',
        3 => '已取消',
    ];

    /**
     * 状态文字（访问器，append 后自动附加）
     */
    public function getStatusTextAttr($value, $data)
    {
        return isset(self::$statusTextMap[$data['status']]) ? self::$statusTextMap[$data['status']] : '';
    }

    /**
     * 下单时间（按马来西亚时区格式化，避免服务器时区配置不一致导致显示偏差）
     */
    public function getCreateTimeTextAttr($value, $data)
    {
        if (empty($data['create_time'])) {
            return '';
        }
        $dt = new \DateTime('@' . $data['create_time']);
        $dt->setTimezone(new \DateTimeZone('Asia/Kuala_Lumpur'));
        return $dt->format('Y-m-d H:i');
    }

    /**
     * 收货地址（合并快照字段，方便小程序直接展示）
     */
    public function getAddressAttr($value, $data)
    {
        return [
            'name'   => $data['receiver_name'] ?? '',
            'phone'  => $data['receiver_phone'] ?? '',
            'region' => $data['receiver_region'] ?? '',
            'detail' => $data['receiver_detail'] ?? '',
        ];
    }

    /**
     * 我的订单列表（按用户分页）
     * @param int $userId
     * @param int $status 不传或 -1 表示全部
     * @return \think\Paginator
     */
    public function getListByUser($userId, $status = -1)
    {
        $query = $this->where('user_id', $userId);
        if ($status !== '' && $status !== null && $status >= 0) {
            $query->where('status', $status);
        }
        return $query->order('order_id', 'desc')->paginate(10);
    }

    /**
     * 根据外送服务ID批量取所属店铺的接单/配送状态
     * 订单只存 delivery_id，需通过 local_delivery.shop_id 关联到店铺
     * @param array $deliveryIds
     * @return array [delivery_id => ['accept_order'=>, 'delivery_enabled'=>]]
     */
    public function getShopStatusByDeliveryIds($deliveryIds)
    {
        if (empty($deliveryIds)) {
            return [];
        }
        $rows = \think\facade\Db::name('local_delivery')
            ->alias('d')
            ->join('local_delivery_shop s', 's.shop_id = d.shop_id', 'LEFT')
            ->whereIn('d.delivery_id', $deliveryIds)
            ->field('d.delivery_id, s.accept_order, s.delivery_enabled')
            ->select()->toArray();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['delivery_id']] = [
                'accept_order'     => (int)$r['accept_order'],
                'delivery_enabled' => (int)$r['delivery_enabled'],
            ];
        }
        return $map;
    }

    /**
     * 用户确认收货：将订单状态置为「已完成」
     * 仅允许 待接单(0)/已接单(1) 变更为已完成(2)；已取消(3) 不允许；已完成(2) 幂等成功
     * @return bool
     */
    public function confirmByUser()
    {
        $status = $this->getAttr('status');
        if ($status == 3) {
            $this->error = '订单已取消，无法确认收货';
            return false;
        }
        if ($status == 2) {
            return true; // 已是完成态，幂等返回成功
        }
        return $this->save([
            'status'      => 2,
            'update_time' => time(),
        ]) !== false;
    }

    /**
     * 标记商户侧回写结果（由回写队列消费后调用，保证两端状态一致）
     * @param int $orderId
     * @param int $wxappId
     * @param int $status 1成功 2失败
     * @return bool
     */
    public static function markNotified($orderId, $wxappId, $status)
    {
        return \think\facade\Db::name('delivery_order')
            ->where('order_id', $orderId)
            ->where('wxapp_id', $wxappId)
            ->update([
                'notify_status' => $status,
                'notify_time'   => time(),
            ]) !== false;
    }

    /**
     * 商家后台外送订单列表（按 wxapp_id）
     * 通过 local_delivery.shop_id 关联店铺，直接带出店铺接单/配送状态
     *
     * TP8 适配：Paginator 不支持 TP5 的 append()，改为逐条补齐视图所需字段后重建集合
     * @param int   $wxappId
     * @param array $params 搜索参数：order_no / status
     * @return \think\Paginator
     */
    public function getListForStore($wxappId, $params = [])
    {
        // 关闭 wxappId 全局作用域：该作用域注入的是裸列 wxapp_id，
        // 而本查询 join 的 local_delivery / local_delivery_shop 同样含 wxapp_id 列，
        // 会导致 "Column 'wxapp_id' in where clause is ambiguous"，故显式改用 o.wxapp_id
        $query = $this->withoutGlobalScope(['wxappId'])
            ->alias('o')
            ->field('o.*, s.shop_name, s.accept_order, s.delivery_enabled')
            ->join('local_delivery d', 'd.delivery_id = o.delivery_id', 'LEFT')
            ->join('local_delivery_shop s', 's.shop_id = d.shop_id', 'LEFT')
            ->where('o.wxapp_id', $wxappId);
        if (!empty($params['order_no'])) {
            $query->where('o.order_no', 'like', '%' . trim($params['order_no']) . '%');
        }
        if (isset($params['status']) && $params['status'] !== '' && $params['status'] >= 0) {
            $query->where('o.status', $params['status']);
        }
        $list = $query->order('o.order_id', 'desc')
            ->paginate(['list_rows' => 15, 'query' => request()->param()]);
        $items = $list->items();
        foreach ($items as &$item) {
            $item = $item instanceof self ? $item->toArray() : (array)$item;
            $item['status_text'] = self::$statusTextMap[$item['status']] ?? '';
            $item['address'] = [
                'name'   => $item['receiver_name'] ?? '',
                'phone'  => $item['receiver_phone'] ?? '',
                'region' => $item['receiver_region'] ?? '',
                'detail' => $item['receiver_detail'] ?? '',
            ];
            $item['create_time_text'] = self::formatKlTime($item['create_time'] ?? 0);
        }
        unset($item);
        $list->setCollection(\think\Collection::make($items));
        return $list;
    }

    /**
     * 店铺后台修改订单状态（订单所属店铺可操作）
     * 允许在 0待接单 / 1已接单 / 3已取消 之间变更
     * 已完成(2)仅限下单人在小程序端确认收货触发，店铺后台不可直接设置
     * 变更后回写商户侧 webhook，保证两端状态最终一致
     * @param int $status
     * @return bool
     */
    public function setStatusByStore($status)
    {
        $allow = [0, 1, 3];
        $status = (int)$status;
        if (!in_array($status, $allow, true)) {
            $this->error = '无效的订单状态';
            return false;
        }
        if ($this->save(['status' => $status, 'update_time' => time()]) === false) {
            $this->error = '状态更新失败';
            return false;
        }
        // 状态变更后回写商户侧，保证两端最终一致（与用户确认收货逻辑一致）
        // 该类可能尚未部署到线上，故先判断存在性；并用 Throwable 兜底，避免回写异常影响状态更新结果
        if (class_exists('\\app\\common\\library\\DeliveryWebhookQueue')) {
            try {
                \app\common\library\DeliveryWebhookQueue::push($this);
            } catch (\Throwable $e) {
                // 回写失败不影响状态更新结果
            }
        }
        return true;
    }

    /**
     * 下单时间文本（马来西亚时区）
     * 兼容两种入参：int 时间戳，或模型 toArray() 后已被转成 'Y-m-d H:i:s' 的日期字符串
     * （TP8 模型 toArray 会按 dateFormat 转换时间字段，直接 new DateTime('@'.字符串) 会抛
     *   "Double timezone specification"，故此处先判断是否为数字）
     * @param int|string $time
     * @return string
     */
    private static function formatKlTime($time)
    {
        if (empty($time)) {
            return '';
        }
        // 已被转成日期字符串：直接截取到分钟
        if (!is_numeric($time)) {
            return substr((string)$time, 0, 16);
        }
        return (new \DateTime('@' . (int)$time))
            ->setTimezone(new \DateTimeZone('Asia/Kuala_Lumpur'))
            ->format('Y-m-d H:i');
    }
}
