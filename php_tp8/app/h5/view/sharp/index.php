<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>整点秒杀</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($tabbar)): ?>
            <div style="display:flex;overflow-x:auto;padding:10px 12px 0;gap:8px;background:#fff;">
                <?php foreach ($tabbar as $idx => $t): ?>
                    <span style="white-space:nowrap;padding:6px 14px;border-radius:14px;font-size:12px;
                        <?= $idx === 0 ? 'background:#e54d42;color:#fff;font-weight:600;' : 'background:#f5f5f7;color:#666;' ?>">
                        <?= $t['label'] ?? ($t['active_time'] ?? '') ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($goodsList)): ?>
            <div class="h5-sharp">
                <div class="h5-sharp__head">
                    <span class="h5-sharp__title">正在疯抢</span>
                    <a class="h5-sharp__more">更多 ›</a>
                </div>
                <ul class="h5-sharp__list column-2">
                    <?php foreach ($goodsList as $g): ?>
                        <li class="h5-sharp__item" onclick="location.href='/h5/sharp/goods.html?active_time_id=<?= $g['active_time_id'] ?? 0 ?>&sharp_goods_id=<?= $g['sharp_goods_id'] ?? 0 ?>'">
                            <img class="h5-sharp__img" src="<?= htmlspecialchars($g['goods_image'] ?? '') ?>"/>
                            <p class="h5-sharp__name"><?= htmlspecialchars($g['goods_name'] ?? '') ?></p>
                            <p class="h5-sharp__price">
                                <span class="h5-sharp__now">¥<?= number_format($g['seckill_price'] ?? 0, 2) ?></span>
                                <span class="h5-sharp__old">¥<?= number_format($g['original_price'] ?? 0, 2) ?></span>
                            </p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">⚡</div><p class="h5-empty__text">暂无秒杀商品</p></div>
        <?php endif; ?>
    </div>
</div>