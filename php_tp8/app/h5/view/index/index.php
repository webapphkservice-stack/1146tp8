<div class="h5-shell fm-page">
    <!-- 顶部导航 -->
    <div class="h5-nav-bar">
        <div class="h5-search" style="flex:1; background: transparent; padding: 0;">
            <span class="h5-search__inner" style="justify-content:center; font-weight:600; color:#2d5016;"><?= htmlspecialchars($storeName ?? '') ?></span>
        </div>
        <a href="/h5/cart/index.html" class="h5-nav-cart">🛒</a>
    </div>

    <!-- Hero 区块 -->
    <div class="fm-hero">
        <h1 class="fm-hero__title">新鲜好物</h1>
        <p class="fm-hero__sub">产地直供 · 每日新鲜到家</p>
        <a href="/h5/goods/list.html" class="fm-hero__btn">去逛逛</a>
    </div>

    <!-- 便捷服务 -->
    <div class="fm-service">
        <a class="fm-service__card" href="/pages/flight/index">
            <span class="fm-service__icon">✈️</span>
            <span class="fm-service__name">航班查询</span>
            <span class="fm-service__desc">免费参考航班</span>
        </a>
        <a class="fm-service__card" href="/pages/taxi/index">
            <span class="fm-service__icon">🚕</span>
            <span class="fm-service__name">出租车预约</span>
            <span class="fm-service__desc">机场接送直达酒店</span>
        </a>
    </div>

    <?php if (!empty($categoryList)): ?>
        <!-- 分类药丸 -->
        <div class="fm-pills">
            <?php foreach (array_slice($categoryList, 0, 6) as $c): ?>
                <a class="fm-pill" href="/h5/goods/list.html?category_id=<?= $c['category_id'] ?>"><?= htmlspecialchars($c['name']) ?></a>
            <?php endforeach; ?>
            <a class="fm-pill" href="/h5/goods/list.html">全部</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($goodsList['data'])): ?>
        <!-- 推荐商品 -->
        <div class="fm-home__sec">推荐好物</div>
        <ul class="fm-home-goods">
            <?php foreach ($goodsList['data'] as $g): ?>
                <li class="fm-home-goods__card" onclick="location.href='/h5/goods/detail.html?goods_id=<?= $g['goods_id'] ?>'">
                    <img class="fm-home-goods__img" src="<?= htmlspecialchars($g['goods_image'] ?? '') ?>" alt=""/>
                    <div class="fm-home-goods__info">
                        <p class="fm-home-goods__name"><?= htmlspecialchars($g['goods_name']) ?></p>
                        <p class="fm-home-goods__price">
                            <span class="fm-price-tag">¥<?= number_format($g['goods_min_price'] ?? 0, 2) ?></span>
                            <span class="h5-goods__sales">已售 <?= $g['goods_sales'] ?? 0 ?></span>
                        </p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="/h5/goods/list.html" class="fm-home__more">查看全部商品 →</a>
    <?php else: ?>
        <div class="h5-empty">
            <div class="h5-empty__icon">🧺</div>
            <p class="h5-empty__text">商品即将上架，敬请期待</p>
        </div>
    <?php endif; ?>
</div>
