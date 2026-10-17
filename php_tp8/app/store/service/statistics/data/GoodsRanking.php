<?php

namespace app\store\service\statistics\data;

use think\facade\Db;

/**
 * 商品销售榜 Top10
 *
 * 移植自 TP5，关键调整（重要）：
 *  原实现用 store OrderGoods 模型做 JOIN，php_tp8 无该模型；更关键的是
 *  **order_goods 与 order 两张表都有 wxapp_id 列**，若走模型会自动注入裸列
 *  where('wxapp_id', ...)，导致 SQL 报 "Column 'wxapp_id' in where clause is ambiguous"。
 *  因此这里用 Db 直查（不套模型全局作用域），并显式限定 order.wxapp_id。
 */
class GoodsRanking
{
    /** 订单支付状态：20 已付款 */
    private const PAY_STATUS_SUCCESS = 20;
    /** 订单状态：20 已取消 */
    private const ORDER_STATUS_CANCELLED = 20;

    /** @var int */
    private $wxappId;

    public function __construct($wxappId)
    {
        $this->wxappId = (int)$wxappId;
    }

    /**
     * 商品销售榜
     * @return \think\Collection 每项含 goods_id/goods_name/sales_volume/total_sales_num
     */
    public function getGoodsRanking()
    {
        return Db::name('order_goods')->alias('o_goods')
            // field 用字符串：数组形式会把 SUM(...) 当作普通字段加反引号，导致 SQL 报错
            ->field('o_goods.goods_id, o_goods.goods_name'
                . ', SUM(o_goods.total_pay_price) AS sales_volume'
                . ', SUM(o_goods.total_num) AS total_sales_num')
            ->join('order', 'order.order_id = o_goods.order_id')
            ->where('order.pay_status', self::PAY_STATUS_SUCCESS)
            ->where('order.order_status', '<>', self::ORDER_STATUS_CANCELLED)
            ->where('order.wxapp_id', $this->wxappId)
            ->group('o_goods.goods_id, o_goods.goods_name')
            // 按销售额排序；若需按销量排序改为 order('total_sales_num', 'desc')
            ->order('sales_volume', 'desc')
            ->limit(10)
            ->select();
    }
}
