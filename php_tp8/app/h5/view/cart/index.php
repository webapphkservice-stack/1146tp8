<?php
/**
 * 购物车列表（带数量 +/-、删除、底部结算条）
 */
?>
<div class="h5-shell">
    <div class="h5-nav-bar">
        <h4>购物车<?php if ($totalNum > 0): ?>(<?= (int)$totalNum ?>)<?php endif; ?></h4>
    </div>

    <div class="h5-body h5-cart-body">
        <?php if (!empty($list)): ?>
        <ul class="h5-cart-list" id="cartList">
            <?php foreach ($list as $item): ?>
            <li class="h5-cart-item" data-goods-id="<?= (int)$item['goods_id'] ?>" data-spec-sku-id="<?= (int)$item['spec_sku_id'] ?>">
                <img class="h5-cart-item__img" src="<?= htmlspecialchars($item['goods_image']) ?>" alt="" onerror="this.src='/assets/h5/images/default-avatar.jpg'"/>
                <div class="h5-cart-item__info">
                    <div class="h5-cart-item__name"><?= htmlspecialchars($item['goods_name']) ?></div>
                    <div class="h5-cart-item__price">¥<?= number_format((float)$item['goods_price'], 2) ?></div>
                </div>
                <div class="h5-cart-item__ctrl">
                    <button class="h5-cart-num" data-act="sub">−</button>
                    <input  class="h5-cart-num__input" type="text" value="<?= (int)$item['total_num'] ?>" readonly/>
                    <button class="h5-cart-num" data-act="add">+</button>
                </div>
                <button class="h5-cart-item__del" title="删除">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- 底部结算条 -->
        <div class="h5-cart-footer">
            <div class="h5-cart-footer__left">
                <span class="h5-cart-footer__label">合计</span>
                <span class="h5-cart-footer__price">¥<span id="cartTotalPrice"><?= number_format((float)$totalPrice, 2) ?></span></span>
            </div>
            <div class="h5-cart-footer__subtotal">共 <span id="cartTotalNum"><?= (int)$totalNum ?></span> 件</div>
            <button class="h5-cart-footer__btn" onclick="alert('订单功能建设中')">去结算</button>
        </div>

        <script>
        (function(){
            function toast(msg){
                var t=document.createElement('div');
                t.textContent=msg;Object.assign(t.style,{position:'fixed',top:'45%',left:'50%',transform:'translate(-50%,-50%)',background:'rgba(0,0,0,.8)',color:'#fff',padding:'10px 18px',borderRadius:'8px',fontSize:'13px',zIndex:999});
                document.body.appendChild(t);setTimeout(function(){t.remove();},1500);
            }
            function postAct(item, act){
                var goodsId=item.dataset.goodsId, specSkuId=item.dataset.specSkuId;
                var url = act==='del' ? '/h5/cart/delete.html' : (act==='sub' ? '/h5/cart/sub.html' : '/h5/cart/add.html');
                var body = act==='del'
                    ? 'cart_ids='+goodsId+'_'+specSkuId
                    : (act==='sub'
                        ? 'goods_id='+goodsId+'&goods_sku_id='+specSkuId
                        : 'goods_id='+goodsId+'&goods_sku_id='+specSkuId+'&goods_num=1');
                fetch(url,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body})
                    .then(function(r){return r.json();})
                    .then(function(d){
                        if(d.code!==1){toast(d.msg);return;}
                        var list=d.data.list;
                        var box=document.getElementById('cartList');
                        if(!list||list.length===0){
                            location.reload();return;
                        }
                        // 更新 total
                        document.getElementById('cartTotalPrice').textContent=d.data.total_price;
                        document.getElementById('cartTotalNum').textContent=d.data.total_num;
                        // 更新/删除当前行
                        if(act==='del'){item.remove();}
                        else {
                            var row=list.find(function(x){return x.goods_id==goodsId && String(x.spec_sku_id)===String(specSkuId);});
                            if(!row){item.remove();}
                            else {item.querySelector('.h5-cart-num__input').value=row.total_num;}
                        }
                        if(box.children.length===0){location.reload();}
                    }).catch(function(){toast('网络错误');});
            }
            document.querySelectorAll('.h5-cart-item').forEach(function(item){
                item.querySelectorAll('.h5-cart-num').forEach(function(btn){
                    btn.addEventListener('click',function(){postAct(item, btn.dataset.act);});
                });
                item.querySelector('.h5-cart-item__del').addEventListener('click',function(){
                    if(!confirm('确定删除这件商品？')) return;
                    postAct(item,'del');
                });
            });
        })();
        </script>
        <?php else: ?>
        <div class="h5-empty" style="padding-top: 90px;">
            <div class="h5-empty__icon">
                <svg viewBox="0 0 24 24" width="56" height="56" fill="none" stroke="#c8c8c8" stroke-width="1.4"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9.4" cy="20" r="1.4"/>
                    <circle cx="17.6" cy="20" r="1.4"/>
                    <path d="M2.6 3.4h2.6l2.4 12.2h10.5l2.1-8.6H6.2"/>
                </svg>
            </div>
            <p class="h5-empty__text">购物车还是空的</p>
            <a href="/h5/page/index.html" class="h5-empty__btn">去逛逛</a>
        </div>
        <?php endif; ?>
    </div>
</div>
