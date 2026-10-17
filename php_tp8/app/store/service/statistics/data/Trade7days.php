<?php

namespace app\store\service\statistics\data;

use app\common\library\helper;
use think\facade\Db;

/**
 * 近7日交易走势
 *
 * 移植自 TP5，调整：不继承 Basics；用 Db 直查 + 显式 wxapp_id（原实现依赖 store Order 模型的
 * getPayOrderTotal/getOrderTotalPrice，php_tp8 未迁移）。
 * 返回值三项均为 JSON 字符串，可直接塞进 echarts 配置（视图已按此契约渲染）。
 */
class Trade7days
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
     * 近7日走势
     * @return array{date:string,order_total:string,order_total_price:string}
     */
    public function getTransactionTrend()
    {
        $lately7days = $this->getLately7days();
        return [
            'date'              => helper::jsonEncode($lately7days),
            'order_total'       => helper::jsonEncode($this->getOrderTotalByDate($lately7days)),
            'order_total_price' => helper::jsonEncode($this->getOrderTotalPriceByDate($lately7days)),
        ];
    }

    /**
     * 最近七天日期（升序）
     * @return array
     */
    private function getLately7days()
    {
        $date = [];
        for ($i = 0; $i < 7; $i++) {
            $date[] = date('Y-m-d', strtotime('-' . $i . ' days'));
        }
        return array_reverse($date);
    }

    /**
     * 每日订单量
     * @param array $days
     * @return array
     */
    private function getOrderTotalByDate($days)
    {
        $data = [];
        foreach ($days as $day) {
            $data[] = number_format($this->buildDayQuery($day)->count());
        }
        return $data;
    }

    /**
     * 每日销售额
     * @param array $days
     * @return array
     */
    private function getOrderTotalPriceByDate($days)
    {
        $data = [];
        foreach ($days as $day) {
            $data[] = helper::number2($this->buildDayQuery($day)->sum('pay_price'));
        }
        return $data;
    }

    /**
     * 指定自然日的已付款订单查询对象
     * @param string $day Y-m-d
     * @return \think\db\Query
     */
    private function buildDayQuery($day)
    {
        $query = Db::name('order')
            ->where('pay_status', self::PAY_STATUS_SUCCESS)
            ->where('order_status', '<>', self::ORDER_STATUS_CANCELLED)
            ->where('is_delete', 0)
            ->where('wxapp_id', $this->wxappId);
        $start = strtotime($day . ' 00:00:00');
        if ($start !== false && $start > 0) {
            $query->where('pay_time', '>=', $start)->where('pay_time', '<', $start + 86400);
        }
        return $query;
    }
}
