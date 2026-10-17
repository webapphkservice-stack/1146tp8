<?php
/** @var array $words @var array|null $dealer @var bool $isDealer @var array $list @var int $level */
$W = $words['team']['words'] ?? [];
$vw = fn($k, $d = '') => htmlspecialchars((string)($W[$k]['value'] ?? $d));
$total = $dealer ? ((int)$dealer['first_num'] + (int)$dealer['second_num'] + (int)$dealer['third_num']) : 0;
$tabs = [-1 => $vw('all', '全部'), 1 => $vw('first', '一级团队'), 2 => $vw('second', '二级团队'), 3 => $vw('third', '三级团队')];
if (!isset($tabs[-1])) { $tabs = [-1 => '全部'] + $tabs; }
$avatar = function ($p) {
    if ($p === '') return '';
    return stripos($p, 'http') === 0 ? $p : '/' . ltrim($p, '/');
};
?>
<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back">‹</a>
        <h4><?= htmlspecialchars($words['team']['title']['value'] ?? '我的团队') ?></h4>
    </div>

    <div class="h5-body">
        <?php if (!$isDealer): ?>
            <div class="h5-dealer-no">
                <div class="h5-dealer-no__icon">🤝</div>
                <p class="h5-dealer-no__msg">您还不是分销商，暂无团队</p>
                <a class="h5-dealer-btn" href="/h5/dealer/apply.html">立即加入</a>
            </div>
        <?php else: ?>
            <div class="h5-dealer-stat">
                <div class="h5-dealer-stat__main">
                    <b><?= $total ?></b>
                    <span><?= $vw('total_team', '团队总人数') ?></span>
                </div>
                <div class="h5-dealer-stat__sub">
                    <div><b><?= (int)$dealer['first_num'] ?></b><span><?= $vw('first', '一级团队') ?></span></div>
                    <div><b><?= (int)$dealer['second_num'] ?></b><span><?= $vw('second', '二级团队') ?></span></div>
                    <div><b><?= (int)$dealer['third_num'] ?></b><span><?= $vw('third', '三级团队') ?></span></div>
                </div>
            </div>

            <div class="h5-dealer-tabs">
                <?php foreach ($tabs as $lv => $label): ?>
                    <a class="h5-dealer-tabs__item<?= (int)$level === (int)$lv ? ' is-active' : '' ?>"
                       href="/h5/dealer/team.html?level=<?= $lv ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($list)): ?>
                <ul class="h5-dealer-list">
                    <?php foreach ($list as $m): ?>
                        <li class="h5-dealer-list__item">
                            <div class="h5-dealer-list__avatar">
                                <?php if ($avatar($m['avatarUrl'] ?? '') !== ''): ?>
                                    <img src="<?= htmlspecialchars($avatar($m['avatarUrl'])) ?>" alt=""/>
                                <?php else: ?>
                                    <span>👤</span>
                                <?php endif; ?>
                            </div>
                            <div class="h5-dealer-list__info">
                                <p class="h5-dealer-list__name"><?= htmlspecialchars($m['nickName'] ?: ('用户' . $m['user_id'])) ?></p>
                                <p class="h5-dealer-list__desc"><?= (int)$m['level'] ?> 级 · <?= date('Y-m-d H:i', (int)$m['create_time']) ?></p>
                            </div>
                            <span class="h5-dealer-list__badge">L<?= (int)$m['level'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="h5-empty"><div class="h5-empty__icon">👥</div><p class="h5-empty__text">暂无团队成员</p></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>