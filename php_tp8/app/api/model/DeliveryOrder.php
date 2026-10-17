<?php
namespace app\api\model;

use app\common\model\DeliveryOrder as DeliveryOrderModel;
use app\api\model\LocalDelivery;
use think\facade\Db;

/**
 * 外送订单（小程序端，移植自 TP5 api/model/DeliveryOrder.php）
 *
 * 对外输出契约（对齐 TP5）：
 * - 列表：list[] + total/per_page/current_page，每项含 status_text/address 与店铺接单状态
 * - 下单：order_no/order_id/total_price/status/status_text
 */
class DeliveryOrder extends DeliveryOrderModel
{
    protected $hidden = [
        'wxapp_id',
        'update_time',
        'receiver_name',
        'receiver_phone',
        'receiver_region',
        'receiver_detail',
    ];

    protected $append = [
        'status_text',
        'address',
    ];

    /**
     * 我的外送订单列表（分页 + 店铺接单/配送状态）
     * @param int $userId
     * @param int $status
     * @return array
     */
    public function getMyList($userId, $status = -1)
    {
        $page = $this->getListByUser($userId, $status);
        $list = $page->append(['status_text', 'address'])
            ->hidden($this->hidden)
            ->toArray()['data'];
        // 关联店铺的接单/配送状态，用于前端外送订单卡片展示
        if (!empty($list)) {
            $deliveryIds = array_column($list, 'delivery_id');
            $shopStatus = $this->getShopStatusByDeliveryIds($deliveryIds);
            foreach ($list as &$item) {
                $st = $shopStatus[$item['delivery_id']] ?? ['accept_order' => 1, 'delivery_enabled' => 1];
                $item['accept_order'] = $st['accept_order'];
                $item['delivery_enabled'] = $st['delivery_enabled'];
            }
            unset($item);
        }
        return [
            'list'         => $list,
            'total'        => $page->total(),
            'per_page'     => $page->listRows(),
            'current_page' => $page->currentPage(),
        ];
    }

    /**
     * 创建外送订单（小程序端下单）
     * 接收 delivery_id / address_id / remark，价格与服务可用性均在后端重新校验，不信任前端
     * @param array|\app\api\model\User $user 当前用户（数组或模型）
     * @param int $deliveryId 外送服务ID
     * @param int $addressId 收货地址ID（<=0 时用默认地址）
     * @param string $remark 备注
     * @return array|false 成功返回订单信息，失败返回 false（错误见 getError）
     */
    public function createOrder($user, $deliveryId, $addressId, $remark)
    {
        // 1. 校验外送服务存在且启用
        $delivery = LocalDelivery::detail((int)$deliveryId);
        if (!$delivery || $delivery['status'] != 1) {
            $this->error = '外送服务不存在或已停用';
            return false;
        }
        // 2. 校验收货地址（指定地址或默认地址），且归属当前用户
        $address = $addressId > 0
            ? Db::name('user_address')->where('address_id', (int)$addressId)->find()
            : $this->getDefaultAddress((int)$user['user_id']);
        if (!$address || $address['user_id'] != $user['user_id']) {
            $this->error = '请先选择收货地址';
            return false;
        }
        if (($address['country'] ?? 'CN') !== $delivery['country']) {
            $this->error = '收货地址国家（' . ($address['country'] ?: '未知') . '）与所选外送服务（' . $delivery['country'] . '）不匹配';
            return false;
        }
        // 3. 金额与起送校验（价格以服务端为准）
        $goodsPrice = (float)$delivery['price'];
        $deliveryFee = (float)$delivery['delivery_fee'];
        $totalPrice = round($goodsPrice + $deliveryFee, 2);
        if ($delivery['min_order'] > 0 && $goodsPrice < (float)$delivery['min_order']) {
            $this->error = '未达起送金额（起送 RM ' . $delivery['min_order'] . '）';
            return false;
        }
        // 4. 收货地址快照
        $region = trim(($address['state'] ?? '') . ' ' . ($address['city'] ?? ''));
        if ($region === '' && !empty($address['detail'])) {
            $region = $address['detail'];
        }
        $data = [
            'order_no'        => $this->buildOrderNo(),
            'user_id'         => $user['user_id'],
            'delivery_id'     => $delivery['delivery_id'],
            'delivery_title'  => $delivery['title'],
            'receiver_name'   => $address['name'],
            'receiver_phone'  => $address['phone'],
            'receiver_region' => $region,
            'receiver_detail' => $address['detail'],
            'remark'          => mb_substr((string)$remark, 0, 500),
            'goods_price'     => $goodsPrice,
            'delivery_fee'    => $deliveryFee,
            'total_price'     => $totalPrice,
            'status'          => 0,
            'wxapp_id'        => $user['wxapp_id'],
            'create_time'     => time(),
            'update_time'     => time(),
        ];
        $orderId = Db::name('delivery_order')->insertGetId($data);
        if (!$orderId) {
            $this->error = '创建订单失败';
            return false;
        }
        return [
            'order_id'    => $orderId,
            'order_no'    => $data['order_no'],
            'total_price' => $totalPrice,
            'status'      => 0,
            'status_text' => '待接单',
        ];
    }

    /**
     * 用户默认地址（user.address_id 指向的地址）
     * @param int $userId
     * @return array|null
     */
    private function getDefaultAddress($userId)
    {
        $addressId = (int)Db::name('user')->where('user_id', $userId)->value('address_id');
        if ($addressId <= 0) {
            return null;
        }
        return Db::name('user_address')->where('address_id', $addressId)->find();
    }

    /**
     * 生成外送订单号：日期时间 + 6 位随机，保证可读性且与普通订单区分
     * @return string
     */
    private function buildOrderNo()
    {
        return 'WM' . date('YmdHis') . mt_rand(100000, 999999);
    }
}
