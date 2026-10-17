<div class="h5-shell">
    <div class="h5-nav-bar">
        <h4>商品分类</h4>
    </div>

    <div class="h5-body">
        <?php if (empty($list)): ?>
            <div class="h5-empty">
                <div class="h5-empty__icon">📂</div>
                <p class="h5-empty__text">暂无分类</p>
                <a href="/h5/page/index.html" class="h5-empty__btn">返回首页</a>
            </div>
        <?php else: ?>
            <?php foreach ($list as $cat): ?>
                <div class="h5-cat-block">
                    <div class="h5-cat-block__title"><?= htmlspecialchars($cat['name']) ?></div>
                    <ul class="h5-cat-grid">
                        <?php
                        $children = !empty($cat['child']) ? $cat['child'] : [$cat];
                        foreach ($children as $c):
                        ?>
                            <li class="h5-cat-grid__item"
                                onclick="location.href='/h5/goods/list.html?category_id=<?= (int)$c['category_id'] ?>'">
                                <img class="h5-cat-grid__img" src="<?= htmlspecialchars($c['image'] ?? '') ?>" alt=""/>
                                <span class="h5-cat-grid__name"><?= htmlspecialchars($c['name']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>