<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>申请数量设置</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="/assets/common/css/tailwind.css" />
    <link rel="stylesheet" href="/assets/store/css/app.css" />
    <style>
        .quantity-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .quantity-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .quantity-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #52c41a 0%, #389e0d 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
        }
        .quantity-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .quantity-subtitle {
            font-size: 13px;
            color: #999;
            margin-top: 4px;
        }
        .quantity-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
            border-bottom: 1px dashed #eee;
        }
        .quantity-item:last-child {
            border-bottom: none;
        }
        .quantity-info {
            display: flex;
            align-items: center;
        }
        .quantity-badge {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #f6ffed;
            color: #52c41a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 15px;
        }
        .quantity-label {
            font-size: 15px;
            color: #333;
        }
        .quantity-input-wrapper {
            display: flex;
            align-items: center;
        }
        .quantity-input {
            width: 120px;
            height: 36px;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0 10px;
            font-size: 14px;
            text-align: right;
            margin-right: 5px;
        }
        .quantity-input:focus {
            border-color: #52c41a;
            outline: none;
        }
        .quantity-unit {
            font-size: 14px;
            color: #999;
        }
        .quantity-actions {
            margin-top: 25px;
            text-align: right;
        }
        .btn-save {
            background: linear-gradient(135deg, #52c41a 0%, #389e0d 100%);
            color: #fff;
            border: none;
            padding: 10px 30px;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(82, 196, 26, 0.4);
        }
        .quantity-tips {
            background: #f6ffed;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #52c41a;
        }
        .quantity-tips i {
            margin-right: 5px;
        }
        
        /* 统计区域样式 */
        .stats-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .stats-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .stats-title i {
            margin-right: 8px;
            color: #1890ff;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .stat-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        .stat-header {
            padding: 12px 15px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .stat-header i {
            margin-right: 8px;
            font-size: 16px;
        }
        .province-bg {
            background: linear-gradient(135deg, #ff4d4f 0%, #cf1322 100%);
        }
        .city-bg {
            background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
        }
        .district-bg {
            background: linear-gradient(135deg, #52c41a 0%, #389e0d 100%);
        }
        .stat-body {
            padding: 15px;
        }
        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .stat-row:last-child {
            margin-bottom: 0;
        }
        .stat-label {
            font-size: 13px;
            color: #666;
        }
        .stat-value {
            font-size: 14px;
            font-weight: 600;
        }
        .stat-value.applied {
            color: #ff4d4f;
        }
        .stat-value.available {
            color: #52c41a;
        }
        .stat-progress {
            display: flex;
            align-items: center;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #eee;
        }
        .progress-bar {
            flex: 1;
            height: 6px;
            background: #f0f0f0;
            border-radius: 3px;
            overflow: hidden;
            margin-right: 10px;
        }
        .progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.5s ease;
        }
        .province-fill {
            background: linear-gradient(90deg, #ff4d4f, #ff7875);
        }
        .city-fill {
            background: linear-gradient(90deg, #1890ff, #69c0ff);
        }
        .district-fill {
            background: linear-gradient(90deg, #52c41a, #95de64);
        }
        .progress-text {
            font-size: 12px;
            color: #999;
            min-width: 40px;
            text-align: right;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* 已申请地区列表样式 */
        .region-list-section {
            margin-top: 25px;
        }
        .region-list-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            padding: 0 5px;
        }
        .region-list-title i {
            margin-right: 8px;
            color: #52c41a;
        }
        .region-level-group {
            margin-bottom: 20px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .region-level-header {
            padding: 12px 15px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .region-level-header.province-header {
            background: linear-gradient(135deg, #ff4d4f 0%, #cf1322 100%);
        }
        .region-level-header.city-header {
            background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
        }
        .region-level-header.district-header {
            background: linear-gradient(135deg, #52c41a 0%, #389e0d 100%);
        }
        .region-level-header i {
            margin-right: 8px;
        }
        .region-count {
            font-size: 13px;
            opacity: 0.9;
        }
        .region-table {
            width: 100%;
            border-collapse: collapse;
        }
        .region-table th {
            background: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-size: 13px;
            color: #666;
            font-weight: 500;
            border-bottom: 1px solid #eee;
        }
        .region-table td {
            padding: 12px 15px;
            font-size: 13px;
            color: #333;
            border-bottom: 1px solid #f0f0f0;
        }
        .region-table tr:last-child td {
            border-bottom: none;
        }
        .region-table tr:hover {
            background: #f8f9fa;
        }
        .region-name {
            font-weight: 500;
            color: #333;
        }
        .region-merger {
            font-size: 12px;
            color: #999;
            margin-top: 3px;
        }
        .region-user {
            color: #666;
        }
        .region-user-id {
            font-size: 11px;
            color: #999;
            margin-left: 5px;
        }
        .region-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .status-101 {
            background: #fff7e6;
            color: #fa8c16;
        }
        .status-102 {
            background: #e6f7ff;
            color: #1890ff;
        }
        .status-103 {
            background: #f6ffed;
            color: #52c41a;
        }
        .empty-tip {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 13px;
        }
        .empty-tip i {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
            color: #ddd;
        }
    </style>
</head>
<body>
<div class=" p-4 text-red-600 text-sm" style="background: #fff;">
    <i class="icon-info-circle"></i> 提示：设置各级区域可申请的代理商数量上限
</div>

<div class="quantity-card">
    <div class="quantity-header">
        <div class="quantity-icon">
            <i class="icon-users"></i>
        </div>
        <div>
            <div class="quantity-title">申请数量设置</div>
            <div class="quantity-subtitle">设置省、市、区各级可申请的代理商数量</div>
        </div>
    </div>
    
    <div class="quantity-tips">
        <i class="icon-lightbulb-o"></i>
        请设置各级区域可申请的代理商数量上限，设置后代理商申请时将进行数量限制
    </div>
    
    <!-- 统计卡片 -->
    <div class="stats-section">
        <div class="stats-title">
            <i class="icon-bar-chart"></i> 申请数量统计
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header province-bg">
                    <i class="icon-map-marker"></i>
                    <span>省级代理</span>
                </div>
                <div class="stat-body">
                    <div class="stat-row">
                        <span class="stat-label">已申请</span>
                        <span class="stat-value applied"><?= $appliedStats[1] ?> 个</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">剩余可用</span>
                        <span class="stat-value available"><?= $availableStats['province_available'] ?> 个</span>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-bar">
                            <div class="progress-fill province-fill" style="width: <?= $quantitySettings['province_quantity'] > 0 ? min(100, ($appliedStats[1] / $quantitySettings['province_quantity']) * 100) : 0 ?>"></div>
                        </div>
                        <span class="progress-text"><?= $quantitySettings['province_quantity'] > 0 ? round(($appliedStats[1] / $quantitySettings['province_quantity']) * 100, 1) : 0 ?></span>
                    </div>
                    
                    <!-- 已申请地区列表 -->
                    <div class="region-list-in-card">
                        <div class="region-list-header">
                            <i class="icon-list"></i> 已申请地区
                        </div>
                        <?php if (!empty($regionList[1])): ?>
                        <div class="region-tags">
                            <?php foreach ($regionList[1] as $region): ?>
                            <?php
                            $statusClass = 'status-' . $region['status'];
                            ?>
                            <div class="region-tag <?= $statusClass ?>">
                                <span class="region-tag-name"><?= $region['region_name'] ?></span>
                                <span class="region-tag-user"><?= $region['user_name'] ?: '未知' ?>(<?= $region['user_id'] ?>)</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="region-empty">暂无申请</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- 市级代理 -->
            <div class="stat-card">
                <div class="stat-header city-bg">
                    <i class="icon-building"></i>
                    <span>市级代理</span>
                </div>
                <div class="stat-body">
                    <div class="stat-row">
                        <span class="stat-label">已申请</span>
                        <span class="stat-value applied"><?= $appliedStats[2] ?> 个</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">剩余可用</span>
                        <span class="stat-value available"><?= $availableStats['city_available'] ?> 个</span>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-bar">
                            <div class="progress-fill city-fill" style="width: <?= $quantitySettings['city_quantity'] > 0 ? min(100, ($appliedStats[2] / $quantitySettings['city_quantity']) * 100) : 0 ?>"></div>
                        </div>
                        <span class="progress-text"><?= $quantitySettings['city_quantity'] > 0 ? round(($appliedStats[2] / $quantitySettings['city_quantity']) * 100, 1) : 0 ?></span>
                    </div>
                    
                    <!-- 已申请地区列表 -->
                    <div class="region-list-in-card">
                        <div class="region-list-header">
                            <i class="icon-list"></i> 已申请地区
                        </div>
                        <?php if (!empty($regionList[2])): ?>
                        <div class="region-tags">
                            <?php foreach ($regionList[2] as $region): ?>
                            <?php
                            $statusClass = 'status-' . $region['status'];
                            ?>
                            <div class="region-tag <?= $statusClass ?>">
                                <span class="region-tag-name"><?= $region['region_name'] ?></span>
                                <span class="region-tag-user"><?= $region['user_name'] ?: '未知' ?>(<?= $region['user_id'] ?>)</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="region-empty">暂无申请</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- 区级代理 -->
            <div class="stat-card">
                <div class="stat-header district-bg">
                    <i class="icon-home"></i>
                    <span>区级代理</span>
                </div>
                <div class="stat-body">
                    <div class="stat-row">
                        <span class="stat-label">已申请</span>
                        <span class="stat-value applied"><?= $appliedStats[3] ?> 个</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">剩余可用</span>
                        <span class="stat-value available"><?= $availableStats['district_available'] ?> 个</span>
                    </div>
                    <div class="stat-progress">
                        <div class="progress-bar">
                            <div class="progress-fill district-fill" style="width: <?= $quantitySettings['district_quantity'] > 0 ? min(100, ($appliedStats[3] / $quantitySettings['district_quantity']) * 100) : 0 ?>"></div>
                        </div>
                        <span class="progress-text"><?= $quantitySettings['district_quantity'] > 0 ? round(($appliedStats[3] / $quantitySettings['district_quantity']) * 100, 1) : 0 ?></span>
                    </div>
                    
                    <!-- 已申请地区列表 -->
                    <div class="region-list-in-card">
                        <div class="region-list-header">
                            <i class="icon-list"></i> 已申请地区
                        </div>
                        <?php if (!empty($regionList[3])): ?>
                        <div class="region-tags">
                            <?php foreach ($regionList[3] as $region): ?>
                            <?php
                            $statusClass = 'status-' . $region['status'];
                            ?>
                            <div class="region-tag <?= $statusClass ?>">
                                <span class="region-tag-name"><?= $region['region_name'] ?></span>
                                <span class="region-tag-user"><?= $region['user_name'] ?: '未知' ?>(<?= $region['user_id'] ?>)</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="region-empty">暂无申请</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <form class=" " method="POST" action="<?= url('regionagent/setQuantity') ?>">
        <div class="quantity-item">
            <div class="quantity-info">
                <div class="quantity-badge">
                    <i class="icon-map-marker"></i>
                </div>
                <span class="quantity-label">省级可申请数量</span>
            </div>
            <div class="quantity-input-wrapper">
                <input type="number" class="quantity-input" name="province_quantity" value="<?= $quantitySettings['province_quantity'] ?>" placeholder="0" required min="0">
                <span class="quantity-unit">个</span>
            </div>
        </div>
        
        <div class="quantity-item">
            <div class="quantity-info">
                <div class="quantity-badge">
                    <i class="icon-building"></i>
                </div>
                <span class="quantity-label">市级可申请数量</span>
            </div>
            <div class="quantity-input-wrapper">
                <input type="number" class="quantity-input" name="city_quantity" value="<?= $quantitySettings['city_quantity'] ?>" placeholder="0" required min="0">
                <span class="quantity-unit">个</span>
            </div>
        </div>
        
        <div class="quantity-item">
            <div class="quantity-info">
                <div class="quantity-badge">
                    <i class="icon-home"></i>
                </div>
                <span class="quantity-label">区级可申请数量</span>
            </div>
            <div class="quantity-input-wrapper">
                <input type="number" class="quantity-input" name="district_quantity" value="<?= $quantitySettings['district_quantity'] ?>" placeholder="0" required min="0">
                <span class="quantity-unit">个</span>
            </div>
        </div>
        
        <div class="quantity-actions">
            <button type="submit" class="btn-save">
                <i class="icon-save"></i> 保存设置
            </button>
        </div>
    </form>
    
    <!-- 已申请地区列表样式 -->
    <style>
        .region-list-in-card {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #eee;
        }
        .region-list-header {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .region-list-header i {
            margin-right: 5px;
            color: #999;
        }
        .region-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            max-height: 120px;
            overflow-y: auto;
        }
        .region-tag {
            display: inline-flex;
            flex-direction: column;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            background: #f5f5f5;
            border: 1px solid #e8e8e8;
        }
        .region-tag.status-101 {
            background: #fff7e6;
            border-color: #ffd591;
        }
        .region-tag.status-102 {
            background: #e6f7ff;
            border-color: #91d5ff;
        }
        .region-tag.status-103 {
            background: #f6ffed;
            border-color: #b7eb8f;
        }
        .region-tag-name {
            font-weight: 500;
            color: #333;
        }
        .region-tag-user {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
        }
        .region-empty {
            font-size: 12px;
            color: #999;
            padding: 10px 0;
            text-align: center;
        }
    </style>
</div>

<script src="/assets/store/js/jquery.min.js"></script>
<script src="/assets/common/js/jquery4-polyfill.js"></script>
<script src="/assets/store/js/amz-modal.js"></script>
<script src="/assets/store/js/store.js"></script>
<script>
$(function() {
    // 提交表单
    $('form').submit(function(e) {
        e.preventDefault();
        var $form = $(this);
        var data = $form.serialize();
        
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.code === 1) {
                    $.show_success(result.msg);
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
</script>
</body>
</html>