<?php
/** @var array $admin */
/** @var string $token */
/** @var array $data */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据看板 - 运营管理系统</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
            color: #fff; min-height: 100vh;
            background: linear-gradient(135deg, #1a3a6b 0%, #0d2545 100%);
        }
        .content { padding: 22px 26px 30px; }
        .page-title { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .page-sub { font-size: 13px; opacity: 0.72; margin-bottom: 22px; }

        .stat-row { display: flex; gap: 18px; flex-wrap: wrap; }
        .stat-card {
            flex: 1; min-width: 220px; padding: 22px 24px; border-radius: 16px;
            background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.22); box-shadow: 0 10px 30px rgba(8, 24, 48, 0.35);
        }
        .stat-card .num { font-size: 30px; font-weight: 700; }
        .stat-card .label { font-size: 13px; opacity: 0.78; margin-top: 8px; }
        .stat-card .icon { float: right; font-size: 22px; opacity: 0.6; }

        .chart-panel {
            margin-top: 22px; padding: 22px 24px; border-radius: 18px;
            background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.22); box-shadow: 0 10px 30px rgba(8, 24, 48, 0.35);
        }
        .chart-panel h3 { font-size: 15px; font-weight: 600; margin-bottom: 14px; }
        #salesChart { width: 100%; height: 320px; }
    </style>
</head>
<body>
<div class="content">
    <div class="page-title">运营核心指标</div>
    <div class="page-sub">来自桌面程序坞「看板」入口</div>

    <div class="stat-row">
        <div class="stat-card">
            <div class="icon">🏬</div>
            <div class="num"><?= number_format($data['store']) ?></div>
            <div class="label">商城数（在售 / 未删除）</div>
        </div>
        <div class="stat-card">
            <div class="icon">💰</div>
            <div class="num">¥ <?= number_format($data['sales'], 2) ?></div>
            <div class="label">销售额（已支付订单 pay_price 合计）</div>
        </div>
        <div class="stat-card">
            <div class="icon">👥</div>
            <div class="num"><?= number_format($data['user']) ?></div>
            <div class="label">用户量（C 端用户 / 未删除）</div>
        </div>
    </div>

    <div class="chart-panel">
        <h3>最近 7 天销售额趋势</h3>
        <div id="salesChart"></div>
    </div>
</div>

<script src="/assets/common/js/echarts.min.js"></script>
<script>
(function () {
    var chart = echarts.init(document.getElementById('salesChart'), null, { renderer: 'canvas' });
    var option = {
        tooltip: { trigger: 'axis', valueFormatter: function (v) { return '¥ ' + Number(v).toLocaleString(); } },
        grid: { left: 50, right: 20, top: 20, bottom: 35 },
        xAxis: {
            type: 'category',
            data: <?= json_encode($data['days'], JSON_UNESCAPED_UNICODE) ?>,
            axisLine: { lineStyle: { color: 'rgba(255,255,255,0.4)' } },
            axisLabel: { color: 'rgba(255,255,255,0.8)' }
        },
        yAxis: {
            type: 'value',
            axisLine: { show: false },
            axisLabel: {
                color: 'rgba(255,255,255,0.8)',
                formatter: function (v) { return v >= 1000 ? (v / 1000) + 'k' : v; }
            },
            splitLine: { lineStyle: { color: 'rgba(255,255,255,0.08)' } }
        },
        series: [{
            name: '销售额',
            type: 'line',
            smooth: true,
            symbol: 'circle',
            symbolSize: 7,
            data: <?= json_encode($data['sales7']) ?>,
            itemStyle: { color: '#6db0ff', borderColor: '#fff', borderWidth: 2 },
            lineStyle: { width: 3, color: '#6db0ff' },
            areaStyle: {
                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                    { offset: 0, color: 'rgba(109,176,255,0.5)' },
                    { offset: 1, color: 'rgba(109,176,255,0.02)' }
                ])
            }
        }]
    };
    chart.setOption(option);
    window.addEventListener('resize', function () { chart.resize(); });
})();
</script>
</body>
</html>
