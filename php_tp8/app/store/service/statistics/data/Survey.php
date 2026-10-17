<?php

namespace app\store\service\statistics\data;

use app\common\library\helper;
use think\facade\Db;

/**
 * 数据概况
 *
 * 移植自 TP5 store/service/statistics/data/Survey.php，针对 php_tp8 的调整：
 *  1. 不继承 app\common\service\Basics（TP8 未迁移）。
 *  2. 全部改用 Db 直查并显式带 wxapp_id：原实现依赖 store 模型的「wxapp_id 全局作用域」，
 *     而 php_tp8 的 store Order 模型未迁移 getPayOrderTotal/getOrderTotalPrice 两个统计方法。
 *  3. 原依赖的三个枚举（order/Status、order/PayStatus、recharge/order/PayStatus）TP8 不存在，
 *     改为类常量，避免为 3 个常量新建枚举文件。
 *  4. 「消费人数」修正为 DISTINCT user_id 后计数（原 group + count 统计的是分组数，语义不同）。
 */
class Survey
{
    /** 订单支付状态：20 已付款 */
    private const PAY_STATUS_SUCCESS = 20;
    /** 订单状态：20 已取消 */
    private const ORDER_STATUS_CANCELLED = 20;
    /** 充值订单支付状态：20 已支付 */
    private const RECHARGE_PAY_SUCCESS = 20;

    /** @var int */
    private $wxappId;

    public function __construct($wxappId)
    {
        $this->wxappId = (int)$wxappId;
    }

    /**
     * 获取数据概况
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getSurveyData($startDate = null, $endDate = null)
    {
        return [
            // 用户数量
            'user_total'        => $this->getUserTotal($startDate, $endDate),
            // 消费人数
            'consume_users'     => $this->getConsumeUsers($startDate, $endDate),
            // 付款订单数
            'order_total'       => $this->getOrderTotal($startDate, $endDate),
            // 付款订单总额
            'order_total_money' => $this->getOrderTotalMoney($startDate, $endDate),
            // 商品总量
            'goods_total'       => $this->getGoodsTotal($startDate, $endDate),
            // 用户充值总额
            'recharge_total'    => $this->getRechargeTotal($startDate, $endDate),
        ];
    }

    /**
     * 用户总量（按注册时间筛选）
     */
    private function getUserTotal($startDate = null, $endDate = null)
    {
        $query = Db::name('user')
            ->where('is_delete', 0)
            ->where('wxapp_id', $this->wxappId);
        $this->applyDateRange($query, 'create_time', $startDate, $endDate);
        return number_format($query->count());
    }

    /**
     * 消费人数（去重且有支付订单）
     */
    private function getConsumeUsers($startDate = null, $endDate = null)
    {
        $query = $this->paidOrderQuery();
        $this->applyDateRange($query, 'pay_time', $startDate, $endDate);
        $userIds = $query->distinct(true)->column('user_id');
        return number_format(count($userIds));
    }

    /**
     * 付款订单数
     */
    private function getOrderTotal($startDate = null, $endDate = null)
    {
        $query = $this->paidOrderQuery();
        $this->applyDateRange($query, 'pay_time', $startDate, $endDate);
        return number_format($query->count());
    }

    /**
     * 付款订单总额
     */
    private function getOrderTotalMoney($startDate = null, $endDate = null)
    {
        $query = $this->paidOrderQuery();
        $this->applyDateRange($query, 'pay_time', $startDate, $endDate);
        return helper::number2($query->sum('pay_price'));
    }

    /**
     * 商品总量（按创建时间筛选）
     */
    private function getGoodsTotal($startDate = null, $endDate = null)
    {
        $query = Db::name('goods')
            ->where('is_delete', 0)
            ->where('wxapp_id', $this->wxappId);
        $this->applyDateRange($query, 'create_time', $startDate, $endDate);
        return number_format($query->count());
    }

    /**
     * 用户充值总额
     */
    private function getRechargeTotal($startDate = null, $endDate = null)
    {
        $query = Db::name('recharge_order')
            ->where('pay_status', self::RECHARGE_PAY_SUCCESS)
            ->where('wxapp_id', $this->wxappId);
        $this->applyDateRange($query, 'pay_time', $startDate, $endDate);
        return helper::number2($query->sum('actual_money'));
    }

    /**
     * 已付款且未取消的订单查询对象
     * @return \think\db\Query
     */
    private function paidOrderQuery()
    {
        return Db::name('order')
            ->where('pay_status', self::PAY_STATUS_SUCCESS)
            ->where('order_status', '<>', self::ORDER_STATUS_CANCELLED)
            ->where('is_delete', 0)
            ->where('wxapp_id', $this->wxappId);
    }

    /**
     * 按日期区间过滤（闭区间，含起止当天）
     * @param \think\db\Query $query
     * @param string          $field
     * @param string|null     $startDate
     * @param string|null     $endDate
     * @return \think\db\Query
     */
    private function applyDateRange($query, $field, $startDate, $endDate)
    {
        if ($startDate === null || $endDate === null || $startDate === '' || $endDate === '') {
            return $query;
        }
        $start = strtotime((string)$startDate);
        $end   = strtotime((string)$endDate);
        if ($start === false || $end === false || $start <= 0 || $end <= 0) {
            return $query;
        }
        return $query->where($field, '>=', $start)->where($field, '<', $end + 86400);
    }
}
