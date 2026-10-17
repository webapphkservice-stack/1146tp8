<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>文章详情</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($detail)): ?>
            <div class="h5-article">
                <div style="padding:12px 0;">
                    <h2 style="font-size:18px;font-weight:bold;margin:0 0 10px;color:#333;"><?= htmlspecialchars($detail['article_title'] ?? '') ?></h2>
                    <p class="h5-article__views"><?= $detail['actual_views'] ?? 0 ?> 阅读</p>
                </div>
                <div class="h5-richtext"><?= $detail['content'] ?? $detail['article_content'] ?? '' ?></div>
            </div>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">😔</div><p class="h5-empty__text">文章不存在或已下架</p></div>
        <?php endif; ?>
    </div>
</div>