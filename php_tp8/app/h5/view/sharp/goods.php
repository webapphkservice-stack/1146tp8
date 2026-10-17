<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>秒杀详情</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($detail) && !empty($detail['goods'])): ?>
            <?php $g = $detail['goods']; ?>
            <div class="h5-sharp">
                <ul class="h5-sharp__list column-1">
                    <li class="h5-sharp__item">
                        <img class="h5-sharp__img" src="<?= htmlspecialchars($g['goods_image'] ?? '') ?>"/>
                        <p class="h5-sharp__name" style="white-space:normal;"><?= htmlspecialchars($g['goods_name'] ?? '') ?></p>
                        <p class="h5-sharp__price">
                            <span class="h5-sharp__now" style="font-size:18px;">¥<?= number_format($g['seckill_price'] ?? 0, 2) ?></span>
                            <span class="h5-sharp__old">¥<?= number_format($g['original_price'] ?? 0, 2) ?></span>
                        </p>
                    </li>
                </ul>
            </div>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">⚡</div><p class="h5-empty__text">秒杀商品不存在或已结束</p></div>
        <?php endif; ?>
    </div>
</div>