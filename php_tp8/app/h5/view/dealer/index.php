<?php
/** @var array $words @var array|null $dealer @var array $user @var bool $isDealer @var string $background */
$W = $words['index']['words'] ?? [];
$vw = fn($k, $d = '') => htmlspecialchars((string)($W[$k]['value'] ?? $d));
?>
<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back">‹</a>
        <h4><?= htmlspecialchars($words['index']['title']['value'] ?? '分销中心') ?></h4>
    </div>

    <div class="h5-body">
        <?php if ($isDealer): ?>
            <!-- 头部背景 -->
            <div class="h5-dealer-bg">
                <?php if (!empty($background)): ?>
                    <img src="<?= htmlspecialchars($background) ?>" alt=""/>
                <?php endif; ?>
            </div>

            <!-- 用户信息卡 -->
            <div class="h5-dealer-card">
                <div class="h5-dealer-card__user">
                    <div class="h5-dealer-card__avatar">
                        <?php if (!empty($user['avatarUrl'])): ?>
                            <img src="<?= htmlspecialchars($user['avatarUrl']) ?>" alt=""/>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="4"/><path d="M4.4 20.8c0-3.8 3.4-6.2 7.6-6.2s7.6 2.4 7.6 6.2"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="h5-dealer-card__meta">
                        <p class="h5-dealer-card__name"><?= htmlspecialchars($user['nickName']) ?></p>
                        <p class="h5-dealer-card__referee"><?= $vw('referee', '推荐人') ?>：<?= htmlspecialchars($refereeName) ?></p>
                    </div>
                </div>

                <div class="h5-dealer-capital">
                    <div class="h5-dealer-capital__left">
                        <p class="h5-dealer-capital__row"><span><?= $vw('money', '可提现') ?></span><b>¥<?= number_format($dealer['money'] ?? 0, 2) ?></b></p>
                        <p class="h5-dealer-capital__row"><span><?= $vw('freeze_money', '待提现') ?></span><b>¥<?= number_format($dealer['freeze_money'] ?? 0, 2) ?></b></p>
                    </div>
                    <a class="h5-dealer-capital__btn" href="/h5/dealer/withdrawApply.html"><?= $vw('withdraw', '去提现') ?></a>
                </div>
                <div class="h5-dealer-capital__total">
                    <span><?= $vw('total_money', '已提现金额') ?></span>
                    <b>¥<?= number_format($dealer['total_money'] ?? 0, 2) ?></b>
                </div>
            </div>

            <!-- 操作入口 -->
            <div class="h5-dealer-ops">
                <a class="h5-dealer-op" href="/h5/dealer/withdraw.html">
                    <span class="h5-dealer-op__icon" style="background:#fff7e6;color:#f9ba21;">￥</span>
                    <span class="h5-dealer-op__text"><?= htmlspecialchars($words['withdraw_list']['title']['value'] ?? '提现明细') ?></span>
                </a>
                <a class="h5-dealer-op" href="/h5/dealer/order.html">
                    <span class="h5-dealer-op__icon" style="background:#fff0f0;color:#ff7575;">单</span>
                    <span class="h5-dealer-op__text"><?= htmlspecialchars($words['order']['title']['value'] ?? '分销订单') ?></span>
                </a>
                <a class="h5-dealer-op" href="/h5/dealer/team.html">
                    <span class="h5-dealer-op__icon" style="background:#eefaf3;color:#59c78e;">队</span>
                    <span class="h5-dealer-op__text"><?= htmlspecialchars($words['team']['title']['value'] ?? '我的团队') ?></span>
                </a>
                <a class="h5-dealer-op" href="/h5/dealer/qrcode.html">
                    <span class="h5-dealer-op__icon" style="background:#eef4ff;color:#5fa5ff;">码</span>
                    <span class="h5-dealer-op__text"><?= htmlspecialchars($words['qrcode']['title']['value'] ?? '推广二维码') ?></span>
                </a>
            </div>
        <?php else: ?>
            <!-- 未成为分销商 -->
            <div class="h5-dealer-no">
                <div class="h5-dealer-no__icon">🤝</div>
                <p class="h5-dealer-no__msg"><?= $vw('not_dealer', '您还不是分销商，请先提交申请') ?></p>
                <a class="h5-dealer-btn" href="/h5/dealer/apply.html"><?= $vw('apply_now', '立即加入') ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>