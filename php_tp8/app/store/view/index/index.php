<style>
/* 商城统计 / 实时概况 间距整体缩小 50%（仅作用于首页 .page-home） */
.page-home.row-content { padding: 12px; }
.page-home .widget { margin-bottom: 10px; }
.page-home .widget-head { padding: 8px 11px; }
.page-home .widget-body { padding: 0 11px 11px; }
.page-home .widget-body:has(> [class*="w-1/4"]),
.page-home .widget-body:has(> [class*="w-1/3"]) { gap: 8px; }
.page-home .widget-card { padding: 11px; margin-bottom: 10px; }
.page-home .widget-outline { padding: 7px 9px; }
.page-home .card-header { padding-bottom: 8px; margin-bottom: 8px; gap: 6px; }
.page-home .card-value-row { gap: 6px; }
.page-home > .row { gap: 10px; }

/* 图表标题栏右侧图例 */
.page-home .widget--chart .widget-head { justify-content: space-between; }
.chart-legend { display: inline-flex; align-items: center; gap: 14px; font-size: 12px; color: #6b7280; }
.chart-legend__item { display: inline-flex; align-items: center; gap: 6px; }
.chart-legend__item i {
    display: inline-block; width: 18px; height: 4px; border-radius: 2px;
}
</style>
<div class="page-home row-content">

    <!-- 商城统计 -->
    <div class="row">
        <div class="w-full mb-4">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">商城统计</div>
                </div>
                <div class="widget-body ">
                    <div class="sm:w-full md:w-1/2 lg:w-1/4">
                        <div class="widget-card card__blue ">
                            <div class="card-header">商品总量</div>
                            <div class="card-body">
                                <div class="card-value-row">
                                    <div class="card-value"><?= $data['widget-card']['goods_total'] ?></div>
                                    <span class="card-icon iconfont icon-goods"></span>
                                </div>
                                <div class="card-description">当前商品总数量</div>
                            </div>
                        </div>
                    </div>

                    <div class="sm:w-full md:w-1/2 lg:w-1/4">
                        <div class="widget-card card__red ">
                            <div class="card-header">用户总量</div>
                            <div class="card-body">
                                <div class="card-value-row">
                                    <div class="card-value"><?= $data['widget-card']['user_total'] ?></div>
                                    <span class="card-icon iconfont icon-user"></span>
                                </div>
                                <div class="card-description">当前用户总数量</div>
                            </div>
                        </div>
                    </div>

                    <div class="sm:w-full md:w-1/2 lg:w-1/4">
                        <div class="widget-card card__blue ">
                            <div class="card-header">订单总量</div>
                            <div class="card-body">
                                <div class="card-value-row">
                                    <div class="card-value"><?= $data['widget-card']['order_total'] ?></div>
                                    <span class="card-icon iconfont icon-order"></span>
                                </div>
                                <div class="card-description">已付款订单总数量</div>
                            </div>
                        </div>
                    </div>

                    <div class="sm:w-full md:w-1/2 lg:w-1/4">
                        <div class="widget-card card__primary ">
                            <div class="card-header">评价总量</div>
                            <div class="card-body">
                                <div class="card-value-row">
                                    <div class="card-value"><?= $data['widget-card']['comment_total'] ?></div>
                                    <span class="card-icon iconfont icon-haoping2"></span>
                                </div>
                                <div class="card-description">订单评价总数量</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- 实时概况 -->
    <div class="row">
        <div class="w-full mb-4">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">实时概况</div>
                </div>
                <div class="widget-body ">
                    <div class="sm:w-1/2 md:w-1/2 lg:w-1/4">
                        <div class="widget-outline dis-flex flex-y-center">
                            <div class="outline-left">
                                <img src="data:image/png;base64,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"
                                     alt="">
                            </div>
                            <div class="outline-right dis-flex flex-dir-column flex-x-between">
                                <div style="color: rgb(102, 102, 102); font-size: 1.3rem;">销售额(元)</div>
                                <div style="color: rgb(51, 51, 51); font-size: 2.4rem;"><?= $data['widget-outline']['order_total_price']['tday'] ?></div>
                                <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">
                                    昨日：<?= $data['widget-outline']['order_total_price']['ytd'] ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:w-1/2 md:w-1/2 lg:w-1/4">
                        <div class="widget-outline dis-flex flex-dir-column flex-x-between">
                            <div style="color: rgb(102, 102, 102); font-size: 1.2rem;">支付订单数</div>
                            <div style="color: rgb(51, 51, 51); font-size: 2.4rem;"><?= $data['widget-outline']['order_total']['tday'] ?></div>
                            <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">
                                昨日：<?= $data['widget-outline']['order_total']['ytd'] ?></div>
                        </div>
                    </div>
                    <div class="sm:w-1/2 md:w-1/2 lg:w-1/4">
                        <div class="widget-outline dis-flex flex-y-center">
                            <div class="outline-left">
                                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAI4AAACMCAMAAACd62ExAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAzUExURUdwTPn///D4/+/2//T6//D3//H3//D3//D3//H5/2aq/7DT/5HC/3az/8Ld/9zs/53I/+kzVBAAAAAKdFJOUwAOY/8hooXE4kIJnP2wAAADmUlEQVR42u2ca3fCIAyGLeFqW+j//7XTOWedbUkgYHaO784+6p6FJNxCTqdyDVpZY5zz3gN475wzVoXh1F9DsO7CsCnvjNJ9USAn3wdpUHmUu5xtTBQM0ORUM1carIcCmdACRlsolWMH0gZqxAtUCcMMpIBDhifMggcmKYZwMsCn6hHjM81NtorGArdcuQdpB/zyQchA1Q2YglZygwS3qeEx0FJevy/bVPMMDlqLEmDtaSj2MQCCeCz0ES6+etHgeBT0k8nPU9BT6v0h/qQgIahW4TVIcZy8+2joL/XebPwiLWeovrPPXlT5t+DsRRciqmIcqYqxLLpCFmY8F2lOJYvnrB/P50LNBd6cNc50LtZITz4548Tv/3NakHqCX8jmQRkn6wV3peUxUpffiZybUZ6DpIm/ppnHFDE4f82TD6vrt6MMM/66/BR/BjmPY6nTAw5nZZgl/fpcHgcG4tyJwfljGAqOIq6Pszgr913Sc0ROxJnLV+PE+eG+LwkCgbN25gCVOFujRMSxtL3MEc70iOvN9InB8aSxOsIZdwxDwnmsMzBjdYQzreK6HMeQ9p0HOIcJG43jSUvk5jj32BpABo4iuE4HHEM5smiP4ym7q/Y4P84DUnAUYSc8t8exeE++pt6xMY6hHL6lBI1xHM+RDhcO8BxbsOFo5HTeCSeg47wLjsLOWN1w9AfnIA+GD85BWv7gfHBKXVl/cP7PJCFsCpW2wBC2/EIuTuM8xx6LU4s2wdxj6Y7Mgx22fYaw7Tt32vYJ2xRLOzIQdqAi7LhJ2GGcsKNKaQe5QQSO4boE4MEJXFckLDiedoF08DdZcCztem3a/1oWHE27fLxd6y9bVR9nBhxHLWs6LnpIu6u2gqtZTCZMMxknLbfPUC+ucalnoeE87tQjyZHRE0WK47Lxs4WTDm5rM45MWKIiI+thmPOSSBmZoSruL864d6eONk6VeZ5wVsUGmFHaMU6NeVY4T7Uy6C/QRaVoOZx7XFMMsxVWleWvN5zXWhmkdspgi8sYrziFhnleWfAUea6nD5phXmYrDm9O1LjO+nFdgXC8GSaWfFY1KJ9Ol6kiFX3S/KPicmml98IeJkh7tiHtUYu0Jz/SHkRJey4m7TGdsKeG0h5iinumKu0Rr7QnztIegEt7Hi+ueQBLC4yVaRiaYYhqPCGuLYe4piXSWrqIa3gjrh2QuGZJ5FZS3nZpbiWo0Za8NmQbTdqAq0nbF7i46IS8tSAEAAAAAElFTkSuQmCC"
                                     alt="">
                            </div>
                            <div class="outline-right dis-flex flex-dir-column flex-x-between">
                                <div style="color: rgb(102, 102, 102); font-size: 1.3rem;">新增用户数</div>
                                <div style="color: rgb(51, 51, 51); font-size: 2.4rem;"><?= $data['widget-outline']['new_user_total']['tday'] ?></div>
                                <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">
                                    昨日：<?= $data['widget-outline']['new_user_total']['ytd'] ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:w-1/2 md:w-1/2 lg:w-1/4">
                        <div class="widget-outline dis-flex flex-dir-column flex-x-between">
                            <div style="color: rgb(102, 102, 102); font-size: 1.2rem;">下单用户数</div>
                            <div style="color: rgb(51, 51, 51); font-size: 2.4rem;"><?= $data['widget-outline']['order_user_total']['tday'] ?></div>
                            <div style="color: rgb(153, 153, 153); font-size: 1.2rem;">
                                昨日：<?= $data['widget-outline']['order_user_total']['ytd'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 近七日交易走势 -->
    <div class="row">
        <div class="w-full mb-4">
            <div class="widget widget--chart">
                <div class="widget-head">
                    <div class="widget-title">近七日交易走势</div>
                    <div class="chart-legend" id="chart-legend"></div>
                </div>
                <div class="widget-body widget-body--chart">
                    <div id="echarts-trade" class="widget-echarts"></div>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="assets/common/js/echarts.min.js"></script>
<script>
(function () {
    var dom = document.getElementById('echarts-trade');
    var chart = echarts.init(dom);

    // 渐变：成交额（青蓝）、成交量（紫粉）
    var priceGrad = new echarts.graphic.LinearGradient(0, 0, 0, 1, [
        { offset: 0,  color: 'rgba(99,102,241,0.55)' },
        { offset: 0.5,color: 'rgba(99,102,241,0.18)' },
        { offset: 1,  color: 'rgba(99,102,241,0.02)' }
    ]);
    var priceLineGrad = new echarts.graphic.LinearGradient(0, 0, 1, 0, [
        { offset: 0, color: '#6366f1' },
        { offset: 1, color: '#06b6d4' }
    ]);
    var orderGrad = new echarts.graphic.LinearGradient(0, 0, 0, 1, [
        { offset: 0,  color: 'rgba(236,72,153,0.45)' },
        { offset: 0.5,color: 'rgba(236,72,153,0.12)' },
        { offset: 1,  color: 'rgba(236,72,153,0.02)' }
    ]);
    var orderLineGrad = new echarts.graphic.LinearGradient(0, 0, 1, 0, [
        { offset: 0, color: '#f97316' },
        { offset: 1, color: '#ec4899' }
    ]);

    var option = {
        grid: { top: 28, right: 18, bottom: 32, left: 46, containLabel: false },
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(255,255,255,0.96)',
            borderColor: 'rgba(0,0,0,0.06)',
            borderWidth: 1,
            padding: [10, 14],
            textStyle: { color: '#1f2937', fontSize: 12, fontFamily: 'system-ui,-apple-system,"PingFang SC"' },
            axisPointer: {
                type: 'line',
                lineStyle: { color: 'rgba(0,0,0,0.12)', type: 'dashed', width: 1 }
            },
            formatter: function (params) {
                var title = params[0].axisValueLabel;
                var html = '<div style="font-size:11px;color:#9ca3af;margin-bottom:6px">' + title + '</div>';
                params.forEach(function (p) {
                    var color = p.seriesName === '成交额' ? '#6366f1' : '#ec4899';
                    var suffix = p.seriesName === '成交额' ? ' 元' : '';
                    var val = p.seriesName === '成交额'
                        ? Number(p.value).toLocaleString('zh-CN', { maximumFractionDigits: 2 })
                        : Number(p.value).toLocaleString('zh-CN');
                    html += '<div style="display:flex;align-items:center;justify-content:space-between;gap:18px;">'
                        + '<span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:' + color + ';margin-right:6px"></span>'
                        + p.seriesName + '</span>'
                        + '<span style="font-weight:700;color:#111827;font-variant-numeric:tabular-nums">' + val + suffix + '</span>'
                        + '</div>';
                });
                return html;
            }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: <?= $data['widget-echarts']['date'] ?>,
            axisLine: { show: false },
            axisTick: { show: false },
            axisLabel: {
                color: '#9ca3af',
                fontSize: 11,
                margin: 12,
                fontFamily: 'system-ui,-apple-system,"PingFang SC"'
            }
        },
        yAxis: {
            type: 'value',
            splitNumber: 4,
            axisLine: { show: false },
            axisTick: { show: false },
            axisLabel: { color: '#9ca3af', fontSize: 11, margin: 10 },
            splitLine: { lineStyle: { color: 'rgba(0,0,0,0.06)', type: 'dashed' } }
        },
        series: [
            {
                name: '成交额',
                type: 'line',
                smooth: true,
                showSymbol: false,
                symbolSize: 6,
                emphasis: { focus: 'series', scale: 1.3 },
                lineStyle: { width: 2.5, color: priceLineGrad, shadowBlur: 8, shadowColor: 'rgba(99,102,241,0.35)' },
                areaStyle: { color: priceGrad, opacity: 1 },
                data: <?= $data['widget-echarts']['order_total_price'] ?>
            },
            {
                name: '成交量',
                type: 'line',
                smooth: true,
                showSymbol: false,
                symbolSize: 6,
                emphasis: { focus: 'series', scale: 1.3 },
                lineStyle: { width: 2.5, color: orderLineGrad, shadowBlur: 8, shadowColor: 'rgba(236,72,153,0.35)' },
                areaStyle: { color: orderGrad, opacity: 1 },
                data: <?= $data['widget-echarts']['order_total'] ?>
            }
        ]
    };

    chart.setOption(option);
    window.addEventListener('resize', function () { chart.resize(); });

    // 自定义图例（widget 标题栏右侧）
    var legendEl = document.getElementById('chart-legend');
    if (legendEl) {
        legendEl.innerHTML =
            '<span class="chart-legend__item"><i style="background:linear-gradient(90deg,#6366f1,#06b6d4)"></i>成交额</span>' +
            '<span class="chart-legend__item"><i style="background:linear-gradient(90deg,#f97316,#ec4899)"></i>成交量</span>';
    }
})();
</script>