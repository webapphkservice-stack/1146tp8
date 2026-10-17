<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>种草社区</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($categoryList)): ?>
            <div style="display:flex;overflow-x:auto;padding:10px 12px 0;gap:8px;background:#fff;">
                <a href="/h5/article/index.html" style="white-space:nowrap;padding:6px 14px;border-radius:14px;font-size:12px;text-decoration:none;
                    <?= $activeCid == 0 ? 'background:#333;color:#fff;' : 'background:#f5f5f7;color:#666;' ?>">全部</a>
                <?php foreach ($categoryList as $c): ?>
                    <a href="/h5/article/index.html?category_id=<?= $c['category_id'] ?>" style="white-space:nowrap;padding:6px 14px;border-radius:14px;font-size:12px;text-decoration:none;
                        <?= $activeCid == $c['category_id'] ? 'background:#333;color:#fff;' : 'background:#f5f5f7;color:#666;' ?>">
                        <?= htmlspecialchars($c['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($list)): ?>
            <div class="h5-article">
                <?php foreach ($list as $a): ?>
                    <div class="h5-article__item" onclick="location.href='/h5/article/detail.html?article_id=<?= $a['article_id'] ?>'">
                        <?php if (!empty($a['image'])): ?>
                            <img class="h5-article__img" src="<?= htmlspecialchars($a['image']) ?>"/>
                        <?php endif; ?>
                        <div class="h5-article__info">
                            <h3 class="h5-article__title"><?= htmlspecialchars($a['article_title'] ?? '') ?></h3>
                            <p class="h5-article__views"><?= $a['show_views'] ?? 0 ?> 阅读</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">📝</div><p class="h5-empty__text">暂无文章</p></div>
        <?php endif; ?>
    </div>
</div>