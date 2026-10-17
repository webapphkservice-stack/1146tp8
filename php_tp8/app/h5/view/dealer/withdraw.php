<?php
/** @var array $words @var array|null $dealer @var bool $isDealer @var array $list @var int $status */
$W = $words['withdraw_list']['words'] ?? [];
$vw = fn($k, $d = '') => htmlspecialchars((string)($W[$k]['value'] ?? $d));
$tabs = [
    -1 => $vw('all', '全部'),
    10 => $vw('apply_10', '审核中'),
    20 => $vw('apply_20', '审核通过'),
    40 => $vw('apply_40', '已打款'),
    30 => $vw('apply_30', '驳回'),
];
$payTypeText = [10 => '微信', 20 => '支付宝', 30 => '银行卡'];
?>
<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back">‹</a>
        <h4><?= htmlspecialchars($words['withdraw_list']['title']['value'] ?? '提现明细') ?></h4>
    </div>

    <div class="h5-body">
        <?php if (!$isDealer): ?>
            <div class="h5-dealer-no">
                <div class="h5-dealer-no__icon">￥</div>
                <p class="h5-dealer-no__msg">您还不是分销商，暂无提现记录</p>
                <a class="h5-dealer-btn" href="/h5/dealer/apply.html">立即加入</a>
            </div>
        <?php else: ?>
            <div class="h5-dealer-wdhead">
                <div class="h5-dealer-wdhead__info">
                    <span>可提现佣金</span>
                    <b>¥<?= number_format((float)($dealer['money'] ?? 0), 2) ?></b>
                </div>
                <a class="h5-dealer-capital__btn" href="/h5/dealer/withdrawApply.html">申请提现</a>
            </div>

            <div class="h5-dealer-tabs">
                <?php foreach ($tabs as $k => $label): ?>
                    <a class="h5-dealer-tabs__item<?= (int)$status === (int)$k ? ' is-active' : '' ?>"
                       href="/h5/dealer/withdraw.html?status=<?= $k ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($list)): ?>
                <ul class="h5-dealer-list">
                    <?php foreach ($list as $r): ?>
                        <li class="h5-dealer-list__item h5-dealer-list__item--order">
                            <div class="h5-dealer-list__info">
                                <p class="h5-dealer-list__name">提现 ¥<?= number_format((float)$r['money'], 2) ?></p>
                                <p class="h5-dealer-list__desc"><?= htmlspecialchars($payTypeText[(int)($r['pay_type'] ?? 0)] ?? '') ?> · <?= date('Y-m-d H:i', (int)$r['create_time']) ?></p>
                            </div>
                            <div class="h5-dealer-list__right">
                                <span class="h5-dealer-list__tag<?= (int)$r['apply_status'] === 40 ? ' is-done' : '' ?>"><?= htmlspecialchars($r['apply_status_text'] ?? '') ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="h5-empty"><div class="h5-empty__icon">￥</div><p class="h5-empty__text">暂无提现记录</p></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>