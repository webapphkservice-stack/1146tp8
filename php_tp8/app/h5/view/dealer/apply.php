<?php
/** @var array $words @var bool $isDealer @var bool $isApplying @var string $refereeName @var string $license @var string $background */
$W = $words['apply']['words'] ?? [];
$vw = fn($k, $d = '') => htmlspecialchars((string)($W[$k]['value'] ?? $d));
?>
<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back">‹</a>
        <h4><?= htmlspecialchars($words['apply']['title']['value'] ?? '申请成为分销商') ?></h4>
    </div>

    <div class="h5-body">
        <div class="h5-dealer-bg">
            <?php if (!empty($background)): ?>
                <img src="<?= htmlspecialchars($background) ?>" alt=""/>
            <?php endif; ?>
        </div>

        <?php if ($isDealer): ?>
            <!-- 已是分销商 -->
            <div class="h5-dealer-no">
                <div class="h5-dealer-no__icon">✅</div>
                <p class="h5-dealer-no__msg">您已经是分销商，无需重复申请</p>
                <a class="h5-dealer-btn" href="/h5/dealer/index.html">返回分销中心</a>
            </div>
        <?php elseif ($isApplying): ?>
            <!-- 审核中 -->
            <div class="h5-dealer-no">
                <div class="h5-dealer-no__icon">⏳</div>
                <p class="h5-dealer-no__msg"><?= $vw('wait_audit', '您的申请已受理，正在进行信息核验，请耐心等待。') ?></p>
                <a class="h5-dealer-btn" href="/h5/page/index.html"><?= $vw('goto_mall', '去商城逛逛') ?></a>
            </div>
        <?php else: ?>
            <!-- 申请表单 -->
            <div class="h5-dealer-form">
                <p class="h5-dealer-form__title"><?= $vw('title', '请填写申请信息') ?></p>

                <div class="h5-dealer-field">
                    <label>邀请人</label>
                    <div class="h5-dealer-field__static"><?= htmlspecialchars($refereeName) ?></div>
                </div>

                <div class="h5-dealer-field">
                    <label>姓名</label>
                    <input type="text" id="d-name" placeholder="请输入真实姓名" value=""/>
                </div>

                <div class="h5-dealer-field">
                    <label>手机号</label>
                    <input type="tel" id="d-mobile" maxlength="11" placeholder="请输入手机号" value=""/>
                </div>

                <?php if ($license !== ''): ?>
                    <div class="h5-dealer-field">
                        <label>协议</label>
                        <div class="h5-dealer-field__static h5-dealer-field__static--text"><?= nl2br(htmlspecialchars($license)) ?></div>
                    </div>
                <?php endif; ?>

                <label class="h5-dealer-agree">
                    <input type="checkbox" id="d-read"/>
                    <span>我已阅读并了解【<?= $vw('license', '分销商申请协议') ?>】</span>
                </label>

                <button type="button" class="h5-dealer-btn h5-dealer-btn--block" id="d-submit"><?= $vw('submit', '申请成为经销商') ?></button>
            </div>

            <script>
            (function () {
                var btn = document.getElementById('d-submit');
                btn.addEventListener('click', function () {
                    var name = (document.getElementById('d-name').value || '').trim();
                    var mobile = (document.getElementById('d-mobile').value || '').trim();
                    var read = document.getElementById('d-read').checked;
                    if (!read) { alert('请先阅读并勾选分销商申请协议'); return; }
                    if (!name) { alert('请输入真实姓名'); return; }
                    if (!/^1\d{10}$/.test(mobile)) { alert('请输入正确的 11 位手机号'); return; }

                    btn.disabled = true;
                    var body = new URLSearchParams({ name: name, mobile: mobile });
                    fetch('/h5/dealer/applySubmit.html', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString()
                    }).then(function (r) { return r.json(); }).then(function (res) {
                        if (res.code === 1) {
                            alert(res.msg || '提交成功');
                            location.href = (res.data && res.data.redirect) || '/h5/dealer/apply.html';
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