<?php
namespace app\common\library;

use app\api\model\LocalDelivery;
use app\common\model\Setting;
use app\common\model\DeliveryNotifyJob;
use app\common\model\DeliveryOrder;

/**
 * 外送订单商户侧 webhook 回写队列（移植自 TP5）
 * 负责：构造回写数据/签名，并将回写任务入队（异步由 task 模块消费）
 */
class DeliveryWebhookQueue
{
    /**
     * 将订单确认收货事件入队（异步回写商户侧），保证两端状态最终一致
     * @param DeliveryOrder $order
     * @return bool
     */
    public static function push(DeliveryOrder $order)
    {
        // 已成功回写则跳过
        if ($order->getAttr('notify_status') == 1) {
            return true;
        }
        $delivery = LocalDelivery::detail($order->getAttr('delivery_id'));
        $notifyUrl = !empty($delivery) ? trim($delivery['notify_url'] ?? '') : '';
        if ($notifyUrl === '') {
            // 未配置商户回写地址，标记无需回写
            DeliveryOrder::update([
                'notify_status' => 0,
                'notify_time'   => time(),
            ], ['order_id' => $order->getAttr('order_id')]);
            return true;
        }

        $secret  = self::getSecret($order->getAttr('wxapp_id'));
        $payload = self::buildPayload($order);
        $payload['sign'] = self::buildSign($payload, $secret);

        $jobId = (new DeliveryNotifyJob)->addJob([
            'order_id'     => $order->getAttr('order_id'),
            'wxapp_id'     => $order->getAttr('wxapp_id'),
            'notify_url'   => $notifyUrl,
            'payload'      => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status'       => 0,
            'attempts'     => 0,
            'next_time'    => time(),
            'create_time'  => time(),
            'update_time'  => time(),
        ]);
        return $jobId > 0;
    }

    /**
     * 构造商户侧 webhook 回写数据
     * @param DeliveryOrder $order
     * @return array
     */
    public static function buildPayload(DeliveryOrder $order)
    {
        return [
            'order_id'        => $order->getAttr('order_id'),
            'order_no'        => $order->getAttr('order_no'),
            'delivery_id'     => $order->getAttr('delivery_id'),
            'delivery_title'  => $order->getAttr('delivery_title'),
            'status'          => $order->getAttr('status'),
            'status_text'     => $order->getAttr('status_text'),
            'receiver_name'   => $order->getAttr('receiver_name'),
            'receiver_phone'  => $order->getAttr('receiver_phone'),
            'receiver_region' => $order->getAttr('receiver_region'),
            'receiver_detail' => $order->getAttr('receiver_detail'),
            'goods_price'     => $order->getAttr('goods_price'),
            'delivery_fee'    => $order->getAttr('delivery_fee'),
            'total_price'     => $order->getAttr('total_price'),
            'timestamp'       => time(),
        ];
    }

    /**
     * 生成签名 sign = md5(排序后的参数串 + 密钥)
     * @param array $data
     * @param string $secret
     * @return string
     */
    public static function buildSign(array $data, $secret)
    {
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }
        return md5(rtrim($str, '&') . $secret);
    }

    /**
     * 获取商户侧 webhook 签名密钥（来自外送设置，缺省使用内置常量）
     * @param int $wxappId
     * @return string
     */
    public static function getSecret($wxappId)
    {
        try {
            $setting = Setting::getItem('delivery');
        } catch (\Exception $e) {
            $setting = [];
        }
        return !empty($setting['webhook_secret']) ? $setting['webhook_secret'] : 'yoshop_delivery_webhook';
    }
}
