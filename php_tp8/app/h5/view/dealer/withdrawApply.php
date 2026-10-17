<?php
/**
 * @var array $words @var array|null $dealer @var bool $isDealer @var array $setting @var string $background
 */
$W = $words['withdraw_apply'] ?? $words['apply'] ?? [];
$vw = fn($k, $d = '') => htmlspecialchars((string)($W['words'][$k]['value'] ?? $d));

// 提现方式白名单（10=微信 20=支付宝 30=银行卡）
$payTypeMap = [10 => '微信', 20 => '支付宝', 30 => '银行卡'];
$allowTypes = array_map('strval', (array)($setting['pay_type'] ?? []));
$payTypes   = [];
foreach ($payTypeMap as $k => $label) {
    if (empty($allowTypes) || in_array((string)$k, $allowTypes, true)) {
        $payTypes[$k] = $label;
    }
}
$minMoney   = (float)($setting['min_money'] ?? 0);
$canMoney   = (float)($dealer['money'] ?? 0);
$firstType  = (int)(array_key_first($payTypes) ?: 10);
?>
<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back">‹</a>
        <h4><?= htmlspecialchars($words['withdraw_apply']['title']['value'] ?? '申请提现') ?></h4>
    </div>

    <div class="h5-body">
        <?php if (!$isDealer): ?>
            <div class="h5-dealer-no">
                <div class="h5-dealer-no__icon">￥</div>
                <p class="h5-dealer-no__msg">您还不是分销商，无法申请提现</p>
                <a class="h5-dealer-btn" href="/h5/dealer/apply.html">立即加入</a>
            </div>
        <?php else: ?>
            <div class="h5-dealer-bg">
                <?php if (!empty($background)): ?>
                    <img src="<?= htmlspecialchars($background) ?>" alt=""/>
                <?php endif; ?>
            </div>

            <div class="h5-dealer-form">
                <div class="h5-dealer-capital h5-dealer-capital--plain">
                    <div class="h5-dealer-capital__left">
                        <p class="h5-dealer-capital__row"><span>可提现佣金</span><b>¥<?= number_format($canMoney, 2) ?></b></p>
                        <?php if ($minMoney > 0): ?>
                            <p class="h5-dealer-capital__row h5-dealer-capital__row--tip"><span>最低提现金额 ¥<?= number_format($minMoney, 2) ?></span></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="h5-dealer-field">
                    <label>提现金额</label>
                    <input type="number" id="w-money" min="0" step="0.01"
                           placeholder="请输入提现金额（≥<?= number_format($minMoney, 2) ?>）" value=""/>
                </div>

                <div class="h5-dealer-field">
                    <label>提现方式</label>
                    <div class="h5-dealer-pay">
                        <?php foreach ($payTypes as $k => $label): ?>
                            <label class="h5-dealer-pay__item<?= $k === $firstType ? ' is-active' : '' ?>">
                                <input type="radio" name="pay_type" value="<?= $k ?>"<?= $k === $firstType ? ' checked' : '' ?>/>
                                <span><?= $label ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 支付宝 -->
                <div class="h5-dealer-group" data-group="20"<?= $firstType === 20 ? '' : ' hidden' ?>>
                    <div class="h5-dealer-field">
                        <label>支付宝姓名</label>
                        <input type="text" id="w-alipay-name" placeholder="请输入支付宝认证姓名"/>
                    </div>
                    <div class="h5-dealer-field">
                        <label>支付宝账号</label>
                        <input type="text" id="w-alipay-account" placeholder="请输入支付宝账号"/>
                    </div>
                </div>

                <!-- 银行卡 -->
                <div class="h5-dealer-group" data-group="30"<?= $firstType === 30 ? '' : ' hidden' ?>>
                    <div class="h5-dealer-field">
                        <label>开户银行</label>
                        <input type="text" id="w-bank-name" placeholder="请输入开户银行"/>
                    </div>
                    <div class="h5-dealer-field">
                        <label>开户人姓名</label>
                        <input type="text" id="w-bank-account" placeholder="请输入开户人姓名"/>
                    </div>
                    <div class="h5-dealer-field">
                        <label>银行卡号</label>
                        <input type="text" id="w-bank-card" placeholder="请输入银行卡号"/>
                    </div>
                </div>

                <button type="button" class="h5-dealer-btn h5-dealer-btn--block" id="w-submit">确认提现</button>
            </div>

            <script>
            (function () {
                var canMoney = <?= json_encode($canMoney) ?>;
                var minMoney = <?= json_encode($minMoney) ?>;

                // 提现方式切换
                var radios = document.querySelectorAll('input[name="pay_type"]');
                function syncGroups() {
                    var val = '';
                    radios.forEach(function (r) { if (r.checked) { val = r.value; } });
                    document.querySelectorAll('.h5-dealer-group').forEach(function (g) {
                        g.hidden = (g.getAttribute('data-group') !== val);
                    });
                    document.querySelectorAll('.h5-dealer-pay__item').forEach(function (it) {
                        var input = it.querySelector('input');
                        it.classList.toggle('is-active', input && input.checked);
                    });
                }
                radios.forEach(function (r) { r.addEventListener('change', syncGroups); });
                syncGroups();

                var btn = document.getElementById('w-submit');
                btn.addEventListener('click', function () {
                    var money = parseFloat(document.getElementById('w-money').value || '0');
                    var payType = 10;
                    radios.forEach(function (r) { if (r.checked) { payType = parseInt(r.value, 10); } });

                    if (!(money > 0)) { alert('请输入正确的提现金额'); return; }
                    if (money > canMoney) { alert('提现金额不能大于可提现佣金'); return; }
                    if (minMoney > 0 && money < minMoney) { alert('提现金额不能小于 ' + minMoney + ' 元'); return; }

                    var params = { money: money, pay_type: payType };
                    if (payType === 20) {
                        params.alipay_name = (document.getElementById('w-alipay-name').value || '').trim();
                        params.alipay_account = (document.getElementById('w-alipay-account').value || '').trim();
                        if (!params.alipay_name || !params.alipay_account) { alert('请填写完整的支付宝信息'); return; }
                    } else if (payType === 30) {
                        params.bank_name = (document.getElementById('w-bank-name').value || '').trim();
                        params.bank_account = (document.getElementById('w-bank-account').value || '').trim();
                        params.bank_card = (document.getElementById('w-bank-card').value || '').trim();
                        if (!params.bank_name || !params.bank_account || !params.bank_card) { alert('请填写完整的银行卡信息'); return; }
                    }

                    btn.disabled = true;
                    fetch('/h5/dealer/withdrawSubmit.html', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams(params).toString()
                    }).then(function (r) { return r.json(); }).then(function (res) {
                        if (res.code === 1) {
                            alert(res.msg || '提交成功');
                            location.href = (res.data && res.data.redirect) || '/h5/dealer/withdraw.html';
                        } else {
                            alert(res.msg || '提交失败');
                            btn.disabled = false;
                        }
                    }).catch(function () {
                        alert('网络异常，请稍后再试');
                        btn.disabled = false;
                    });
                });
            })();
            </script>
        <?php endif; ?>
    </div>
</div>