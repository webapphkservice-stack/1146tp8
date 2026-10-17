<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>拼团</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($goodsList)): ?>
            <div class="h5-sharing">
                <?php foreach ($goodsList as $g): ?>
                    <div class="h5-sharing__item" onclick="location.href='/h5/sharing/goods.html?goods_id=<?= $g['goods_id'] ?>'">
                        <img class="h5-sharing__img" src="<?= htmlspecialchars($g['goods_image'] ?? '') ?>"/>
                        <div class="h5-sharing__info">
                            <p class="h5-sharing__name"><?= htmlspecialchars($g['goods_name'] ?? '') ?></p>
                            <p class="h5-sharing__desc"><?= $g['people_num'] ?? 2 ?>人团 · 还差<?= max(0, ($g['people_num'] ?? 2) - 1) ?>人即成团</p>
                            <div class="h5-sharing__price">
                                <span class="h5-sharing__now">¥<?= number_format($g['goods_price'] ?? 0, 2) ?></span>
                                <span class="h5-sharing__old">¥<?= number_format($g['line_price'] ?? 0, 2) ?></span>
                            </div>
                        </div>
                        <button class="h5-sharing__btn">去拼团</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">👥</div><p class="h5-empty__text">暂无拼团商品</p></div>
        <?php endif; ?>
    </div>
</div>