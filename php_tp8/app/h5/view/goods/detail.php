<?php
/**
 * 商品详情 - 含 SKU 选择 + 加入购物车
 */
$hasLogin = !empty($h5User);
$skuList = isset($skuList) ? $skuList : [];
$firstSku = !empty($skuList) ? $skuList[0] : null;
$defaultSkuId   = $firstSku ? (int)$firstSku['spec_sku_id'] : 0;
$defaultGoodsSkuId = $firstSku ? (int)$firstSku['goods_sku_id'] : 0;
$defaultPrice = $firstSku ? (float)$firstSku['goods_price'] : 0;
$defaultStock = $firstSku ? (int)$firstSku['stock_num'] : 0;
?>
<?php if (!empty($goods)): ?>
<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;">‹</a>
        <h4>商品详情</h4>
        <a href="/h5/cart/index.html" class="h5-detail-cart-badge" title="购物车">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9.4" cy="20" r="1.4"/><circle cx="17.6" cy="20" r="1.4"/>
                <path d="M2.6 3.4h2.6l2.4 12.2h10.5l2.1-8.6H6.2"/>
            </svg>
            <?php if ($cartTotalNum > 0): ?><span class="h5-detail-cart-badge__num"><?= (int)$cartTotalNum ?></span><?php endif; ?>
        </a>
    </div>

    <div class="h5-body h5-goods-detail">
        <!-- 轮播图 -->
        <div class="h5-detail-swiper">
            <?php
            $imgs = !empty($goods['image']) ? $goods['image'] : [['file_path' => $goods['goods_image']]];
            foreach ($imgs as $idx => $img):
            ?>
                <img src="<?= htmlspecialchars($img['file_path']) ?>" alt=""/>
            <?php endforeach; ?>
        </div>

        <!-- 价格块 -->
        <div class="h5-detail-price">
            <span class="h5-detail-price__now">¥<span id="skuPrice"><?= number_format($defaultPrice, 2) ?></span></span>
            <?php if (!empty($firstSku['line_price']) && $firstSku['line_price'] > 0): ?>
                <span class="h5-detail-price__line">¥<?= number_format($firstSku['line_price'], 2) ?></span>
            <?php endif; ?>
            <span class="h5-detail-price__sales">已售 <?= $goods['goods_sales'] ?? 0 ?></span>
        </div>

        <!-- 名称 + 卖点 -->
        <div class="h5-detail-title">
            <h1><?= htmlspecialchars($goods['goods_name']) ?></h1>
            <?php if (!empty($goods['selling_point'])): ?>
                <p><?= htmlspecialchars($goods['selling_point']) ?></p>
            <?php endif; ?>
        </div>

        <!-- 规格 + 库存 -->
        <div class="h5-detail-meta">
            <div>规格：<span id="skuSpecName"><?= htmlspecialchars($firstSku['spec_name'] ?? '默认') ?></span></div>
            <div>库存：<span id="skuStock"><?= $defaultStock ?></span></div>
        </div>

        <!-- SKU 选择 -->
        <?php if (count($skuList) > 1): ?>
        <div class="h5-detail-skus">
            <div class="h5-detail-skus__title">选择规格</div>
            <div class="h5-detail-skus__list" id="skuList">
                <?php foreach ($skuList as $sku): ?>
                <button class="h5-detail-skus__item<?= (int)$sku['spec_sku_id'] === $defaultSkuId ? ' is-active' : '' ?>"
                        data-spec-sku-id="<?= (int)$sku['spec_sku_id'] ?>"
                        data-goods-sku-id="<?= (int)$sku['goods_sku_id'] ?>"
                        data-price="<?= (float)$sku['goods_price'] ?>"
                        data-stock="<?= (int)$sku['stock_num'] ?>"
                        data-spec-name="<?= htmlspecialchars($sku['spec_name'] ?? '默认') ?>">
                    <?= htmlspecialchars($sku['spec_name'] ?? '默认') ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 数量 -->
        <div class="h5-detail-num">
            <span class="h5-detail-num__label">数量</span>
            <div class="h5-detail-num__ctrl">
                <button id="numMinus" class="h5-detail-num__btn">−</button>
                <input id="numInput" type="text" value="1" readonly/>
                <button id="numPlus" class="h5-detail-num__btn">+</button>
            </div>
        </div>

        <!-- 详情 -->
        <div class="h5-detail-desc">
            <h3>商品详情</h3>
            <div class="h5-detail-desc__content">
                <?= $goods['goods_desc'] ?? '' ?>
            </div>
        </div>

        <!-- 底部操作 -->
        <div class="h5-detail-bottom">
            <a href="/h5/page/index.html" class="h5-detail-bottom__btn h5-detail-bottom__btn--ghost">继续逛逛</a>
            <button id="btnAddCart" class="h5-detail-bottom__btn h5-detail-bottom__btn--primary">加入购物车</button>
        </div>
    </div>
