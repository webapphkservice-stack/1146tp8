<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>门店详情</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($detail)): ?>
            <div class="h5-shop">
                <div class="h5-shop__item" style="flex-direction:column;align-items:center;text-align:center;border-bottom:none;">
                    <img class="h5-shop__logo" style="width:100px;height:100px;border-radius:50%;"
                         src="<?= htmlspecialchars($detail['shop_logo'] ?? $detail['logo'] ?? '') ?>"/>
                    <div class="h5-shop__info" style="margin-top:10px;">
                        <p class="h5-shop__name"><?= htmlspecialchars($detail['shop_name'] ?? '') ?></p>
                        <p class="h5-shop__addr"><?= htmlspecialchars($detail['address'] ?? '') ?></p>
                        <?php if (!empty($detail['phone'])): ?><p class="h5-shop__phone">📞 <?= htmlspecialchars($detail['phone']) ?></p><?php endif; ?>
                        <?php if (!empty($detail['business_hours'])): ?><p class="h5-shop__phone">🕐 <?= htmlspecialchars($detail['business_hours']) ?></p><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">🏪</div><p class="h5-empty__text">门店不存在</p></div>
        <?php endif; ?>
    </div>
</div>