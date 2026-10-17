<?php
/** @var array $words @var bool $isDealer @var array $list @var int $settled @var int $myId */
$W = $words['order']['words'] ?? [];
$vw = fn($k, $d = '') => htmlspecialchars((string)($W[$k]['value'] ?? $d));
$tabs = [-1 => $vw('all', '全部'), 20 => $vw('unsettled', '未结算'), 10 => $vw('settled', '已结算')];
?>
<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back">‹</a>
        <h4><?= htmlspecialchars($words['order']['title']['value'] ?? '分销订单') ?></h4>
    </div>

    <div class="h5-body">
        <?php if (!$isDealer): ?>
            <div class="h5-dealer-no">
                <div class="h5-dealer-no__icon">📦</div>
                <p class="h5-dealer-no__msg">您还不是分销商，暂无分销订单</p>
                <a class="h5-dealer-btn" href="/h5/dealer/apply.html">立即加入</a>
            </div>
        <?php else: ?>
            <div class="h5-dealer-tabs">
                <?php foreach ($tabs as $k => $label): ?>
                    <a class="h5-dealer-tabs__item<?= (int)$settled === (int)$k ? ' is-active' : '' ?>"
                       href="/h5/dealer/order.html?settled=<?= $k ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($list)): ?>
                <ul class="h5-dealer-list">
                    <?php foreach ($list as $o): ?>
                        <?php
                        // 当前用户在订单中的佣金
                        $commission = 0.0;
                        if ((int)($o['first_user_id'] ?? 0) === $myId)  { $commission = (float)($o['first_money'] ?? 0); }
                        if ((int)($o['second_user_id'] ?? 0) === $myId) { $commission = (float)($o['second_money'] ?? 0); }
                        if ((int)($o['third_user_id'] ?? 0) === $myId)  { $commission = (float)($o['third_money'] ?? 0); }
                        $isSettled = (int)($o['is_settled'] ?? 0) === 1;
                        ?>
                        <li class="h5-dealer-list__item h5-dealer-list__item--order">
                            <div class="h5-dealer-list__info">
                                <p class="h5-dealer-list__name">订单号：<?= htmlspecialchars($o['order_no'] ?? '') ?></p>
                                <p class="h5-dealer-list__desc">下单金额 ¥<?= number_format((float)($o['order_price'] ?: ($o['pay_price'] ?? 0)), 2) ?></p>
                                <p class="h5-dealer-list__desc"><?= date('Y-m-d H:i', (int)($o['create_time'] ?? 0)) ?></p>
                            </div>
                            <div class="h5-dealer-list__right">
                                <p class="h5-dealer-list__money">+¥<?= number_format($commission, 2) ?></p>
                                <span class="h5-dealer-list__tag<?= $isSettled ? ' is-done' : '' ?>"><?= $isSettled ? '已结算' : '未结算' ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="h5-empty"><div class="h5-empty__icon">📦</div><p class="h5-empty__text">暂无分销订单</p></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>