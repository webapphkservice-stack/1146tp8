<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>拼团详情</h4>
    </div>
    <div class="h5-body">
        <?php if (!empty($goods)): ?>
            <div class="h5-sharing">
                <div class="h5-sharing__item" style="flex-direction:column;">
                    <img class="h5-sharing__img" style="width:100%;height:auto;aspect-ratio:1;object-fit:cover;border-radius:6px;"
                         src="<?= htmlspecialchars($goods['goods_image'] ?? '') ?>"/>
                    <div class="h5-sharing__info" style="margin-top:10px;">
                        <p class="h5-sharing__name" style="white-space:normal;"><?= htmlspecialchars($goods['goods_name'] ?? '') ?></p>
                        <p class="h5-sharing__desc"><?= $goods['people_num'] ?? 2 ?>人团</p>
                        <div class="h5-sharing__price">
                            <span class="h5-sharing__now">拼团价 ¥<?= number_format($goods['sku'][0]['sharing_price'] ?? 0, 2) ?></span>
                            <span class="h5-sharing__old">¥<?= number_format($goods['goods_price'] ?? 0, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!empty($activeList)): ?>
                <div style="padding:12px 16px 4px;font-size:14px;font-weight:600;color:#333;">进行中的拼单</div>
                <div class="h5-sharing">
                    <?php foreach ($activeList as $a): ?>
                        <div class="h5-sharing__item" onclick="location.href='/h5/sharing/active.html?active_id=<?= $a['active_id'] ?>'">
                            <div class="h5-sharing__info">
                                <p class="h5-sharing__name">凑团 还差<?= max(0, ($a['people_num'] ?? 2) - count($a['users'] ?? [])) ?>人</p>
                                <p class="h5-sharing__desc">剩余<?= max(0, ($a['expire_time'] ?? 0) - time()) ?>秒结束</p>
                            </div>
                            <button class="h5-sharing__btn">加入</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="h5-empty"><div class="h5-empty__icon">😔</div><p class="h5-empty__text">拼团商品不存在或已下架</p></div>
        <?php endif; ?>
    </div>
</div>