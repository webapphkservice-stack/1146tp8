<?php

namespace app\store\controller\statistics;

use app\store\controller\Controller;
use app\store\service\statistics\Data as StatisticsDataService;

/**
 * 数据统计
 * 菜单入口：statistics.data/index（对应 TP5 store/controller/statistics/Data.php）
 *
 * index  渲染数据概况页（含近七日走势、商品销售榜、用户消费榜）
 * survey 数据概况 AJAX 接口（按日期范围重新统计，供页面切换时间后局部刷新）
 */
class Data extends Controller
{
    /* @var StatisticsDataService 数据概况服务类 */
    private $statisticsDataService;

    /**
     * 注意：TP8 用 initialize()（TP5 为 _initialize()）
     */
    public function initialize()
    {
        parent::initialize();
        // 统计口径必须按小程序隔离，避免多个 wxapp 数据串算
        $this->statisticsDataService = new StatisticsDataService(self::getWxappId());
    }

    /**
     * 数据统计主页
     * @return mixed
     */
    public function index()
    {
        return $this->fetch('index', [
            // 数据概况
            'survey'            => $this->statisticsDataService->getSurveyData(),
            // 近七日交易走势（date/order_total/order_total_price，均为 JSON 串）
            'echarts7days'      => $this->statisticsDataService->getTransactionTrend(),
            // 商品销售榜
            'goodsRanking'      => $this->statisticsDataService->getGoodsRanking(),
            // 用户消费榜
            'userExpendRanking' => $this->statisticsDataService->getUserExpendRanking(),
        ]);
    }

    /**
     * 数据概况 API
     * @param string|null $startDate
     * @param string|null $endDate
     * @return mixed
     */
    public function survey($startDate = null, $endDate = null)
    {
        // 兼容：方法参数绑定 + POST 表单两种方式都支持
        $startDate = $startDate ?: $this->request->post('startDate', null);
        $endDate   = $endDate ?: $this->request->post('endDate', null);

        return $this->renderSuccess('', '', $this->statisticsDataService->getSurveyData($startDate, $endDate));
    }
}
