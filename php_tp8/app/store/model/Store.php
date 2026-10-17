<?php
namespace app\store\model;

use app\common\library\helper;
use think\facade\Db;

/**
 * 商城（后台首页统计）模型
 *
 * 移植自 TP5 store/model/Store.php。原实现委托给 Goods/Order/User/Comment 四个模型，
 * 这些模型尚未整体迁移；此处用 Db 直查复刻完全一致的统计口径，
 * 保证 getHomeData() 输出结构与 TP5 一致（widget-card / widget-outline / widget-echarts），
 * 后续各模型迁移到位后可改为委托调用。
 */
class Store
{
    /** @var int 当前商城 id */
    private $wxappId;

    public function __construct($wxappId = 0)
    {
        $this->wxappId = (int)$wxappId;
    }

    /**
     * 后台首页数据
     * @return array
     */
    public function getHomeData()
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        // 最近七天日期
        $lately7days = $this->getLately7days();
        return [
            'widget-card' => [
                'goods_total'   => $this->getGoodsTotal(),
                'user_total'    => $this->getUserTotal(),
                'order_total'   => $this->getOrderTotal(),
                'comment_total' => $this->getCommentTotal(),
            ],
            'widget-outline' => [
                'order_total_price' => [
                    'tday' => $this->getOrderTotalPrice($today),
                    'ytd'  => $this->getOrderTotalPrice($yesterday),
                ],
                'order_total' => [
                    'tday' => $this->getOrderTotal($today),
                    'ytd'  => $this->getOrderTotal($yesterday),
                ],
                'new_user_total' => [
                    'tday' => $this->getUserTotal($today),
                    'ytd'  => $this->getUserTotal($yesterday),
                ],
                'order_user_total' => [
                    'tday' => $this->getPayOrderUserTotal($today),
                    'ytd'  => $this->getPayOrderUserTotal($yesterday),
                ],
            ],
            'widget-echarts' => [
                'date'              => helper::jsonEncode($lately7days),
                'order_total'       => helper::jsonEncode($this->getOrderTotalByDate($lately7days)),
                'order_total_price' => helper::jsonEncode($this->getOrderTotalPriceByDate($lately7days)),
            ],
        ];
    }

    /**
     * 最近七天日期
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

    /** 商品总量 */
    private function getGoodsTotal()
    {
        return number_format($this->countTable('goods', ['is_delete' => 0]));
    }

    /** 用户总量（$day 为空统计全部，否则统计当天新增） */
    private function getUserTotal($day = null)
    {
        return number_format($this->countTable('user', ['is_delete' => 0], $day));
    }

    /** 支付订单总量 */
    private function getOrderTotal($day = null)
    {
        return number_format($this->countPayOrder($day));
    }

    /** 按日期统计订单数 */
    private function getOrderTotalByDate($days)
    {
        $data = [];
        foreach ($days as $day) {
            $data[] = $this->getOrderTotal($day);
        }
        return $data;
    }

    /** 评价总量 */
    private function getCommentTotal()
    {
        return number_format($this->countTable('comment', ['is_delete' => 0]));
    }

    /** 某天总销售额 */
    private function getOrderTotalPrice($day = null)
    {
        return helper::number2($this->sumPayOrderPrice($day));
    }

    /** 按日期统计销售额 */
    private function getOrderTotalPriceByDate($days)
    {
        $data = [];
        foreach ($days as $day) {
            $data[] = $this->getOrderTotalPrice($day);
        }
        return $data;
    }

    /** 某天下单用户数 */
    private function getPayOrderUserTotal($day)
    {
        return number_format($this->countPayOrderUser($day));
    }

    /**
     * 通用计数（自动附加 wxapp_id）
     */
    private function countTable($table, $where = [], $day = null)
    {
        $query = Db::name($table)->where($where);
        if ($this->wxappId > 0) {
            $query->where('wxapp_id', $this->wxappId);
        }
        if (!is_null($day)) {
            $start = strtotime($day);
            $query->where('create_time', '>=', $start)->where('create_time', '<', $start + 86400);
        }
        return (int)$query->count();
    }

    /**
     * 已支付订单查询（pay_status=20 已支付，order_status<>20 未取消）
     */
    private function payOrderQuery($day = null)
    {
        // 与 TP5 Order::getOrderTotal() 口径一致：必须排除已软删订单，
        // 否则首页统计（订单数/销售额/下单用户数）会把已删除订单算进去
        $query = Db::name('order')
            ->where('pay_status', 20)
            ->where('order_status', '<>', 20)
            ->where('is_delete', 0);
        if ($this->wxappId > 0) {
            $query->where('wxapp_id', $this->wxappId);
        }
        if (!is_null($day)) {
            $start = strtotime($day);
            $query->where('pay_time', '>=', $start)->where('pay_time', '<', $start + 86400);
        }
        return $query;
    }

    private function countPayOrder($day = null)
    {
        return (int)$this->payOrderQuery($day)->count();
    }

    private function sumPayOrderPrice($day = null)
    {
        return (float)$this->payOrderQuery($day)->sum('pay_price');
    }

    private function countPayOrderUser($day = null)
    {
        // distinct(true)->count('user_id') 未必生成 COUNT(DISTINCT user_id)，
        // 这里与 Order::getPayOrderUserTotal() 保持一致：取列后计数
        return count($this->payOrderQuery($day)->distinct(true)->column('user_id'));
    }
}
