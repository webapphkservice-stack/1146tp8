<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>砍价活动</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($list)): ?>
            <div class="h5-bargain">
                <?php foreach ($list as $a): ?>
                    <div class="h5-bargain__item" onclick="location.href='/h5/bargain/goods.html?active_id=<?= $a['active_id'] ?>'">
                        <div class="h5-bargain__info">
                            <p class="h5-bargain__name"><?= htmlspecialchars($a['title'] ?? '') ?></p>
                            <div class="h5-bargain__price">
                                <span class="h5-bargain__floor">底价 ¥<?= number_format($a['floor_price'] ?? 0, 2) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">⚔️</div><p class="h5-empty__text">暂无砍价活动</p></div>
        <?php endif; ?>
    </div>
</div>