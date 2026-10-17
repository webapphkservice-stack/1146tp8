<link rel="stylesheet" href="https://unpkg.com/element-plus@2/dist/index.css">
<div id="app" v-cloak class="page-statistics-data row-content">
    <!-- 数据概况 -->
    <div class="row">
        <div class="sm:w-full mb-4">
            <div class="widget widget-survey " v-loading="survey.loading">
                <div class="widget-head">
                    <div class="widget-title">数据概况</div>
                    <div class="widget-screen ">
                        <!-- 日期选择器 -->
                        <div class="yxs-date-editor ">
                            <el-date-picker
                                    v-model="survey.dateValue"
                                    type="daterange"
                                    size="small"
                                    @change="onChangeDate"
                                    value-format="yyyy-MM-dd"
                                    range-separator="至"
                                    start-placeholder="开始日期"
                                    end-placeholder="结束日期">
                            </el-date-picker>
                        </div>
                        <!-- 快捷选项 -->
                        <div class="widget-screen_shortcut ">
                            <div class="shortcut-days ">
                                <div class="shortcut-days_item ">
                                    <a href="javascript:void(0);" @click="onFastDate(7)">7天</a>
                                </div>
                                <div class="shortcut-days_item ">
                                    <a href="javascript:void(0);" @click="onFastDate(30)">30天</a>
                                </div>
                                <div class="shortcut-days_item item-clear ">
                                    <a href="javascript:void(0);" @click="onFastDate(0)">清空</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="widget-body">
                    <div class="widget-body-center ">
                        <div class="sm:w-1/2 md:w-1/2 lg:w-1/3">
                            <div class="widget-outline dis-flex flex-y-center">
                                <div class="outline-left">
                                    <img src="assets/store/img/statistics/survey/03.png" alt="">
                                </div>
                                <div class="outline-right dis-flex flex-dir-column flex-x-center">
                                    <div class="item-name">用户数量</div>
                                    <div class="item-value">{{ survey.values.user_total }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="sm:w-1/2 md:w-1/2 lg:w-1/3">
                            <div class="widget-outline dis-flex flex-y-center">
                                <div class="outline-left">
                                    <img src="assets/store/img/statistics/survey/04.png" alt="">
                                </div>
                                <div class="outline-right dis-flex flex-dir-column flex-x-center">
                                    <div class="item-name">付款订单数</div>
                                    <div class="item-value">{{ survey.values.order_total }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="sm:w-1/2 md:w-1/2 lg:w-1/3">
                            <div class="widget-outline dis-flex flex-y-center">
                                <div class="outline-left">
                                    <img src="assets/store/img/statistics/survey/05.png" alt="">
                                </div>
                                <div class="outline-right dis-flex flex-dir-column flex-x-center">
                                    <div class="item-name">商品数量</div>
                                    <div class="item-value">{{ survey.values.goods_total }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="sm:w-1/2 md:w-1/2 lg:w-1/3">
                            <div class="widget-outline dis-flex flex-y-center">
                                <div class="outline-left">
                                    <img src="assets/store/img/statistics/survey/03.png" alt="">
                                </div>
                                <div class="outline-right dis-flex flex-dir-column flex-x-center">
                                    <div class="item-name">消费人数</div>
                                    <div class="item-value">{{ survey.values.consume_users }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="sm:w-1/2 md:w-1/2 lg:w-1/3">
                            <div class="widget-outline dis-flex flex-y-center">
                                <div class="outline-left">
                                    <img src="assets/store/img/statistics/survey/02.png" alt="">
                                </div>
                                <div class="outline-right dis-flex flex-dir-column flex-x-center">
                                    <div class="item-name">付款订单总额</div>
                                    <div class="item-value">{{ survey.values.order_total_money }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="sm:w-1/2 md:w-1/2 lg:w-1/3">
                            <div class="widget-outline dis-flex flex-y-center">
                                <div class="outline-left">
                                    <img src="assets/store/img/statistics/survey/02.png" alt="">
                                </div>
                                <div class="outline-right dis-flex flex-dir-column flex-x-center">
                                    <div class="item-name">用户充值总额</div>
                                    <div class="item-value">{{ survey.values.recharge_total }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 近七日交易走势 -->
    <div class="row">
        <div class="sm:w-full mb-4">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">近七日交易走势</div>
                </div>
                <div class="widget-body ">
                    <div id="echarts-trade" class="widget-echarts"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- 排行榜 -->
    <div class="row">
        <div class="sm:w-1/2 mb-4">
            <div class="widget-ranking widget">
                <div class="widget-head">
                    <div class="widget-title">商品销售榜</div>
                </div>
                <div class="widget-body ">
                    <table width="100%" class="table-base  
                         tpl-table-black">
                        <thead>
                        <tr>
                            <th class="text-center" width="15%">排名</th>
                            <th class="text-left" width="45%">商品</th>
                            <th class="text-center" width="20%">销量</th>
                            <th class="text-center" width="20%">销售额</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, index) in goodsRanking">
                            <td class="align-middle text-center">
                                <div v-if="index < 3 && item.total_sales_num > 0" class="ranking-img">
                                    <img :src="'assets/store/img/statistics/ranking/0' + (index + 1) + '.png'" alt="">
                                </div>
                                <span v-else>{{ index + 1 }}</span>
                            </td>
                            <td class="align-middle">
                                <p class="ranking-item-title truncate">{{ item.goods_name }}</p>
                            </td>
                            <td class="align-middle text-center">{{ item.total_sales_num }}</td>
                            <td class="align-middle text-center">{{ item.sales_volume }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="sm:w-1/2 mb-4">
            <div class="widget-ranking widget">
                <div class="widget-head">
                    <div class="widget-title">用户消费榜</div>
                </div>
                <div class="widget-body ">
                    <table width="100%" class="table-base  
                         tpl-table-black">
                        <thead>
                        <tr>
                            <th class="text-center" width="20%">排名</th>
                            <th class="text-left" width="50%">用户昵称</th>
                            <th class="text-center" width="30%">实际消费金额</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, index) in userExpendRanking">
                            <td class="align-middle text-center">
                                <div v-if="index < 3 && item.expend_money > 0" class="ranking-img">
                                    <img :src="'assets/store/img/statistics/ranking/0' + (index + 1) + '.png'" alt="">
                                </div>
                                <span v-else>{{ index + 1 }}</span>
                            </td>
                            <td class="align-middle">
                                <p class="ranking-item-title truncate">{{ item.nickName }}</p>
                            </td>
                            <td class="align-middle text-center">{{ item.expend_money }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/common/js/echarts.min.js"></script>
<script src="assets/common/js/echarts-walden.js"></script>
<script src="assets/common/js/vue3.min.js?v=1.1.35"></script>
<script src="assets/common/plugins/element-plus/index.full.min.js"></script>
<script src="assets/common/plugins/element-plus/zh-cn.js"></script>

<script type="text/javascript">

    window.__statScriptRan = true;
    console.log('[stats] template script running');
    var app = Vue.createApp({
        data() {
            return {
            // 数据概况
            survey: {
                loading: false,
                dateValue: [],
                values: <?= \app\common\library\helper::jsonEncode($survey) ?>
            },
            // 商品销售榜
            goodsRanking: <?= \app\common\library\helper::jsonEncode($goodsRanking) ?>,
            // 用户消费榜
            userExpendRanking: <?= \app\common\library\helper::jsonEncode($userExpendRanking) ?>
            }
        },

        mounted() {
            // 近七日交易走势
            this.drawLine();
        },

        methods: {

            // 监听事件：日期选择快捷导航
            onFastDate: function (days) {
                var startDate, endDate;
                // 清空日期
                if (days === 0) {
                    this.survey.dateValue = [];
                } else {
                    startDate = $.getDay(-days);
                    endDate = $.getDay(0);
                    this.survey.dateValue = [startDate, endDate];
                }
                // api: 获取数据概况
                this.__getApiData__survey(startDate, endDate);
            },

            // 监听事件：日期选择框改变
            onChangeDate: function (e) {
                // api: 获取数据概况
                this.__getApiData__survey(e[0], e[1]);
            },

            // 获取数据概况
            __getApiData__survey: function (startDate, endDate) {
                var app = this;
                // 请求api数据
                app.survey.loading = true;
                // api地址
                var url = '<?= url('statistics.data/survey') ?>';
                $.post(url, {
                    startDate: startDate,
                    endDate: endDate
                }, function (result) {
                    app.survey.values = result.data;
                    app.survey.loading = false;
                });
            },

            /**
             * 近七日交易走势
             * @type {HTMLElement}
             */
            drawLine() {
                var dom = document.getElementById('echarts-trade');
                echarts.init(dom, 'walden').setOption({
                    tooltip: {
                        trigger: 'axis'
                    },
                    legend: {
                        data: ['成交量', '成交额']
                    },
                    toolbox: {
                        show: true,
                        showTitle: false,
                        feature: {
                            mark: {show: true},
                            magicType: {show: true, type: ['line', 'bar']}
                        }
                    },
                    calculable: true,
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: <?= $echarts7days['date'] ?>
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [
                        {
                            name: '成交额',
                            type: 'line',
                            data: <?= $echarts7days['order_total_price'] ?>
                        },
                        {
                            name: '成交量',
                            type: 'line',
                            data: <?= $echarts7days['order_total'] ?>
                        }
                    ]
                }, true);
            }

        }

    });
    app.use(ElementPlus, { locale: ElementPlusLocaleZhCn });
    try {
      app.use(ElementPlus, { locale: ElementPlusLocaleZhCn });
      app.mount('#app');
      console.log('[stats] mount OK', !!document.querySelector('#app').__vue_app__);
    } catch(e) {
      console.error('[stats] mount FAILED', e);
    }

</script>