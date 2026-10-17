<?php

namespace app\api\controller\bargain;

use app\api\controller\Controller;
use app\api\service\Payment;
use app\common\enum\order\OrderSource;
use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 砍价订单结算（完整移植 php api/controller/bargain/Order）
 * 小程序调用点：bargain.order/checkout（GET 结算页 / POST 提交建单）
 * 砍价商品不参与折扣；订单走主 order 表（order_source = OrderSource::BARGAIN = 20）。
 *
 * 注意：此处原硬编码为 30，但 30 是 OrderSource::SHARP（秒杀），
 * 会导致砍价订单被「按分钟关闭秒杀订单」的 sharp:cron 误关，
 * 且支付成功分发（OrderStateMachine::dispatchPaySuccess）会走到秒杀分支回退 seckill_stock。
 * 修正为 OrderSource::BARGAIN(20)，砍价走普通商品库存/通用逻辑。
 */
class Order extends Controller
{
    public function checkout()
    {
        $user = $this->getUser(true);
        $wxappId = $this->getWxappId();
        $params = $this->request->param();
        $taskId = (int)($params['task_id'] ?? 0);
        $task = Db::name('bargain_task')->where('task_id', $taskId)
            ->where('is_delete', 0)->where('wxapp_id', $wxappId)->find();
        if (empty($task) || (int)$task['status'] !== 1) {
            return $this->renderError('砍价任务不存在或已结束');
        }
        if ((int)$task['is_buy'] === 1) {
            return $this->renderError('该砍价商品已购买');
        }
        $goods = Db::name('goods')->where('goods_id', $task['goods_id'])
            ->where('goods_status', 10)->where('is_delete', 0)
            ->field('goods_id,goods_name,goods_type,spec_type,deduct_stock_type')->find();
        if (empty($goods)) {
            return $this->renderError('很抱歉，商品不存在或已下架');
        }
        $sku = Db::name('goods_sku')->where('goods_id', $task['goods_id'])
            ->where('spec_sku_id', (string)$task['spec_sku_id'])->find();
        if (empty($sku)) {
            return $this->renderError('很抱歉，该商品规格不存在');
        }
        if ((int)$sku['stock_num'] < 1) {
            return $this->renderError('很抱歉，商品库存不足');
        }
        $actualPrice = (string)$task['actual_price'];
        $totalPrice = $actualPrice;
        $imageId = (int)Db::name('goods_image')->where('goods_id', $task['goods_id'])
            ->order('id', 'asc')->value('image_id');
        $goodsItem = [
            'goods_id' => (int)$task['goods_id'],
            'goods_name' => $goods['goods_name'],
            'goods_image' => BaseModel::getFileUrl($imageId),
            'goods_price' => $actualPrice,
            'total_num' => 1,
            'total_price' => $totalPrice,
            'goods_sku_id' => (int)$sku['goods_sku_id'],
            'spec_sku_id' => (string)$task['spec_sku_id'],
            'goods_type' => (int)$goods['goods_type'],
        ];
        $delivery = (int)($params['delivery'] ?? 10);
        $address = $this->resolveAddress($user, $params, $delivery);
        if ($delivery != 20 && $address === null) {
            return $this->renderError('很抱歉，请先选择收货地址');
        }
        $orderInfo = [
            'goods_list' => [$goodsItem],
            'order_total_num' => 1,
            'order_total_price' => $totalPrice,
            'order_price' => $totalPrice,
            'order_pay_price' => $totalPrice,
            'address' => $address,
            'exist_address' => $address !== null,
            'delivery' => $delivery,
            'express_price' => '0.00',
            'task_id' => $taskId,
            'pay_type' => (int)($params['pay_type'] ?? 50),
        ];
        if ($this->request->isGet()) {
            return $this->renderSuccess($orderInfo);
        }
        try {
            $orderId = $this->createOrder($task, $goods, $sku, $totalPrice, $actualPrice, $params, $delivery, $user, $wxappId);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage() ?: '订单创建失败');
        }
        $order = Db::name('order')->where('order_id', $orderId)->find();
        $payType = (int)$order['pay_type'];
        $payment = null;
        if ($payType == 10) {
            $member = Db::name('user')->where('user_id', $user['user_id'])->find();
            if ((float)$member['balance'] < (float)$order['pay_price']) {
                return $this->renderError('用户余额不足，无法使用余额支付');
            }
            Db::name('user')->where('user_id', $user['user_id'])->dec('balance', $order['pay_price'])->update();
            (new \app\api\model\Order)->onPaySuccess($order['order_no'], $payType, 'balance_' . $order['order_no']);
        } else {
            $payment = (new Payment)->getParams($payType, $order, $user);
        }
        return $this->renderSuccess(['order_id' => $orderId, 'pay_type' => $payType, 'payment' => $payment]);
    }

    private function resolveAddress($user, $params, $delivery)
    {
        if ($delivery == 20) {
            return null;
        }
        $addrRow = Db::name('user_address')->where('user_id', $user['user_id'])
            ->where('address_id', (int)($params['address_id'] ?? 0))->find();
        if (empty($addrRow)) {
            $addrRow = Db::name('user_address')->where('user_id', $user['user_id'])
                ->order('is_default desc, address_id desc')->find();
        }
        if (empty($addrRow)) {
            return null;
        }
        return [
            'address_id' => (int)$addrRow['address_id'],
            'name' => $addrRow['name'], 'phone' => $addrRow['phone'],
            'region' => ['province' => $addrRow['state'], 'city' => $addrRow['city'], 'region' => $addrRow['district']],
            'detail' => $addrRow['detail'],
        ];
    }

    private function createOrder($task, $goods, $sku, $totalPrice, $actualPrice, $params, $delivery, $user, $wxappId)
    {
        return Db::transaction(function () use ($task, $goods, $sku, $totalPrice, $actualPrice, $params, $delivery, $user, $wxappId) {
            $now = time();
            $addrRow = null;
            if ($delivery != 20) {
                $addrRow = Db::name('user_address')->where('user_id', $user['user_id'])
                    ->where('address_id', (int)($params['address_id'] ?? 0))->find();
                if (empty($addrRow)) {
                    $addrRow = Db::name('user_address')->where('user_id', $user['user_id'])
                        ->order('is_default desc, address_id desc')->find();
                }
                if (empty($addrRow)) {
                    throw new \Exception('很抱歉，请先选择收货地址');
                }
            } else {
                if ((int)($params['shop_id'] ?? 0) <= 0) {
                    throw new \Exception('请选择自提门店');
                }
                if (trim((string)($params['linkman'] ?? '')) === '' || trim((string)($params['phone'] ?? '')) === '') {
                    throw new \Exception('请填写自提联系人和电话');
                }
            }
            $orderNo = date('YmdHis') . str_pad(mt_rand(0, 9999), 4, '0');
            $payType = (int)($params['pay_type'] ?? 50);
            $imageId = (int)Db::name('goods_image')->where('goods_id', $goods['goods_id'])->order('id', 'asc')->value('image_id');
            $orderId = Db::name('order')->insertGetId([
                'order_no' => $orderNo,
                'total_price' => $totalPrice, 'order_price' => $totalPrice,
                'coupon_id' => 0, 'coupon_money' => '0.00',
                'points_money' => '0.00', 'points_num' => 0,
                'pay_price' => $totalPrice, 'update_price' => '0.00',
                'buyer_remark' => trim((string)($params['remark'] ?? '')),
                'pay_type' => $payType, 'pay_status' => 10, 'pay_time' => 0,
                'delivery_type' => $delivery,
                'goods_type' => (int)$goods['goods_type'] == 20 ? 20 : 10,
                'virtual_content' => '',
                'extract_shop_id' => $delivery == 20 ? (int)($params['shop_id'] ?? 0) : 0,
                'extract_clerk_id' => 0,
                'express_price' => '0.00', 'express_id' => 0, 'express_company' => '',
                'delivery_id' => 0, 'express_no' => '',
                'delivery_status' => 10, 'delivery_time' => 0,
                'receipt_status' => 10, 'receipt_time' => 0,
                'order_status' => 10, 'points_bonus' => 0, 'is_settled' => 0,
                'transaction_id' => '', 'is_comment' => 0,
                // 20 = 砍价（不可写 30，那是秒杀，会被 sharp:cron 误关）
                'order_source' => OrderSource::BARGAIN,
                'order_source_id' => (int)$task['task_id'],
                'user_id' => $user['user_id'], 'is_delete' => 0,
                'wxapp_id' => $wxappId, 'create_time' => $now, 'update_time' => $now,
                'is_vip' => 0,
            ]);
            if ($delivery != 20) {
                Db::name('order_address')->insert([
                    'name' => $addrRow['name'], 'phone' => $addrRow['phone'],
                    'province_id' => $addrRow['province_id'], 'city_id' => $addrRow['city_id'],
                    'region_id' => $addrRow['region_id'], 'district' => $addrRow['district'],
                    'country' => $addrRow['country'] ?: 'CN',
                    'state' => $addrRow['state'], 'city' => $addrRow['city'], 'detail' => $addrRow['detail'],
                    'order_id' => $orderId, 'user_id' => $user['user_id'],
                    'wxapp_id' => $wxappId, 'create_time' => $now,
                ]);
            } else {
                Db::name('order_extract')->insert([
                    'order_id' => $orderId,
                    'linkman' => trim((string)$params['linkman']),
                    'phone' => trim((string)$params['phone']),
                    'user_id' => $user['user_id'], 'wxapp_id' => $wxappId, 'create_time' => $now,
                ]);
            }
            Db::name('order_goods')->insert([
                'goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'],
                'image_id' => $imageId,
                'deduct_stock_type' => (int)$goods['deduct_stock_type'],
                'spec_type' => (int)$goods['spec_type'],
                'spec_sku_id' => (string)$task['spec_sku_id'],
                'goods_sku_id' => (int)$sku['goods_sku_id'],
                'goods_attr' => '', 'content' => '', 'goods_no' => $sku['goods_no'],
                'goods_price' => $actualPrice, 'line_price' => $sku['line_price'],
                'goods_weight' => $sku['goods_weight'],
                'is_user_grade' => 0, 'grade_ratio' => 0,
                'grade_goods_price' => $actualPrice, 'grade_total_money' => '0.00',
                'coupon_money' => '0.00', 'points_money' => '0.00',
                'points_num' => 0, 'points_bonus' => 0,
                'total_num' => 1, 'total_price' => $totalPrice, 'total_pay_price' => $totalPrice,
                'is_ind_dealer' => 0, 'dealer_money_type' => 10,
                'first_money' => '0.00', 'second_money' => '0.00', 'third_money' => '0.00',
                'is_comment' => 0, 'order_id' => $orderId, 'user_id' => $user['user_id'],
                'goods_source_id' => 0, 'wxapp_id' => $wxappId, 'create_time' => $now,
            ]);
            Db::name('goods_sku')->where('goods_sku_id', $sku['goods_sku_id'])->dec('stock_num', 1)->update();
            Db::name('goods')->where('goods_id', $goods['goods_id'])->inc('sales_actual', 1)->update();
            Db::name('bargain_task')->where('task_id', $task['task_id'])->update(['status' => 0, 'update_time' => $now]);
            return $orderId;
        });
    }
}
