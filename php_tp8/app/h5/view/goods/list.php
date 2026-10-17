<div class="h5-shell">
    <!-- 顶部搜索栏 -->
    <div class="h5-nav-bar">
        <a href="/h5/page/index.html" class="h5-back">‹</a>
        <div class="h5-search" style="flex:1; padding: 0; background: transparent;">
            <form class="h5-search__inner" :class="'radius'" method="get" action="/h5/goods/list.html">
                <svg class="h5-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="keyword" class="h5-search__input" placeholder="搜索商品..."
                       value="<?= htmlspecialchars($keyword ?? '') ?>"/>
                <button type="submit" class="h5-search__btn">搜索</button>
            </form>
        </div>
    </div>

    <div class="h5-body">
        <?php if (!empty($keyword)): ?>
            <div class="h5-search-tip">关键词「<?= htmlspecialchars($keyword) ?>」共 <?= $list['total'] ?? 0 ?> 件商品</div>
        <?php endif; ?>

        <?php if (!empty($list['data'])): ?>
            <ul class="h5-goods h5-goods--list">
                <?php foreach ($list['data'] as $g): ?>
                    <li class="h5-goods__item h5-goods__item--list"
                        onclick="location.href='/h5/goods/detail.html?goods_id=<?= $g['goods_id'] ?>'">
                        <img class="h5-goods__img" src="<?= htmlspecialchars($g['goods_image'] ?? '') ?>" alt=""/>
                        <div class="h5-goods__info">
                            <p class="h5-goods__name"><?= htmlspecialchars($g['goods_name']) ?></p>
                            <?php if (!empty($g['selling_point'])): ?>
                                <p class="h5-goods__point"><?= htmlspecialchars($g['selling_point']) ?></p>
                            <?php endif; ?>
                            <p class="h5-goods__price">
                                <span class="h5-goods__now">¥<?= number_format($g['goods_min_price'] ?? 0, 2) ?></span>
                                <span class="h5-goods__sales">已售 <?= $g['goods_sales'] ?? 0 ?></span>
                            </p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="h5-empty">
                <div class="h5-empty__icon">🔍</div>
                <p class="h5-empty__text">暂无相关商品</p>
                <a href="/h5/page/index.html" class="h5-empty__btn">返回首页</a>
            </div>
        <?php endif; ?>
    </div>
</div>
