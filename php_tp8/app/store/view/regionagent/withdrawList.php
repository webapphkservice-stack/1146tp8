<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>代理商转账记录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="/assets/common/css/tailwind.css" />
    <link rel="stylesheet" href="/assets/store/css/app.css" />
    <style>
        .withdraw-stats {
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
            margin: 5px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }
        .stat-total .stat-value { color: #1890ff; }
        .stat-pending .stat-value { color: #faad14; }
        .stat-approved .stat-value { color: #52c41a; }
        .stat-completed .stat-value { color: #722ed1; }
        
        .withdraw-card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            margin: 0 20px 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .withdraw-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .withdraw-no {
            font-size: 14px;
            color: #666;
        }
        .withdraw-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-pending {
            background: linear-gradient(135deg, #faad14 0%, #d48806 100%);
            color: #fff;
        }
        .status-approved {
            background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
            color: #fff;
        }
        .status-rejected {
            background: linear-gradient(135deg, #ff4d4f 0%, #cf1322 100%);
            color: #fff;
        }
        .status-completed {
            background: linear-gradient(135deg, #52c41a 0%, #389e0d 100%);
            color: #fff;
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
        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px dashed #eee;
        }
        .amount-row:last-child {
            border-bottom: none;
        }
        .amount-section {
            background: #f8f9fa;
            border-radius: 8px;
            margin: 10px 0;
            overflow: hidden;
        }
        .amount-withdraw-row {
            background: linear-gradient(135deg, #fff1f0 0%, #fff7e6 100%);
            border-left: 4px solid #ff4d4f;
        }
        .amount-tax-row {
            background: linear-gradient(135deg, #fffbe6 0%, #fff7e6 100%);
            border-left: 4px solid #faad14;
        }
        .amount-actual-row {
            background: linear-gradient(135deg, #f6ffed 0%, #d9f7be 100%);
            border-left: 4px solid #52c41a;
        }
        .amount-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        .amount-label i {
            margin-right: 5px;
        }
        .amount-value {
            font-size: 16px;
            font-weight: bold;
        }
        .amount-withdraw {
            color: #ff4d4f;
        }
        .amount-tax {
            color: #faad14;
        }
        .amount-actual {
            color: #52c41a;
        }
        .type-tag {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            background: #f0f0f0;
            color: #666;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .type-wechat {
            background: linear-gradient(135deg, #07c160 0%, #06ae56 100%);
            color: #fff;
        }
        .type-bank {
            background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
            color: #fff;
        }
        .withdraw-footer {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px dashed #eee;
            font-size: 12px;
            color: #999;
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
        .amount-withdraw-type-row {
            display: flex;
            align-items: center;
        }
        .withdraw-type-right {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            justify-content: flex-end;
        }
        .bank-card-info {
            font-size: 13px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .bank-card-info i {
            margin-right: 3px;
        }
        .action-bar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px dashed #eee;
        }
        .action-btn {
            padding: 6px 15px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        .btn-approve {
            background: #52c41a;
            color: #fff;
        }
        .btn-approve:hover {
            background: #73d13d;
        }
        .btn-reject {
            background: #faad14;
            color: #fff;
        }
        .btn-reject:hover {
            background: #ffc53d;
        }
        .btn-transfer {
            background: #1890ff;
            color: #fff;
        }
        .btn-transfer:hover {
            background: #40a9ff;
        }
        .btn-disabled {
            background: #f0f0f0;
            color: #999;
            cursor: not-allowed;
        }
        .btn-bank {
            background: #722ed1;
            color: #fff;
        }
        .btn-bank:hover {
            background: #9254de;
        }
        .status-completed-bank {
            background: linear-gradient(135deg, #722ed1 0%, #531dab 100%);
            color: #fff;
        }
    </style>
</head>
<body>
<div class=" p-4 text-red-600 text-sm" style="background: #fff;">
    <i class="icon-info-circle"></i> 提示：显示所有代理商的提现转账记录
</div>

<div class="withdraw-stats">
    <div class="stat-item stat-total">
        <div class="stat-value"><?php echo $stats['total']; ?></div>
        <div class="stat-label">总记录数</div>
    </div>
    <div class="stat-item stat-pending">
        <div class="stat-value"><?php echo $stats['pending']; ?></div>
        <div class="stat-label">待审核</div>
    </div>
    <div class="stat-item stat-approved">
        <div class="stat-value"><?php echo $stats['approved']; ?></div>
        <div class="stat-label">已审核</div>
    </div>
    <div class="stat-item stat-completed">
        <div class="stat-value"><?php echo $stats['completed']; ?></div>
        <div class="stat-label">已完成</div>
    </div>
    <div class="stat-item">
        <div class="stat-value">¥<?php echo number_format($stats['total_amount'] ?: 0, 2); ?></div>
        <div class="stat-label">总提现金额</div>
    </div>
    <div class="stat-item">
        <div class="stat-value">¥<?php echo number_format($stats['total_tax'] ?: 0, 2); ?></div>
        <div class="stat-label">总税费</div>
    </div>
    <div class="stat-item">
        <div class="stat-value">¥<?php echo number_format($stats['total_actual'] ?: 0, 2); ?></div>
        <div class="stat-label">总实际到账</div>
    </div>
</div>

<form method="GET" id="filter-form">
    <div class="filter-bar">
        <div class="filter-item">
            <span class="filter-label">状态：</span>
            <select name="status" class="filter-select" id="status">
                <option value="">全部</option>
                <option value="0">待审核</option>
                <option value="1">审核通过</option>
                <option value="2">审核拒绝</option>
                <option value="3">已打款</option>
                <option value="4">已完成</option>
            </select>
        </div>
        <div class="filter-item">
            <span class="filter-label">提现方式：</span>
            <select name="withdraw_type" class="filter-select" id="withdraw_type">
                <option value="">全部</option>
                <option value="1">微信钱包</option>
                <option value="2">银行卡</option>
            </select>
        </div>
        <button type="button" class="btn-filter" onclick="doFilter()">
            <i class="icon-search"></i> 筛选
        </button>
    </div>
</form>

<script>
function doFilter() {
    var status = document.getElementById('status').value;
    var withdrawType = document.getElementById('withdraw_type').value;
    
    var url = '/index.php?s=/store/regionagent/withdrawList';
    var params = [];
    
    if (status !== '') {
        params.push('status=' + status);
    }
    if (withdrawType !== '') {
        params.push('withdraw_type=' + withdrawType);
    }
    
    if (params.length > 0) {
        url += '&' + params.join('&');
    }
    
    window.location.href = url;
}
</script>

<div class="sm:w-full">
<?php if (!empty($withdrawList)): ?>
    <?php foreach ($withdrawList as $item): ?>
        <div class="withdraw-card">
            <div class="withdraw-header">
                <span class="withdraw-no">订单号：<?php echo $item['order_sn']; ?></span>
                <span class="withdraw-status status-<?php if ($item['status'] == 0) { echo 'pending'; } elseif ($item['status'] == 1) { echo 'approved'; } elseif ($item['status'] == 2) { echo 'rejected'; } elseif ($item['status'] == 3) { echo 'completed'; } elseif ($item['status'] == 5) { echo 'completed-bank'; } else { echo 'completed'; } ?>">
                    <?php echo $item['status_text']; ?>
                </span>
            </div>
            
            <div class="agent-info">
                <div class="agent-avatar">
                    <?php echo mb_substr($item['nickName'] ?: '未命名', 0, 1, 'utf-8'); ?>
                </div>
                <div>
                    <span class="agent-name"><?php echo $item['nickName'] ?: '未知'; ?></span>
                    <span class="agent-user-id">(用户ID: <?php echo $item['user_id']; ?>)</span>
                    <span class="agent-level <?php if ($item['region_level'] == 1) { echo 'level-province'; } elseif ($item['region_level'] == 2) { echo 'level-city'; } else { echo 'level-district'; } ?>">
                        <?php echo $item['level_text']; ?>
                    </span>
                </div>
            </div>
            
            <div class="amount-section">
                <div class="amount-row amount-withdraw-row">
                    <span class="amount-label">
                        <i class="icon-database"></i> 提现金额
                    </span>
                    <span class="amount-value amount-withdraw">¥<?php echo number_format($item['withdraw_amount'], 2); ?></span>
                </div>
                <div class="amount-row amount-tax-row">
                    <span class="amount-label">
                        <i class="icon-file-text-o"></i> 税费金额
                    </span>
                    <span class="amount-value amount-tax">-¥<?php echo number_format($item['tax_amount'], 2); ?></span>
                </div>
                <div class="amount-row amount-actual-row">
                    <span class="amount-label">
                        <i class="icon-check-circle"></i> 实际到账
                    </span>
                    <span class="amount-value amount-actual">¥<?php echo number_format($item['actual_amount'], 2); ?></span>
                </div>
            </div>
            <div class="amount-row amount-withdraw-type-row">
                <span class="amount-label">
                    <i class="icon-credit-card"></i> 提现方式
                </span>
                <div class="withdraw-type-right">
                    <span class="type-tag <?php if ($item['withdraw_type'] == 1) { echo 'type-wechat'; } else { echo 'type-bank'; } ?>">
                        <i class="icon-<?php if ($item['withdraw_type'] == 1) { echo 'wechat'; } else { echo 'credit-card'; } ?>"></i>
                        <?php echo $item['withdraw_type_text']; ?>
                    </span>
<?php if ($item['withdraw_type'] == 2 && !empty($item['bank_name'])): ?>
                    <span class="bank-card-info">
                        <i class="icon-building"></i><?php echo $item['bank_name']; ?>
                        <i class="icon-user"></i><?php echo $item['bank_account']; ?>
                        <i class="icon-credit-card"></i><?php echo $item['bank_card']; ?>
                    </span>
<?php endif; ?>
                </div>
            </div>
            
            <div class="withdraw-footer">
                <i class="icon-clock-o"></i> 申请时间：<?php echo $item['apply_time_text']; ?>
                <?php if ($item['audit_time'] > 0): ?>
                    | 审核时间：<?php echo $item['audit_time_text']; ?>
                <?php endif; ?>
            </div>
            
            <?php if ($item['status'] == 0): ?>
            <div class="action-bar">
                <button type="button" class="action-btn btn-approve j-approve" data-id="<?php echo $item['id']; ?>">
                    <i class="icon-check"></i> 通过
                </button>
                <button type="button" class="action-btn btn-reject j-reject" data-id="<?php echo $item['id']; ?>">
                    <i class="icon-close"></i> 拒绝
                </button>
            </div>
            <?php elseif ($item['status'] == 1): ?>
            <div class="action-bar">
                <?php if ($item['withdraw_type'] == 1): ?>
                <button type="button" class="action-btn btn-transfer j-transfer" data-id="<?php echo $item['id']; ?>">
                    <i class="icon-wechat"></i> 微信打款
                </button>
                <?php else: ?>
                <button type="button" class="action-btn btn-bank j-bank" data-id="<?php echo $item['id']; ?>">
                    <i class="icon-building"></i> 已银行转账
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="empty-state">
        <i class="icon-inbox"></i>
        <p>暂无提现记录</p>
    </div>
<?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="inline-flex items-center gap-1 flex justify-center mt-6" style="margin: 20px;">
    <ul class="inline-flex items-center gap-1">
<?php if ($currentPage > 1): ?>
<?php 
$prevParams = array_merge($_GET, ['page' => $currentPage - 1]);
unset($prevParams['s']);
$prevUrl = '/index.php?s=/store/regionagent/withdrawList&' . http_build_query($prevParams);
?>
        <li><a href="<?php echo $prevUrl; ?>">&laquo; 上一页</a></li>
<?php endif; ?>

<?php 
$startPage = max(1, $currentPage - 3);
$endPage = min($totalPages, $currentPage + 3);

if ($startPage > 1):
?>
        <li><a href="/index.php?s=/store/regionagent/withdrawList">1</a></li>
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
$pageUrl = '/index.php?s=/store/regionagent/withdrawList&' . http_build_query($pageParams);
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
$lastUrl = '/index.php?s=/store/regionagent/withdrawList&' . http_build_query($lastParams);
?>
        <li><a href="<?php echo $lastUrl; ?>"><?php echo $totalPages; ?></a></li>
<?php endif; ?>

<?php if ($currentPage < $totalPages): ?>
<?php 
$nextParams = array_merge($_GET, ['page' => $currentPage + 1]);
unset($nextParams['s']);
$nextUrl = '/index.php?s=/store/regionagent/withdrawList&' . http_build_query($nextParams);
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

<!-- 拒绝弹窗 -->
<div class="fixed inset-0 z-50 flex items-center justify-center hidden fixed inset-0 z-50 flex items-center justify-center hidden" id="reject-modal">
    <div class="relative bg-white rounded-lg shadow-lg w-96 max-w-full">
        <div class="px-4 py-3 border-b font-medium">拒绝申请</div>
        <div class="p-4">
            <textarea class="form-input w-full" rows="4" placeholder="请输入拒绝原因（选填）"></textarea>
        </div>
        <div class="px-4 py-3 border-t flex justify-end gap-2">
            <span class="btn" data-fixed inset-0 z-50 flex items-center justify-center hidden-cancel>取消</span>
            <span class="btn" data-fixed inset-0 z-50 flex items-center justify-center hidden-confirm>确定</span>
        </div>
    </div>
</div>

<script>
$(function() {
    // 审核通过
    $('.j-approve').click(function() {
        var id = $(this).data('id');
        var $btn = $(this);
        
        layer.confirm('确定要通过该提现申请吗？', function(index) {
            layer.close(index);
            
            $.ajax({
                url: "<?= url('regionagent/withdrawApprove') ?>",
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function(result) {
                    if (result.code === 1) {
                        $.show_success(result.msg);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $.show_error(result.msg);
                    }
                },
                error: function() {
                    $.show_error('请求失败，请稍后重试');
                }
            });
        });
    });
    
    // 拒绝申请
    $('.j-reject').click(function() {
        var id = $(this).data('id');
        var $modal = $('#reject-modal');
        var $textarea = $modal.find('textarea');
        $textarea.val('');
        
        $modal.modal({
            relatedTarget: this,
            onConfirm: function(e) {
                var reason = e.data || '';
                
                $.ajax({
                    url: "<?= url('regionagent/withdrawReject') ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: { 
                        id: id,
                        reason: reason
                    },
                    success: function(result) {
                        if (result.code === 1) {
                            $.show_success(result.msg);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            $.show_error(result.msg);
                        }
                    },
                    error: function() {
                        $.show_error('请求失败，请稍后重试');
                    }
                });
            },
            onCancel: function() {
                // 取消
            }
        });
    });
    
    // 微信打款
    $('.j-transfer').click(function() {
        var id = $(this).data('id');
        
        layer.confirm('确定要微信打款吗？', function(index) {
            layer.close(index);
            
            $.ajax({
                url: "<?= url('regionagent/withdrawWechatTransfer') ?>",
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function(result) {
                    if (result.code === 1) {
                        $.show_success(result.msg);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $.show_error(result.msg);
                    }
                },
                error: function() {
                    $.show_error('请求失败，请稍后重试');
                }
            });
        });
    });
    
    // 银行转账完成
    $('.j-bank').click(function() {
        var id = $(this).data('id');
        
        layer.confirm('确定该提现已通过银行转账完成吗？', function(index) {
            layer.close(index);
            
            $.ajax({
                url: "<?= url('regionagent/withdrawBankTransfer') ?>",
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function(result) {
                    if (result.code === 1) {
                        $.show_success(result.msg);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $.show_error(result.msg);
                    }
                },
                error: function() {
                    $.show_error('请求失败，请稍后重试');
                }
            });
        });
    });
});
</script>
</body>
</html>