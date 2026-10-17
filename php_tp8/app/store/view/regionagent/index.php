<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>代理商审核</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="/assets/common/css/tailwind.css" />
    <link rel="stylesheet" href="/assets/store/css/app.css" />
    <style>
        .agent-card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .agent-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .agent-info {
            display: flex;
            align-items: center;
        }
        .agent-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #8e84fc;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 10px;
        }
        .agent-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .agent-id {
            font-size: 12px;
            color: #999;
            font-weight: normal;
            margin-left: 5px;
        }
        .agent-region {
            font-size: 12px;
            color: #999;
            margin-top: 2px;
        }
        .agent-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-pending {
            background: #fff7e6;
            color: #fa8c16;
        }
        .status-rejected {
            background: #fff1f0;
            color: #f5222d;
        }
        .status-approved {
            background: #f6ffed;
            color: #52c41a;
        }
        .agent-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        .detail-item {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .detail-label {
            font-size: 12px;
            color: #999;
            margin-bottom: 4px;
        }
        .detail-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        .agent-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .action-btn {
            padding: 6px 15px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        .btn-agree {
            background: #52c41a;
            color: #fff;
        }
        .btn-agree:hover {
            background: #73d13d;
        }
        .btn-refuse {
            background: #faad14;
            color: #fff;
        }
        .btn-refuse:hover {
            background: #ffc53d;
        }
        .btn-delete {
            background: #ff4d4f;
            color: #fff;
        }
        .btn-delete:hover {
            background: #ff7875;
        }
        .btn-transfer {
            background: #1890ff;
            color: #fff;
        }
        .btn-transfer:hover {
            background: #40a9ff;
        }
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }
        .stats-bar {
            display: flex;
            justify-content: space-around;
            background: #fff;
            padding: 15px 20px;
            padding-left: 20px;
            padding-right: 20px;
            border-radius: 8px;
            margin-top: 20px;
            margin-bottom: 15px;
            margin-left: 20px;
            margin-right: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
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
        .stat-pending .stat-value {
            color: #fa8c16;
        }
        .stat-approved .stat-value {
            color: #52c41a;
        }
        .stat-rejected .stat-value {
            color: #ff4d4f;
        }
    </style>
</head>
<body>
<div class=" p-4 text-red-600 text-sm" style="background: #fff;">
    <i class="icon-info-circle"></i> 提示：代理商审核管理，可以查看、审核、驳回代理商申请，以及进行微信打款
</div>

<!-- 统计栏 -->
<div class="stats-bar">
    <div class="stat-item stat-pending">
        <div class="stat-value"><?= count(array_filter($list->items(), function($item) { return $item['status'] == 101; })) ?></div>
        <div class="stat-label">待审核</div>
    </div>
    <div class="stat-item stat-approved">
        <div class="stat-value"><?= count(array_filter($list->items(), function($item) { return $item['status'] == 103; })) ?></div>
        <div class="stat-label">已通过</div>
    </div>
    <div class="stat-item stat-rejected">
        <div class="stat-value"><?= count(array_filter($list->items(), function($item) { return $item['status'] == 102; })) ?></div>
        <div class="stat-label">已驳回</div>
    </div>
    <div class="stat-item">
        <div class="stat-value"><?= $list->total() ?></div>
        <div class="stat-label">总申请数</div>
    </div>
    <div class="stat-item">
        <div class="stat-value" style="color: #52c41a;">¥<?= number_format($total_current_money, 2) ?></div>
        <div class="stat-label">订单收入</div>
    </div>
    <div class="stat-item">
        <div class="stat-value" style="color: #fa8c16;">¥<?= number_format($total_withdrawn, 2) ?></div>
        <div class="stat-label">已提现</div>
    </div>
    <div class="stat-item">
        <div class="stat-value" style="color: #1890ff;">¥<?= number_format($total_withdrawable, 2) ?></div>
        <div class="stat-label">可提现</div>
    </div>
</div>

<!-- 代理商列表 -->
<div class="sm:w-full">
    <?php if(!empty($list->items())): ?>
        <?php foreach ($list->items() as $item): ?>
            <div class="agent-card">
                <div class="agent-header">
                    <div class="agent-info">
                        <div class="agent-avatar">
                            <?= mb_substr($item['nick_name'] ?: '未命名', 0, 1, 'utf-8') ?>
                        </div>
                        <div>
                            <div class="agent-name"><?= $item['nick_name'] ?: '未命名' ?> <span class="agent-id">(ID:<?= $item['user_id'] ?>)</span></div>
                            <div class="agent-region"><?= $item['region_text'] ?> · <?= $item['level_text'] ?></div>
                        </div>
                    </div>
                    <div class="agent-status <?= $item['status'] == 101 ? 'status-pending' : ($item['status'] == 102 ? 'status-rejected' : 'status-approved') ?>">
                        <?= $item['status_text'] ?>
                    </div>
                </div>
                
                <div class="agent-details">
                    <div class="detail-item">
                        <div class="detail-label">申请时间</div>
                        <div class="detail-value"><?= is_numeric($item['create_time']) ? date('Y-m-d', (int)$item['create_time']) : mb_substr((string)$item['create_time'], 0, 10) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">代理商费用状态</div>
                        <div class="detail-value"><?= $item['is_paid'] == 1 ? '已支付 ¥' . number_format($item['pay_amount'], 2) : '未支付' ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">订单收入金额</div>
                        <div class="detail-value" style="color: #52c41a;">¥<?= number_format($item['current_money'], 2) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">已提现金额</div>
                        <div class="detail-value" style="color: #fa8c16;">¥<?= number_format($item['withdrawn'], 2) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">可提现金额</div>
                        <div class="detail-value" style="color: #1890ff;">¥<?= number_format($item['withdrawable'], 2) ?></div>
                    </div>
                </div>
                
                <?php if($item['status'] == 102 && !empty($item['reason'])): ?>
                    <div style="background: #fff1f0; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; color: #f5222d;">
                        <i class="icon-warning"></i> 驳回原因：<?= $item['reason'] ?>
                    </div>
                <?php endif; ?>
                
                <div class="agent-actions">
                    <?php if($item['status'] == 101): ?>
                        <!-- 待审核（未支付）：显示同意和驳回按钮 -->
                        <button type="button" class="action-btn btn-agree j-agree" data-agent-id="<?= $item['id'] ?>">
                            <i class="icon-check"></i> 同意
                        </button>
                        <button type="button" class="action-btn btn-refuse j-refuse" data-agent-id="<?= $item['id'] ?>">
                            <i class="icon-close"></i> 驳回
                        </button>
                    <?php elseif($item['status'] == 102 && $item['is_paid'] == 1): ?>
                        <!-- 已支付待审核：显示审核按钮（同意/驳回） -->
                        <button type="button" class="action-btn btn-agree j-agree" data-agent-id="<?= $item['id'] ?>">
                            <i class="icon-check"></i> 审核通过
                        </button>
                        <button type="button" class="action-btn btn-refuse j-refuse" data-agent-id="<?= $item['id'] ?>">
                            <i class="icon-close"></i> 审核驳回
                        </button>
                    <?php elseif($item['status'] == 102 && $item['is_paid'] == 0): ?>
                        <!-- 审核中（未支付）：显示删除按钮 -->
                        <button type="button" class="action-btn btn-delete j-delete" data-agent-id="<?= $item['id'] ?>">
                            <i class="icon-trash"></i> 删除
                        </button>
                    <?php elseif($item['status'] == 104): ?>
                        <!-- 已驳回：显示删除按钮 -->
                        <button type="button" class="action-btn btn-delete j-delete" data-agent-id="<?= $item['id'] ?>">
                            <i class="icon-trash"></i> 删除
                        </button>
                    <?php endif; ?>
                    <!-- 状态 103（已通过）不显示任何按钮 -->
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="icon-inbox"></i>
            <p>暂无代理商申请</p>
        </div>
    <?php endif; ?>

    <!-- 分页 -->
    <div class="inline-flex items-center gap-1 flex justify-center">
        <?= $list->render() ?>
    </div>
</div>

<!-- 驳回弹窗 -->
<div class="fixed inset-0 z-50 flex items-center justify-center hidden fixed inset-0 z-50 flex items-center justify-center hidden" id="refuse-modal">
    <div class="relative bg-white rounded-lg shadow-lg w-96 max-w-full">
        <div class="px-4 py-3 border-b font-medium">驳回申请</div>
        <div class="p-4">
            <textarea class="form-input w-full" rows="4" placeholder="请输入驳回原因（选填）"></textarea>
        </div>
        <div class="px-4 py-3 border-t flex justify-end gap-2">
            <span class="btn" data-fixed inset-0 z-50 flex items-center justify-center hidden-cancel>取消</span>
            <span class="btn" data-fixed inset-0 z-50 flex items-center justify-center hidden-confirm>确定</span>
        </div>
    </div>
</div>

<script src="/assets/store/js/jquery.min.js"></script>
<script src="/assets/common/js/jquery4-polyfill.js"></script>
<script src="/assets/store/js/amz-modal.js"></script>
<script src="/assets/store/js/store.js"></script>
<script>
$(function() {
    // 同意申请
    $('.j-agree').click(function() {
        var agentId = $(this).data('agent-id');
        var $btn = $(this);
        
        layer.confirm('确定要同意该代理商申请吗？', function(index) {
            layer.close(index);
            
            $.ajax({
                url: "<?= url('regionagent/agree') ?>",
                type: 'POST',
                dataType: 'json',
                data: { agent_id: agentId },
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
    
    // 驳回申请
    $('.j-refuse').click(function() {
        var agentId = $(this).data('agent-id');
        var $modal = $('#refuse-modal');
        var $textarea = $modal.find('textarea');
        
        $modal.modal({
            relatedTarget: this,
            onConfirm: function(e) {
                var reason = e.data || '';
                
                $.ajax({
                    url: "<?= url('regionagent/refuse') ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: { 
                        agent_id: agentId,
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
    
    // 删除申请
    $('.j-delete').click(function() {
        var agentId = $(this).data('agent-id');
        
        layer.confirm('确定要删除该代理商申请吗？删除后无法恢复', function(index) {
            layer.close(index);
            
            $.ajax({
                url: "<?= url('regionagent/delete') ?>",
                type: 'POST',
                dataType: 'json',
                data: { agent_id: agentId },
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
    
    // 微信打款
    $('.j-transfer').click(function() {
        var agentId = $(this).data('agent-id');
        var money = $(this).data('money');
        
        if (money <= 0) {
            $.show_error('可提现金额为0，无法打款');
            return;
        }
        
        layer.confirm('确定要给该代理商微信打款 ¥' + money + ' 元吗？', function(index) {
            layer.close(index);
            
            $.ajax({
                url: "<?= url('regionagent/wechatTransfer') ?>",
                type: 'POST',
                dataType: 'json',
                data: { agent_id: agentId },
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