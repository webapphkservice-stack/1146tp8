<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>代理订单列表</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="/assets/common/css/tailwind.css" />
    <link rel="stylesheet" href="/assets/store/css/app.css" />
    <style>
        .order-stats {
            display: flex;
            justify-content: space-around;
            background: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            flex-wrap: wrap;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }
        .stat-total .stat-value { color: #1890ff; }
        .stat-settled .stat-value { color: #52c41a; }
        .stat-unsettled .stat-value { color: #faad14; }
        .stat-income .stat-value { color: #722ed1; }
        
        .order-card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            margin: 0 20px 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .order-no {
            font-size: 14px;
            color: #666;
        }
        .order-amount {
            font-size: 16px;
            font-weight: bold;
            color: #ff4d4f;
        }
        .agent-info {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }
        .agent-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #8e84fc;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            margin-right: 10px;
        }
        .agent-name {
            font-size: 15px;
            font-weight: bold;
            color: #333;
        }
        .agent-user-id {
            font-size: 12px;
            color: #999;
        }
        .agent-level {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 8px;
        }
        .level-province {
            background: #fff7e6;
            color: #fa8c16;
        }
        .level-city {
            background: #e6f7ff;
            color: #1890ff;
        }
        .level-district {
            background: #f6ffed;
            color: #52c41a;
        }
        .order-status-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }
        .status-tag {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
        .status-pay {
            background: #f6ffed;
            color: #52c41a;
        }
        .status-delivery {
            background: #e6f7ff;
            color: #1890ff;
        }
        .status-receipt {
            background: #fff7e6;
            color: #fa8c16;
        }
        .status-settle {
            background: #f9f0ff;
            color: #722ed1;
        }
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
            border-top: 1px dashed #eee;
        }
        .order-time {
            font-size: 12px;
            color: #999;
        }
        .order-income {
            font-size: 15px;
            font-weight: bold;
            color: #52c41a;
        }
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
            margin: 20px;
            background: #fff;
            border-radius: 8px;
        }
        .filter-bar {
            background: #fff;
            padding: 15px 20px;
            margin: 0 20px 10px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            flex-wrap: wrap;
        }
        .filter-item {
            display: flex;
            align-items: center;
        }
        .filter-label {
            font-size: 14px;
            color: #666;
            margin-right: 8px;
        }
        .filter-select {
            width: 120px;
            height: 32px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0 10px;
            font-size: 13px;
        }
        .btn-filter {
            background: #1890ff;
            color: #fff;
            border: none;
            padding: 6px 15px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
        }
        .btn-filter:hover {
            background: #40a9ff;
        }
    </style>
</head>
<body>
<div class=" p-4 text-red-600 text-sm" style="background: #fff;">
    <i class="icon-info-circle"></i> 提示：显示所有代理商的订单，包含付款状态、发货状态、收货状态、结算状态
</div>

<?php
$baseUrl = '/index.php?s=/store/regionagent/orders';
?>

<div class="order-stats">
    <div class="stat-item stat-total">
        <div class="stat-value"><?php echo $stats['total']; ?></div>
        <div class="stat-label">总订单数</div>
    </div>
    <div class="stat-item stat-settled">
        <div class="stat-value"><?php echo $stats['settled']; ?></div>
        <div class="stat-label">已结算</div>
    </div>
    <div class="stat-item stat-unsettled">
        <div class="stat-value"><?php echo $stats['unsettled']; ?></div>
        <div class="stat-label">未结算</div>
    </div>
    <div class="stat-item stat-income">
        <div class="stat-value">¥<?php echo number_format($stats['total_income'], 2); ?></div>
        <div class="stat-label">总收入</div>
    </div>
</div>

<form method="GET" id="filter-form">
    <div class="filter-bar">
        <div class="filter-item">
            <span class="filter-label">代理商级别：</span>
            <select name="level" class="filter-select" id="level">
                <option value="0">全部</option>
                <option value="1">省代</option>
                <option value="2">市代</option>
                <option value="3">区代</option>
            </select>
        </div>
        <div class="filter-item">
            <span class="filter-label">发货状态：</span>
            <select name="delivery_status" class="filter-select" id="delivery_status">
                <option value="">全部</option>
                <option value="0">未发货</option>
                <option value="1">已发货</option>
            </select>
        </div>
        <div class="filter-item">
            <span class="filter-label">收货状态：</span>
            <select name="receipt_status" class="filter-select" id="receipt_status">
                <option value="">全部</option>
                <option value="0">未收货</option>
                <option value="1">已收货</option>
            </select>
        </div>
        <div class="filter-item">
            <span class="filter-label">结算状态：</span>
            <select name="is_settle" class="filter-select" id="is_settle">
                <option value="">全部</option>
                <option value="0">未结算</option>
                <option value="1">已结算</option>
            </select>
        </div>
        <button type="button" class="btn-filter" onclick="doFilter()">
            <i class="icon-search"></i> 筛选
        </button>
    </div>
</form>

<script>
function doFilter() {
    var level = document.getElementById('level').value;
    var deliveryStatus = document.getElementById('delivery_status').value;
    var receiptStatus = document.getElementById('receipt_status').value;
    var isSettle = document.getElementById('is_settle').value;
    
    var url = '/index.php?s=/store/regionagent/orders';
    var params = [];
    
    if (level !== '0') {
        params.push('level=' + level);
    }
    if (deliveryStatus !== '') {
        params.push('delivery_status=' + deliveryStatus);
    }
    if (receiptStatus !== '') {
        params.push('receipt_status=' + receiptStatus);
    }
    if (isSettle !== '') {
        params.push('is_settle=' + isSettle);
    }
    
    if (params.length > 0) {
        url += '&' + params.join('&');
    }
    
    window.location.href = url;
}
</script>

<div class="sm:w-full">
<?php if (!empty($orders)): ?>
    <?php foreach ($orders as $order): ?>
        <div class="order-card">
            <div class="order-header">
                <span class="order-no">订单号：<?php echo $order['order_no']; ?></span>
                <span class="order-amount">¥<?php echo number_format($order['pay_price'], 2); ?></span>
            </div>
            
            <div class="agent-info">
                <div class="agent-avatar">
                    <?php echo mb_substr($order['agent_name'] ?: '未命名', 0, 1, 'utf-8'); ?>
                </div>
                <div>
                    <span class="agent-name"><?php echo $order['agent_name'] ?: '未知'; ?></span>
                    <span class="agent-user-id">(用户ID: <?php echo $order['agent_user_id']; ?>)</span>
                    <span class="agent-level <?php if ($order['agent_level'] == 1) { echo 'level-province'; } elseif ($order['agent_level'] == 2) { echo 'level-city'; } else { echo 'level-district'; } ?>">
                        <?php echo $order['agent_level_text']; ?>
                    </span>
                </div>
            </div>
            
            <div class="order-status-row">
                <span class="status-tag status-pay">
                    <i class="icon-credit-card"></i> <?php echo $order['pay_status_text']; ?>
                </span>
                <span class="status-tag status-delivery">
                    <i class="icon-truck"></i> <?php echo $order['delivery_status_text']; ?>
                </span>
                <span class="status-tag status-receipt">
                    <i class="icon-check-circle"></i> <?php echo $order['receipt_status_text']; ?>
                </span>
                <span class="status-tag status-settle">
                    <i class="icon-rmb"></i> <?php echo $order['is_settle_text']; ?>
                </span>
            </div>
            
            <div class="order-footer">
                <span class="order-time">
                    <i class="icon-clock-o"></i> <?php echo date('Y-m-d H:i:s', $order['create_time']); ?>
                </span>
                <span class="order-income">
                    收入：+¥<?php echo number_format($order['income'], 2); ?>
                </span>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="empty-state">
        <i class="icon-inbox"></i>
        <p>暂无代理订单</p>
    </div>
<?php endif; ?>
</div>

<?php if (isset($totalPages) && $totalPages > 1): ?>
<div class="inline-flex items-center gap-1 flex justify-center mt-6" style="margin: 20px;">
    <ul class="inline-flex items-center gap-1">
<?php if ($currentPage > 1): ?>
<?php 
$prevParams = array_merge($_GET, ['page' => $currentPage - 1]);
unset($prevParams['s']);
$prevUrl = $baseUrl . '&' . http_build_query($prevParams);
?>
        <li><a href="<?php echo $prevUrl; ?>">&laquo; 上一页</a></li>
<?php endif; ?>

<?php 
$startPage = max(1, $currentPage - 3);
$endPage = min($totalPages, $currentPage + 3);

if ($startPage > 1):
?>
        <li><a href="<?php echo $baseUrl; ?>">1</a></li>
<?php if ($startPage > 2): ?>
        <li class="opacity-50 cursor-not-allowed"><span>...</span></li>
<?php endif; ?>
<?php endif; ?>

<?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <li <?php if ($i == $currentPage) { echo 'class="px-2 py-1 rounded bg-blue-600 text-white"'; } ?>>
<?php if ($i == $currentPage): ?>
            <span><?php echo $i; ?></span>
<?php else: ?>
<?php 
$pageParams = array_merge($_GET, ['page' => $i]);
unset($pageParams['s']);
$pageUrl = $baseUrl . '&' . http_build_query($pageParams);
?>
            <a href="<?php echo $pageUrl; ?>"><?php echo $i; ?></a>
<?php endif; ?>
        </li>
<?php endfor; ?>

<?php if ($endPage < $totalPages): ?>
<?php if ($endPage < $totalPages - 1): ?>
        <li class="opacity-50 cursor-not-allowed"><span>...</span></li>
<?php endif; ?>
<?php 
$lastParams = array_merge($_GET, ['page' => $totalPages]);
unset($lastParams['s']);
$lastUrl = $baseUrl . '&' . http_build_query($lastParams);
?>
        <li><a href="<?php echo $lastUrl; ?>"><?php echo $totalPages; ?></a></li>
<?php endif; ?>

<?php if ($currentPage < $totalPages): ?>
<?php 
$nextParams = array_merge($_GET, ['page' => $currentPage + 1]);
unset($nextParams['s']);
$nextUrl = $baseUrl . '&' . http_build_query($nextParams);
?>
        <li><a href="<?php echo $nextUrl; ?>">下一页 &raquo;</a></li>
<?php endif; ?>
    </ul>
    
    <div class="inline-flex items-center" style="display: inline-block; margin-left: 20px; vertical-align: middle;">
        <span class="text-sm text-gray-500">共 <?php echo $totalCount; ?> 条，每页 <?php echo $limit; ?> 条，第 <?php echo $currentPage; ?>/<?php echo $totalPages; ?> 页</span>
    </div>
</div>
<?php endif; ?>

<script src="/assets/store/js/jquery.min.js"></script>
<script src="/assets/common/js/jquery4-polyfill.js"></script>
<script src="/assets/store/js/amz-modal.js"></script>
<script src="/assets/store/js/store.js"></script>
</body>
</html>