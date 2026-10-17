<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>拼单详情</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($detail)): ?>
            <div class="h5-sharing">
                <div class="h5-sharing__item" style="flex-direction:column;">
                    <div class="h5-sharing__info">
                        <p class="h5-sharing__name">拼单 #<?= $detail['active_id'] ?></p>
                        <p class="h5-sharing__desc">需<?= $detail['people_num'] ?? 2 ?>人成团 · 已参与<?= count($detail['users'] ?? []) ?>人</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">😔</div><p class="h5-empty__text">拼单不存在</p></div>
        <?php endif; ?>
    </div>
</div>