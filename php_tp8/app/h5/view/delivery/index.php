<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>外送</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($list)): ?>
            <ul class="h5-goods h5-goods--list">
                <?php foreach ($list as $d): ?>
                    <li class="h5-goods__item h5-goods__item--list">
                        <div class="h5-goods__info">
                            <p class="h5-goods__name"><?= htmlspecialchars($d['title'] ?? '') ?></p>
                            <p style="color:#666;font-size:13px;"><?= htmlspecialchars($d['description'] ?? '') ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">🛵</div><p class="h5-empty__text">暂无外送服务</p></div>
        <?php endif; ?>
    </div>
</div>