<?php

namespace app\api\controller\shop;

use app\api\controller\Controller;
use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 自提订单管理（门店端，完整移植 php api/controller/shop/Order）
 * 小程序调用点（商家页面 pages/store）：shop.order/detail、shop.order/extract
 */
class Order extends Controller
{
    private function getClerk($userId)
    {
        return Db::name('store_shop_clerk')->where('user_id', $userId)
            ->where('status', 1)->where('is_delete', 0)->find();
    }

    /**
     * 核销订单详情（校验店员身份）
     */
    public function detail()
    {
        $user = $this->getUser(true);
        $orderId = (int)$this->request->param('order_id', 0);
        $clerk = $this->getClerk((int)$user['user_id']);
        if (empty($clerk)) {
            return $this->renderError('您不是门店核销员');
        }
        $order = Db::name('order')->where('order_id', $orderId)
            ->where('is_delete', 0)->where('wxapp_id', $this->getWxappId())->find();
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        $goods = Db::name('order_goods')->where('order_id', $orderId)->select()->toArray();
        foreach ($goods as &$g) {
            $g['image'] = BaseModel::getFileUrl($g['image_id']);
        }
        unset($g);
        $extract = Db::name('order_extract')->where('order_id', $orderId)->find();
        $shop = Db::name('store_shop')->where('shop_id', $clerk['shop_id'])->find();
        return $this->renderSuccess([
            'order' => [
                'order_id' => (int)$order['order_id'],
                'order_no' => $order['order_no'],
                'pay_price' => $order['pay_price'],
                'pay_status' => (int)$order['pay_status'],
                'delivery_type' => (int)$order['delivery_type'],
                'extract_shop_id' => (int)$order['extract_shop_id'],
                'order_source' => (int)$order['order_source'],
                'order_status' => (int)$order['order_status'],
                'goods' => $goods,
                'extract' => $extract ? ['linkman' => $extract['linkman'], 'phone' => $extract['phone']] : null,
                'clerk' => [
                    'clerk_id' => (int)$clerk['clerk_id'],
                    'real_name' => $clerk['real_name'],
                    'shop_name' => $shop['shop_name'] ?? '',
                ],
            ],
        ]);
    }

    /**
     * 确认核销（店员身份 + 门店匹配 + 已支付未发货 → 完成）
     */
    public function extract()
    {
        $user = $this->getUser(true);
        $orderId = (int)$this->request->param('order_id', 0);
        $clerk = $this->getClerk((int)$user['user_id']);
        if (empty($clerk)) {
            return $this->renderError('您不是门店核销员');
        }
        $order = Db::name('order')->where('order_id', $orderId)
            ->where('is_delete', 0)->where('wxapp_id', $this->getWxappId())->find();
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        if ((int)$order['extract_shop_id'] !== (int)$clerk['shop_id']) {
            return $this->renderError('该订单不属于您所在门店，无法核销');
        }
        if ((int)$order['delivery_type'] != 20) {
            return $this->renderError('该订单不是自提订单');
        }
        if ((int)$order['pay_status'] != 20) {
            return $this->renderError('该订单未支付');
        }
        if ((int)$order['delivery_status'] == 20) {
            return $this->renderError('该订单已核销，请勿重复操作');
        }
        $now = time();
        Db::startTrans();
        try {
            Db::name('order')->where('order_id', $orderId)->update([
                'delivery_status' => 20, 'delivery_time' => $now,
                'receipt_status' => 20, 'receipt_time' => $now,
                'order_status' => 30,
                'extract_clerk_id' => (int)$clerk['clerk_id'],
                'update_time' => $now,
            ]);
            Db::name('store_shop_order')->insert([
                'order_id' => $orderId,
                'order_type' => 10,
                'shop_id' => (int)$clerk['shop_id'],
                'clerk_id' => (int)$clerk['clerk_id'],
                'wxapp_id' => $this->getWxappId(),
                'create_time' => $now,
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return $this->renderError('核销失败');
        }
        return $this->renderSuccess([], '订单核销成功');
    }
}
