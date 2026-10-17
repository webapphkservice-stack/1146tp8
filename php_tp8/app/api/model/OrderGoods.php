<?php
namespace app\api\model;

use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 订单商品模型（轻量版，直查 order_goods；用于退款/好友代付取快照）
 */
class OrderGoods
{
    /**
     * 订单商品详情（含商品快照）
     * @param int $orderGoodsId
     * @return array|null
     */
    public static function detail($orderGoodsId)
    {
        $row = Db::name('order_goods')->where('order_goods_id', $orderGoodsId)->find();
        if (!$row) {
            return null;
        }
        foreach (['total_price', 'total_pay_price', 'goods_price', 'total_num'] as $k) {
            if (isset($row[$k])) {
                $row[$k] = is_numeric($row[$k]) ? (float)$row[$k] : $row[$k];
            }
        }
        $goods = Db::name('goods')->where('goods_id', $row['goods_id'])
            ->field('goods_name')->find() ?: [];
        // 商品主图快照存放在 order_goods.image_id（goods 表无 image 列），一并回填
        $goods['image_id'] = $row['image_id'] ?? 0;
        $row['goods'] = $goods;
        return $row;
    }

    /**
     * 待评价订单商品列表（is_comment = 0）
     */
    public static function getNotCommentGoodsList($orderId)
    {
        $rows = Db::name('order_goods')
            ->where('order_id', $orderId)
            ->where('is_comment', 0)
            ->order('order_goods_id', 'asc')
            ->select()->toArray();
        foreach ($rows as &$g) {
            $g['image'] = BaseModel::getFileUrl($g['image_id']);
        }
        return $rows;
    }
}
