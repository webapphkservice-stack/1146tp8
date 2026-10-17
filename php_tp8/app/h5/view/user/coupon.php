<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>领券中心</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($list)): ?>
            <div style="padding:10px 0;">
                <?php foreach ($list as $c): ?>
                    <?php $isReceived = !empty($c['is_receive']); ?>
                    <div class="h5-coupon" style="padding:6px 12px;">
                        <div class="h5-coupon__item" style="<?= $isReceived ? 'background:#e0e0e0;' : 'background:linear-gradient(135deg,#ff6a00,#ee0979);' ?>">
                            <div class="h5-coupon__left">
                                <div class="h5-coupon__price"><em>¥</em><b><?= number_format($c['reduce_price'] ?? 0, 0) ?></b></div>
                                <div class="h5-coupon__condition"><?= htmlspecialchars($c['name'] ?? '') ?></div>
                            </div>
                            <div class="h5-coupon__right"><?= $isReceived ? '已领取' : '领取' ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">🎟️</div><p class="h5-empty__text">暂无优惠券</p></div>
        <?php endif; ?>
    </div>
</div>