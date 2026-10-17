<?php
$nickName    = htmlspecialchars($userInfo['nickName'] ?? '点击登录');
$avatarUrl   = !empty($userInfo['avatarUrl']) ? $userInfo['avatarUrl'] : $defaultAvatar;
$avatarUrl   = (strpos($avatarUrl, 'http') === 0 || strpos($avatarUrl, '/') === 0) ? $avatarUrl : $defaultAvatar;
$vipText     = $isVip ? ('有效期至 ' . $vipExpireDate) : '专享商品 · 双倍积分 · 运费券 · 退货包运费';
$vipBtn      = $isVip ? '续费' : '立即开通';
?>
<div class="h5u">
    <!-- 用户信息区（黄顶 + 悬浮订单卡） -->
    <section class="h5u-user">
        <div class="h5u-user-header" style="background-image:url('/assets/h5/images/user-bg.png')">
            <div class="h5u-user-cont">
                <div class="h5u-user-right">
                    <div class="h5u-user-name"><?= $nickName ?></div>
                    <?php if (!empty($userInfo['grade_id'])): ?>
                        <div class="h5u-user-grade">
                            <img src="/assets/h5/images/grade_icon.png" alt="会员">
                            <span>VIP会员</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 头像（独立 absolute，z-index 高于 header 和 order，只有头像圆压在订单卡顶） -->
        <div class="h5u-user-avatar" id="userAvatar">
            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="头像"
                 onerror="this.src='<?= $defaultAvatar ?>'">
        </div>

        <!-- 订单导航（悬浮白卡） -->
        <div class="h5u-order">
            <a href="<?= url('cart/index') ?>" class="h5u-order-head">
                <span class="h5u-order-title">我的订单</span>
                <span class="h5u-order-all">全部订单 ›</span>
            </a>
            <div class="h5u-order-row">
                <a class="h5u-order-item" href="<?= url('cart/index') ?>">
                    <span class="h5u-order-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z"/><path d="M4 9h16M9 4v16"/></svg>
                    </span>
                    <span class="h5u-order-name">全部订单</span>
                </a>
                <a class="h5u-order-item" href="<?= url('cart/index') ?>">
                    <span class="h5u-order-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18v12H3z"/><path d="M3 10h18"/></svg>
                    </span>
                    <span class="h5u-order-name">待付款</span>
                    <?php if ($orderCount['payment'] > 0): ?>
                        <span class="h5u-badge"><?= (int)$orderCount['payment'] ?></span>
                    <?php endif; ?>
                </a>
                <a class="h5u-order-item" href="<?= url('cart/index') ?>">
                    <span class="h5u-order-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h13v10H3z"/><path d="M16 10h5l-2 5h-3"/></svg>
                    </span>
                    <span class="h5u-order-name">待收货</span>
                    <?php if ($orderCount['received'] > 0): ?>
                        <span class="h5u-badge"><?= (int)$orderCount['received'] ?></span>
                    <?php endif; ?>
                </a>
                <a class="h5u-order-item" href="<?= url('cart/index') ?>">
                    <span class="h5u-order-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"/><circle cx="12" cy="17" r="1"/><path d="M5 7h14l-1 11H6z"/></svg>
                    </span>
                    <span class="h5u-order-name">退款/售后</span>
                    <?php if ($orderCount['refund'] > 0): ?>
                        <span class="h5u-badge"><?= (int)$orderCount['refund'] ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </section>

    <!-- 我的钱包 -->
    <section class="h5u-asset">
        <div class="h5u-asset-left">
            <div class="h5u-asset-item">
                <div class="h5u-asset-value"><?= $balanceText ?></div>
                <div class="h5u-asset-name">账户余额</div>
            </div>
            <div class="h5u-asset-item">
                <div class="h5u-asset-value"><?= (int)$points ?></div>
                <div class="h5u-asset-name">可用积分</div>
            </div>
            <div class="h5u-asset-item">
                <div class="h5u-asset-value"><?= $coinText ?></div>
                <div class="h5u-asset-name">我的代币</div>
            </div>
            <div class="h5u-asset-item">
                <div class="h5u-asset-value">0</div>
                <div class="h5u-asset-name">我的水票</div>
            </div>
        </div>
        <div class="h5u-asset-right" onclick="alert('钱包功能建设中')">
            <div class="h5u-asset-right-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="14" rx="2"/><path d="M16 12h3"/></svg>
            </div>
            <div class="h5u-asset-right-name">我的钱包</div>
        </div>
    </section>

    <!-- VIP 会员卡片 -->
    <section class="h5u-vip">
        <div class="h5u-vip-left">
            <div class="h5u-vip-title">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f5d28b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7l3 10 4-7 3 5 2-4 4 6 4-10"/><circle cx="20" cy="5" r="1.5" fill="#f5d28b"/></svg>
                <span>VIP会员</span>
            </div>
            <div class="h5u-vip-sub"><?= $vipText ?></div>
        </div>
        <a class="h5u-vip-btn" href="javascript:alert('VIP功能建设中')"><?= $vipBtn ?></a>
    </section>

    <!-- 功能菜单（4 列宫格） -->
    <section class="h5u-menus">
        <a class="h5u-menu-item" href="<?= url('user/coupon') ?>">
            <div class="h5u-menu-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V7z"/><path d="M13 5v14"/></svg>
            </div>
            <div class="h5u-menu-name">我的优惠券</div>
        </a>
        <a class="h5u-menu-item" href="<?= url('dealer/index') ?>">
            <div class="h5u-menu-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3"/><path d="M5 21c0-3.3 3.1-6 7-6s7 2.7 7 6"/></svg>
            </div>
            <div class="h5u-menu-name">分销中心</div>
        </a>
        <a class="h5u-menu-item" href="javascript:alert('收货地址功能建设中')">
            <div class="h5u-menu-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s7-7 7-12a7 7 0 0 0-14 0c0 5 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>
            </div>
            <div class="h5u-menu-name">收货地址</div>
        </a>
        <a class="h5u-menu-item" href="javascript:alert('联系客服功能建设中')">
            <div class="h5u-menu-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a4 4 0 0 1-4 4H9l-4 3V7a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4z"/></svg>
            </div>
            <div class="h5u-menu-name">联系客服</div>
        </a>
        <a class="h5u-menu-item" href="<?= url('sharing/index') ?>">
            <div class="h5u-menu-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2.5"/><circle cx="5" cy="19" r="2.5"/><circle cx="19" cy="19" r="2.5"/><path d="M12 7.5v5M10 12.5l-4 4M14 12.5l4 4"/></svg>
            </div>
            <div class="h5u-menu-name">我的拼团</div>
        </a>
        <a class="h5u-menu-item" href="<?= url('bargain/index') ?>">
            <div class="h5u-menu-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20l4-8 4 4 4-6 4 10"/><circle cx="4" cy="20" r="1.2"/><circle cx="20" cy="20" r="1.2"/></svg>
            </div>
            <div class="h5u-menu-name">我的砍价</div>
        </a>
        <a class="h5u-menu-item" href="<?= url('coupon/index') ?>">
            <div class="h5u-menu-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l18-3v12l-18 3V9z"/><path d="M8 6v12"/></svg>
            </div>
            <div class="h5u-menu-name">领券中心</div>
        </a>
        <a class="h5u-menu-item" href="javascript:void(0)" onclick='if(confirm("确定退出登录？")){localStorage.removeItem("h5_token");document.cookie="h5_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT";location.href="/h5/passport/login.html";}'>
            <div class="h5u-menu-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </div>
            <div class="h5u-menu-name">退出</div>
        </a>
    </section>

    <div class="h5u-icp"><?= htmlspecialchars($storeName) ?></div>
</div>
