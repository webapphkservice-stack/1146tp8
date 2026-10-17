<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>代理商费用设置</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="/assets/common/css/tailwind.css" />
    <link rel="stylesheet" href="/assets/store/css/app.css" />
    <style>
        .fee-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .fee-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .fee-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #c6a348 0%, #8a6d2e 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
        }
        .fee-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .fee-subtitle {
            font-size: 13px;
            color: #999;
            margin-top: 4px;
        }
        .fee-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px dashed #eee;
        }
        .fee-item:last-child {
            border-bottom: none;
        }
        .fee-info {
            display: flex;
            align-items: center;
        }
        .fee-badge {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            margin-right: 12px;
        }
        .badge-province {
            background: #fff7e6;
            color: #fa8c16;
        }
        .badge-city {
            background: #e6f7ff;
            color: #1890ff;
        }
        .badge-district {
            background: #f6ffed;
            color: #52c41a;
        }
        .fee-label {
            font-size: 15px;
            color: #333;
        }
        .fee-input-wrapper {
            display: flex;
            align-items: center;
        }
        .fee-input {
            width: 120px;
            height: 36px;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0 10px;
            font-size: 14px;
            text-align: right;
            margin-right: 5px;
        }
        .fee-input:focus {
            border-color: #c6a348;
            outline: none;
        }
        .fee-unit {
            font-size: 14px;
            color: #999;
        }
        .fee-actions {
            margin-top: 25px;
            text-align: right;
        }
        .btn-save {
            background: linear-gradient(135deg, #c6a348 0%, #8a6d2e 100%);
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
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .fee-tips {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #666;
        }
        .fee-tips i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
<div class=" p-4 text-red-600 text-sm" style="background: #fff;">
    <i class="icon-info-circle"></i> 提示：代理商费用设置用于设置各级代理商的申请费用
</div>

<div class="fee-card">
    <div class="fee-header">
        <div class="fee-icon">
            <i class="icon-cny"></i>
        </div>
        <div>
            <div class="fee-title">代理商费用设置</div>
            <div class="fee-subtitle">设置省代、市代、区代的申请费用</div>
        </div>
    </div>
    
    <div class="fee-tips">
        <i class="icon-lightbulb-o"></i>
        请设置各级代理商的申请费用，设置后代理商申请时需要支付相应费用
    </div>
    
    <form id="fee-form" method="POST" action="<?= url('regionagent/setFee') ?>">
        <div class="fee-item">
            <div class="fee-info">
                <div class="fee-badge badge-province">省代</div>
                <span class="fee-label">省级代理商费用</span>
            </div>
            <div class="fee-input-wrapper">
                <input type="number" class="fee-input" name="province_fee" 
                       value="<?= $feeSettings['province_fee'] ?>" 
                       placeholder="0.00" required min="0" step="0.01">
                <span class="fee-unit">元</span>
            </div>
        </div>
        
        <div class="fee-item">
            <div class="fee-info">
                <div class="fee-badge badge-city">市代</div>
                <span class="fee-label">市级代理商费用</span>
            </div>
            <div class="fee-input-wrapper">
                <input type="number" class="fee-input" name="city_fee" 
                       value="<?= $feeSettings['city_fee'] ?>" 
                       placeholder="0.00" required min="0" step="0.01">
                <span class="fee-unit">元</span>
            </div>
        </div>
        
        <div class="fee-item">
            <div class="fee-info">
                <div class="fee-badge badge-district">区代</div>
                <span class="fee-label">区级代理商费用</span>
            </div>
            <div class="fee-input-wrapper">
                <input type="number" class="fee-input" name="district_fee" 
                       value="<?= $feeSettings['district_fee'] ?>" 
                       placeholder="0.00" required min="0" step="0.01">
                <span class="fee-unit">元</span>
            </div>
        </div>
        
        <div class="fee-actions">
            <button type="submit" class="btn-save">
                <i class="icon-save"></i> 保存设置
            </button>
        </div>
    </form>
</div>

<script src="/assets/store/js/jquery.min.js"></script>
<script src="/assets/common/js/jquery4-polyfill.js"></script>
<script src="/assets/store/js/amz-modal.js"></script>
<script src="/assets/store/js/store.js"></script>
<script>
$(function() {
    // 提交表单
    $('#fee-form').submit(function(e) {
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