</div>

<script>
(function(){
    var goodsId = <?= (int)$goods['goods_id'] ?>;
    var goodsSkuId = <?= (int)$defaultGoodsSkuId ?>;
    var specSkuId  = <?= (int)$defaultSkuId ?>;
    var stock = <?= $defaultStock ?>;
    var num = 1;
    var isLogin = <?= $hasLogin ? 'true' : 'false' ?>;

    function toast(msg){
        var t = document.createElement('div');
        t.textContent = msg;
        Object.assign(t.style,{position:'fixed',top:'45%',left:'50%',transform:'translate(-50%,-50%)',background:'rgba(0,0,0,.8)',color:'#fff',padding:'10px 18px',borderRadius:'8px',fontSize:'13px',zIndex:999});
        document.body.appendChild(t);
        setTimeout(function(){t.remove();},1600);
    }

    // SKU 切换
    document.querySelectorAll('.h5-detail-skus__item').forEach(function(btn){
        btn.addEventListener('click',function(){
            document.querySelectorAll('.h5-detail-skus__item').forEach(function(b){b.classList.remove('is-active');});
            btn.classList.add('is-active');
            goodsSkuId = parseInt(btn.dataset.goodsSkuId);
            specSkuId  = parseInt(btn.dataset.specSkuId);
            stock      = parseInt(btn.dataset.stock);
            document.getElementById('skuPrice').textContent  = parseFloat(btn.dataset.price).toFixed(2);
            document.getElementById('skuSpecName').textContent= btn.dataset.specName;
            document.getElementById('skuStock').textContent    = stock;
            if (num > stock) { num = Math.max(1, stock); document.getElementById('numInput').value = num; }
        });
    });

    // 数量 +/-
    document.getElementById('numMinus').addEventListener('click',function(){
        if (num > 1) { num--; document.getElementById('numInput').value = num; }
    });
    document.getElementById('numPlus').addEventListener('click',function(){
        if (num < stock) { num++; document.getElementById('numInput').value = num; }
    });

    // 加入购物车
    document.getElementById('btnAddCart').addEventListener('click',function(){
        if (!isLogin) {
            toast('请先登录');
            setTimeout(function(){location.href='/h5/passport/login.html?redirect='+encodeURIComponent(location.pathname+location.search);},800);
            return;
        }
        fetch('/h5/cart/add.html',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'goods_id='+goodsId+'&goods_sku_id='+specSkuId+'&goods_num='+num
        }).then(function(r){return r.json();}).then(function(d){
            toast(d.msg);
            if (d.code === 1) {
                // 更新 Dock 购物车角标：改 data-total-num（layout 已注入）
                var badge = document.querySelector('.h5-detail-cart-badge__num');
                var total = d.data && d.data.total_num ? d.data.total_num : 0;
                if (total > 0) {
                    if (badge) { badge.textContent = total; }
                    else {
                        var el = document.createElement('span');
                        el.className = 'h5-detail-cart-badge__num';
                        el.textContent = total;
                        document.querySelector('.h5-detail-cart-badge').appendChild(el);
                    }
                } else if (badge) { badge.remove(); }
            }
        }).catch(function(){ toast('网络错误'); });
    });
})();
</script>
<?php else: ?>
<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back" style="font-size:28px;color:#333;text-decoration:none;padding:0 8px;margin-right:4px;">‹</a>
        <h4>商品详情</h4>
    </div>
    <div class="h5-body">
        <div class="h5-empty" style="padding-top: 90px;">
            <div class="h5-empty__icon">😢</div>
            <p class="h5-empty__text">商品不存在或已下架</p>
            <a href="/h5/page/index.html" class="h5-empty__btn">返回首页</a>
        </div>
    </div>
</div>
<?php endif; ?>
