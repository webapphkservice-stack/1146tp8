<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>税务设置</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="/assets/common/css/tailwind.css" />
    <link rel="stylesheet" href="/assets/store/css/app.css" />
    <style>
        .tax-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .tax-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .tax-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f5222d 0%, #fa541c 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
        }
        .tax-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .tax-subtitle {
            font-size: 13px;
            color: #999;
            margin-top: 4px;
        }
        .tax-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
            border-bottom: 1px dashed #eee;
        }
        .tax-item:last-child {
            border-bottom: none;
        }
        .tax-info {
            display: flex;
            align-items: center;
        }
        .tax-badge {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #fff1f0;
            color: #f5222d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 15px;
        }
        .tax-label {
            font-size: 15px;
            color: #333;
        }
        .tax-input-wrapper {
            display: flex;
            align-items: center;
        }
        .tax-input {
            width: 120px;
            height: 36px;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0 10px;
            font-size: 14px;
            text-align: right;
            margin-right: 5px;
        }
        .tax-input:focus {
            border-color: #f5222d;
            outline: none;
        }
        .tax-unit {
            font-size: 14px;
            color: #999;
        }
        .tax-actions {
            margin-top: 25px;
            text-align: right;
        }
        .btn-save {
            background: linear-gradient(135deg, #f5222d 0%, #fa541c 100%);
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
            box-shadow: 0 4px 12px rgba(245, 34, 45, 0.4);
        }
        .tax-tips {
            background: #fff1f0;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #f5222d;
        }
        .tax-tips i {
            margin-right: 5px;
        }
        .tax-calculate {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
        }
        .calculate-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .calculate-example {
            font-size: 13px;
            color: #666;
            line-height: 1.8;
        }
        .calculate-example .highlight {
            color: #f5222d;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class=" p-4 text-red-600 text-sm" style="background: #fff;">
    <i class="icon-info-circle"></i> 提示：税务设置用于设置代理商提现时需要扣除的税费比例
</div>

<div class="tax-card">
    <div class="tax-header">
        <div class="tax-icon">
            <i class="icon-calculator"></i>
        </div>
        <div>
            <div class="tax-title">税务设置</div>
            <div class="tax-subtitle">设置代理商提现时的税费扣除比例</div>
        </div>
    </div>
    
    <div class="tax-tips">
        <i class="icon-exclamation-circle"></i>
        设置税率后，代理商提现时会自动扣除相应比例的税费
    </div>
    
    <form id="tax-form" method="POST" action="<?= url('regionagent/setTaxRate') ?>">
        <div class="tax-item">
            <div class="tax-info">
                <div class="tax-badge">
                    <i class="icon-percent"></i>
                </div>
                <span class="tax-label">提现税率</span>
            </div>
            <div class="tax-input-wrapper">
                <input type="number" class="tax-input" name="tax_rate" 
                       value="<?= $taxRateSettings['withdraw_tax_rate'] ?>" 
                       placeholder="0.00" required min="0" max="100" step="0.01">
                <span class="tax-unit">%</span>
            </div>
        </div>
        
        <div class="tax-calculate">
            <div class="calculate-title">💡 计算示例</div>
            <div class="calculate-example">
                <p>如果设置税率为 <span class="highlight">5%</span>：</p>
                <p>• 代理商提现 <span class="highlight">¥100.00</span> 时，实际到账 <span class="highlight">¥95.00</span></p>
                <p>• 扣除税费 <span class="highlight">¥5.00</span></p>
            </div>
        </div>
        
        <div class="tax-actions">
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
    $('#tax-form').submit(function(e) {
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