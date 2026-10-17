<?php

namespace app\api\service\sharp;

use app\common\enum\order\OrderSource;
use think\facade\Db;

/**
 * 秒杀订单「支付成功 / 取消」的库存与销量钩子
 * 对应 php_my：
 *  - common/service/goods/source/Sharp::updateStockSales（付款后：加销量 + 扣付款减库存）
 *  - common/service/goods/source/Sharp::backGoodsStock（取消：回退下单减库存）
 *
 * 由 app/api/service/OrderStateMachine 在 paySuccess / cancel 时按 order_source 分派调用。
 */
class PaySuccess
{
    /**
     * 秒杀订单支付成功后：
     *   1) 增加 sharp_goods.total_sales 与 sharp_active_goods.sales_actual（销量，无论减库存方式）
     *   2) 付款减库存（deduct_stock_type=20）时，此时扣减 seckill_stock
     * @param array $order 订单行（需含 order_id / order_source / order_source_id）
     */
    public static function onPaySuccess($order)
    {
        $goodsList = Db::name('order_goods')
            ->where('order_id', $order['order_id'])
            ->select()->toArray();
        foreach ($goodsList as $g) {
            $sharpGoodsId = (int)$g['goods_source_id'];
            if ($sharpGoodsId <= 0) {
                continue;
            }
            // 销量累计
            Db::name('sharp_goods')->where('sharp_goods_id', $sharpGoodsId)
                ->inc('total_sales', $g['total_num'])->update();
            Db::name('sharp_active_goods')
                ->where('active_time_id', $order['order_source_id'])
                ->where('sharp_goods_id', $sharpGoodsId)
                ->inc('sales_actual', $g['total_num'])->update();
            // 付款减库存：此时扣 seckill_stock
            if ((int)$g['deduct_stock_type'] === 20) {
                Db::name('sharp_goods')->where('sharp_goods_id', $sharpGoodsId)
                    ->dec('seckill_stock', $g['total_num'])->update();
                Db::name('sharp_goods_sku')
                    ->where('sharp_goods_id', $sharpGoodsId)
                    ->where('spec_sku_id', $g['spec_sku_id'])
                    ->dec('seckill_stock', $g['total_num'])->update();
            }
        }
    }

    /**
     * 秒杀订单取消（未付款）：仅「下单减库存」(deduct_stock_type=10) 回退 seckill_stock
     * @param array $order 订单行（需含 order_id）
     */
    public static function onCancel($order)
    {
        $goodsList = Db::name('order_goods')
            ->where('order_id', $order['order_id'])
            ->select()->toArray();
        foreach ($goodsList as $g) {
            $sharpGoodsId = (int)$g['goods_source_id'];
            if ($sharpGoodsId <= 0) {
                continue;
            }
            if ((int)$g['deduct_stock_type'] === 10) {
                Db::name('sharp_goods')->where('sharp_goods_id', $sharpGoodsId)
                    ->inc('seckill_stock', $g['total_num'])->update();
                Db::name('sharp_goods_sku')
                    ->where('sharp_goods_id', $sharpGoodsId)
                    ->where('spec_sku_id', $g['spec_sku_id'])
                    ->inc('seckill_stock', $g['total_num'])->update();
            }
        }
    }
}
