<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>砍价详情</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($active)): ?>
            <div class="h5-bargain">
                <div class="h5-bargain__item" style="flex-direction:column;text-align:center;">
                    <div class="h5-bargain__info">
                        <p class="h5-bargain__name" style="font-size:16px;"><?= htmlspecialchars($active['title'] ?? '') ?></p>
                        <div class="h5-bargain__price" style="justify-content:center;margin-top:8px;">
                            <span class="h5-bargain__floor" style="font-size:22px;">底价 ¥<?= number_format($active['floor_price'] ?? 0, 2) ?></span>
                        </div>
                        <button class="h5-bargain__btn" style="margin-top:16px;padding:8px 24px;font-size:14px;">发起砍价</button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">😔</div><p class="h5-empty__text">砍价活动已结束</p></div>
        <?php endif; ?>
    </div>
</div>