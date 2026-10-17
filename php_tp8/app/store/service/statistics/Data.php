<?php

namespace app\store\service\statistics;

use app\store\service\statistics\data\GoodsRanking;
use app\store\service\statistics\data\Survey;
use app\store\service\statistics\data\Trade7days;
use app\store\service\statistics\data\UserExpendRanking;

/**
 * 数据概况服务类
 *
 * 移植自 php/source/application/store/service/statistics/Data.php，针对 php_tp8 调整：
 *  1. 不再继承 app\common\service\Basics（TP8 未迁移该基类，且其本身无逻辑）。
 *  2. 显式注入 wxapp_id：php_tp8 的 Db 直查不会自动带小程序隔离，必须手动限定。
 *  3. 修正原实现的方法名拼写错误 geUserExpendRanking -> getUserExpendRanking。
 */
class Data
{
    /** @var int */
    private $wxappId;

    public function __construct($wxappId)
    {
        $this->wxappId = (int)$wxappId;
    }

    /**
     * 数据概况
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getSurveyData($startDate = null, $endDate = null)
    {
        return (new Survey($this->wxappId))->getSurveyData($startDate, $endDate);
    }

    /**
     * 近七日交易走势
     * @return array{date:string,order_total:string,order_total_price:string} 值为 JSON 字符串
     */
    public function getTransactionTrend()
    {
        return (new Trade7days($this->wxappId))->getTransactionTrend();
    }

    /**
     * 商品销售榜 Top10
     * @return \think\Collection
     */
    public function getGoodsRanking()
    {
        return (new GoodsRanking($this->wxappId))->getGoodsRanking();
    }

    /**
     * 用户消费榜 Top10
     * @return \think\Collection
     */
    public function getUserExpendRanking()
    {
        return (new UserExpendRanking($this->wxappId))->getUserExpendRanking();
    }
}
