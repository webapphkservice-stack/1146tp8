<?php
namespace app\api\model;

use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 售后/退款模型（轻量版，直查 order_refund / order_refund_image）
 * 对齐 RN 订单详情页退款流程（php_my user/Refund + OrderRefund）
 */
class OrderRefund
{
    /**
     * 退款列表
     * @param int   $userId
     * @param mixed $state ''=全部 10=已完成 20=进行中 30=已取消
     * @return array
     */
    public static function getList($userId, $state)
    {
        $q = Db::name('order_refund')
            ->where('user_id', $userId)
            ->where('wxapp_id', BaseModel::getWxappId());
        if ($state !== '' && $state !== null) {
            if ($state == 30) {
                $q->where('status', 30);
            } elseif ($state == 10) {
                $q->where('status', 20);
            } elseif ($state == 20) {
                $q->where('status', 0);
            }
        }
        $rows = $q->order('create_time', 'desc')->select()->toArray();
        foreach ($rows as &$r) {
            $r['refund_money'] = (float)$r['refund_money'];
            $r['order_no'] = Db::name('order')->where('order_id', $r['order_id'])->value('order_no') ?: '';
            $goods = Db::name('order_goods')->where('order_goods_id', $r['order_goods_id'])
                ->field('goods_name, image_id, total_num, total_pay_price')->find();
            $r['goods'] = $goods ?: [];
        }
        return $rows;
    }

    /**
     * 提交退款申请
     * @param array $user
     * @param array $orderGoods OrderGoods::detail 结果（含 order_id/order_goods_id/total_pay_price/goods）
     * @param array $formData
     * @return int 新退款单id
     */
    public static function apply($user, $orderGoods, $formData)
    {
        $wxappId = BaseModel::getWxappId();
        $refundMoney = (float)($formData['refund_money'] ?? ($orderGoods['total_pay_price'] ?? 0));
        $data = [
            'order_id'        => $orderGoods['order_id'],
            'order_goods_id'  => $orderGoods['order_goods_id'],
            'user_id'         => $user['user_id'],
            'type'            => (int)($formData['type'] ?? 10),
            'apply_desc'      => $formData['apply_desc'] ?? '',
            'refund_money'    => $refundMoney,
            'is_agree'        => 0,
            'status'          => 0,
            'wxapp_id'        => $wxappId,
            'create_time'     => time(),
            'update_time'     => time(),
        ];
        $refundId = Db::name('order_refund')->insertGetId($data);
        if (!empty($formData['images']) && is_array($formData['images'])) {
            $imgRows = [];
            foreach ($formData['images'] as $imgId) {
                if (!$imgId) {
                    continue;
                }
                $imgRows[] = [
                    'order_refund_id' => $refundId,
                    'image_id'        => $imgId,
                    'wxapp_id'        => $wxappId,
                    'create_time'     => time(),
                ];
            }
            if ($imgRows) {
                Db::name('order_refund_image')->insertAll($imgRows);
            }
        }
        return $refundId;
    }

    /** 退款详情（含订单、商品、图片、物流公司列表） */
    public static function detail($refundId, $userId)
    {
        $row = Db::name('order_refund')
            ->where('order_refund_id', $refundId)
            ->where('user_id', $userId)
            ->find();
        if (!$row) {
            return null;
        }
        $row['refund_money'] = (float)$row['refund_money'];
        $row['order'] = Db::name('order')->where('order_id', $row['order_id'])
            ->field('order_no, pay_price, pay_status')->find() ?: [];
        $row['goods'] = Db::name('order_goods')->where('order_goods_id', $row['order_goods_id'])
            ->field('goods_name, image_id, total_num, total_pay_price, total_price')->find() ?: [];
        $row['image_id'] = Db::name('order_refund_image')->where('order_refund_id', $refundId)->column('image_id');
        return $row;
    }

    /** 用户回寄物流 */
    public static function delivery($data)
    {
        $up = [
            'is_user_send' => 1,
            'send_time'    => time(),
            'express_id'   => $data['express_id'] ?? '',
            'express_no'   => $data['express_no'] ?? '',
            'update_time'  => time(),
        ];
        return Db::name('order_refund')->where('order_refund_id', $data['order_refund_id'])->update($up) !== false;
    }

    /** 可退金额（取实付） */
    public static function getRefundMoney($orderGoods)
    {
        return (float)($orderGoods['total_pay_price'] ?? 0);
    }
}
