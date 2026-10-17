<?php
use think\facade\Route;

// 用户 / 上传 全部走 Token 中间件（注入 wxapp_id；登录态由控制器 getUser 解析）
Route::middleware(\app\api\middleware\Token::class)->group(function () {
    // 登录 / 注册
    Route::post('user/login', 'User/login');
    Route::post('user/sendCode', 'User/sendCode');
    Route::post('user/whatsappLogin', 'User/whatsappLogin');
    Route::post('user/googleLogin', 'User/googleLogin');
    Route::post('user/isRegister', 'User/isRegister');
    // 用户资料
    Route::post('user/detail', 'User/detail');
    Route::post('user/bindMobile', 'User/bindMobile');
    Route::post('user/bindWhatsapp', 'User/bindWhatsapp');
    Route::post('user/bindWechat', 'User/bindWechat');
    Route::post('user/whatsappSendCode', 'User/whatsappSendCode');
    // 资料修改（前端大写 User 开头，显式映射）
    Route::post('User/update_avatarurl', 'User/update_avatarurl');
    Route::post('User/update_nickname', 'User/update_nickname');
    // 用户中心（前端点分式 user.index/detail）
    Route::post('user.index/detail', 'user.Index/detail');
    // 文件上传
    Route::post('upload/image', 'Upload/image');
    // 页面装修（首页 diy，免登录；RN HomeScreen 依赖 page/index 渲染整个首页）
    Route::get('page/index', 'Page/index');
    Route::get('page/icon', 'Page/icon');

    // 内容类（文章 / 签到 / 帮助中心，免登录读取；签到提交由控制器强制登录）
    // 帮助中心 RN 调用 wxapp/help，由 Wxapp 控制器的 help 方法经默认 URL 解析命中
    // （命名控制器 Wxapp 以匹配路径首段，详见 app/api/controller/Wxapp.php 注释）。
    Route::get('article/index', 'Article/index');
    Route::get('article/lists', 'Article/lists');
    Route::get('article/detail', 'Article/detail');
    Route::get('signin/index', 'Signin/index');
    Route::post('signin/submit', 'Signin/submit');

    // 商品浏览（免登录，但需 wxapp_id，见 Token 中间件）
    Route::get('category/index', 'Category/index');
    Route::get('goods/lists', 'Goods/lists');
    Route::get('goods/detail', 'Goods/detail');
    Route::get('comment/lists', 'Comment/lists');

    // 购物车（需登录，Token 中间件注入 wxapp_id，getUser(true) 强制登录）
    Route::get('cart/lists', 'Cart/lists');
    Route::post('cart/add', 'Cart/add');
    Route::post('cart/sub', 'Cart/sub');
    Route::post('cart/delete', 'Cart/delete');
    // 收货地址（需登录）
    Route::get('address/lists', 'Address/lists');
    Route::get('address/detail', 'Address/detail');
    Route::post('address/add', 'Address/add');
    Route::post('address/edit', 'Address/edit');
    Route::post('address/delete', 'Address/delete');
    Route::post('address/setDefault', 'Address/setDefault');
    // 下单结算（需登录；GET=结算页，POST=提交建单，payment 由第 6 批补充）
    Route::get('order/cart', 'Order/cart');
    Route::post('order/cart', 'Order/cart');
    Route::get('order/buyNow', 'Order/buyNow');
    Route::post('order/buyNow', 'Order/buyNow');
    // 用户订单（需登录）：列表/详情/物流/取消/收货/支付
    Route::get('user.order/lists', 'user.Order/lists');
    Route::get('user.order/detail', 'user.Order/detail');
    Route::get('user.order/express', 'user.Order/express');
    Route::post('user.order/cancel', 'user.Order/cancel');
    Route::post('user.order/receipt', 'user.Order/receipt');
    Route::get('user.order/pay', 'user.Order/pay');
    // 会员等级（B3）
    Route::get('user.grade/lists', 'user.Grade/lists');
    Route::get('user.grade/my', 'user.Grade/my');
    // 会员 VIP（需登录）
    Route::get('user.vip/index', 'user.Vip/index');
    Route::post('user.vip/buy', 'user.Vip/buy');
    Route::get('user.vip/order', 'user.Vip/order');
    // 积分明细（需登录）。注意 RN PointsScreen 直接对 list 做 .map()，
    // 故返回纯数组而非分页对象；表字段 value 映射为 RN 契约的 points。
    Route::get('points/log/index', 'PointsLog/index');

    // 优惠券（第 8 批）
    Route::get('coupon/lists', 'Coupon/lists');
    Route::get('user.coupon/lists', 'user.Coupon/lists');
    Route::post('user.coupon/receive', 'user.Coupon/receive');
    // 钱包 / 余额流水（第 8 批）
    Route::get('user.wallet/index', 'user.Wallet/index');
    Route::get('balance/log/lists', 'Balance/log');
    // 我的水票（送水：余额 + 流水，需登录）
    Route::get('user.water_ticket/lists', 'user.WaterTicket/lists');
    // 代币充值（微信虚拟支付 currency 模式，需登录）
    Route::get('user.coin/index', 'user.Coin/index');
    Route::post('user.coin/buy', 'user.Coin/buy');
    Route::get('user.coin/order', 'user.Coin/order');
    // 充值（第 8 批）
    Route::get('recharge/index', 'Recharge/index');
    Route::post('recharge/submit', 'Recharge/submit');
    // 分销（第 9 批）—— TP8 将 "user.dealer" 解析为单级控制器 user\Dealer，
    // 3 段后缀（/lists、/submit、/poster）由该控制器按 pathinfo 自行区分，
    // 与既有约定（balance/log/lists → 单级 Balance 控制器）一致
    // RN dealer.ts 使用斜杠 3/4 段 URL（user/dealer/apply/submit 等）。
    // 注意：TP8 本环境下 user 前缀多段静态规则不命中（默认解析落到 User::dealer），
    // 由 User 控制器的 dealer() 方法按 pathinfo 转调 user\Dealer，无需在此注册。
    Route::get('user.dealer/center', 'user/Dealer/center');
    Route::get('user.dealer/apply', 'user/Dealer/apply');
    Route::get('user.dealer/withdraw', 'user/Dealer/withdraw');
    Route::get('user.dealer/team', 'user/Dealer/team');
    Route::get('user.dealer/order', 'user/Dealer/order');
    Route::get('user.dealer/qrcode', 'user/Dealer/qrcode');
    // 售后/退款（第 10 批）
    Route::get('user.refund/lists', 'user/Refund/lists');
    Route::post('user.refund/apply', 'user/Refund/apply');
    Route::get('user.refund/detail', 'user/Refund/detail');
    Route::post('user.refund/delivery', 'user/Refund/delivery');
    // 好友代付（第 10 批）
    Route::get('order.friendpay/detail', 'order/Friendpay/detail');
    Route::post('order.friendpay/pay', 'order/Friendpay/pay');
    // 配送/外送（第 11 批，路由在 Token 分组内注入 wxapp_id）
    Route::get('delivery/lists', 'Delivery/lists');
    Route::get('delivery/detail', 'Delivery/detail');
    Route::get('delivery/checkCountry', 'Delivery/checkCountry');
    Route::get('delivery/shop', 'Delivery/shop');
    Route::get('delivery/goods', 'Delivery/goods');
});

// 支付异步回调（无需登录态，网关直连，置于 Token 中间件分组之外）
// 微信 / 支付宝 / Stripe 三类网关共用订单状态机收口
Route::post('notify/order', 'Notify/order');           // 微信支付异步通知
Route::post('notify/alipay', 'NotifyAlipay/order');    // 支付宝异步通知
Route::post('notify/stripe', 'NotifyStripe/order');    // Stripe Webhook

// 整点秒杀（营销模块，免登录读取；路由置于 Token 分组之外）。
// 注意：thinkphp 6/8 多应用下 Route::middleware()->group() 闭包内的 Route::get
// 不会注册路由规则（项目既有路由一直依赖默认 URL 解析碰巧命中），故秒杀路由放组外。
// Token 中间件经上述写法已成为 api 应用级全局中间件，wxapp_id 仍会被注入，
// 与组内路由共享同一套鉴权能力。
Route::get('sharp/index', 'sharp.Index/index');
Route::get('sharp/goods/lists', 'sharp.Goods/lists');
Route::get('sharp/goods/detail', 'sharp.Goods/detail');
// 秒杀下单（需登录：控制器 getUser(true) 强制登录；路由放组外复用 api 应用级 Token 中间件注入 wxapp_id）
Route::get('sharp/order', 'sharp.Order/checkout');
Route::post('sharp/order', 'sharp.Order/checkout');

// 拼团（公开读端点；与 sharp 同方式注册于组外，复用 api 应用级 Token 中间件注入 wxapp_id）
Route::get('sharing/index', 'sharing.Index/index');
Route::get('sharing/goods/lists', 'sharing.Goods/lists');
Route::get('sharing/goods/detail', 'sharing.Goods/detail');
Route::get('sharing/active/detail', 'sharing.Active/detail');

// 砍价（6 端点，含写：partake / help_cut；与 sharp 同方式注册于组外，复用 api 应用级 Token 中间件注入 wxapp_id）
Route::get('bargain/active/lists', 'bargain.Active/lists');
Route::get('bargain/active/detail', 'bargain.Active/detail');
Route::get('bargain/task/lists', 'bargain.Task/lists');
Route::post('bargain/task/partake', 'bargain.Task/partake');
Route::get('bargain/task/detail', 'bargain.Task/detail');
Route::post('bargain/task/help_cut', 'bargain.Task/help_cut');

// 直播（公开读端点，复用 api 应用级 Token 中间件注入 wxapp_id）
Route::get('live/room/lists', 'live.Room/lists');

// 自提点（公开读端点）
Route::get('shop/lists', 'Shop/lists');
Route::get('shop/detail', 'Shop/detail');

// 订单评价（需登录，复用 api 应用级 Token 中间件）
Route::get('user/comment/order', 'user.Comment/order');
Route::post('user/comment/order', 'user.Comment/order');
Route::get('user/comment/lists', 'user.Comment/lists');
Route::post('user/comment/delete', 'user.Comment/delete');
Route::post('user/comment/append', 'user.Comment/append');

// 平台垂直模块（第 12 批，移植自 php api/controller Flight/Taxi）
// 每日任务（第 13 批，小程序独有）
Route::get('user.task/lists', 'user.Task/lists');
Route::post('user.task/receive', 'user.Task/receive');
// 充值记录
Route::get('recharge.order/lists', 'recharge.Order/lists');
// 小程序专用（formId 已废弃，空实现兼容）
Route::post('wxapp.formId/save', 'wxapp.Formid/save');
Route::get('wxapp.submsg/setting', 'wxapp.Submsg/setting');
// 拼团设置
Route::get('sharing.setting/getAll', 'sharing.Setting/getAll');
// 航班查询（公开读，无需登录）
Route::get('flight/search', 'Flight/search');
Route::get('flight/defaults', 'Flight/defaults');
// 出租车（setting 公开读；submit/orders 需登录）
Route::get('taxi/setting', 'Taxi/setting');
Route::post('taxi/submit', 'Taxi/submit');
Route::get('taxi/orders', 'Taxi/orders');

// 拼团订单整链（第 13 批，小程序独有）
Route::get('sharing.order/checkout', 'sharing.Order/checkout');
Route::post('sharing.order/checkout', 'sharing.Order/checkout');
Route::get('sharing.order/lists', 'sharing.Order/lists');
Route::get('sharing.order/detail', 'sharing.Order/detail');
Route::get('sharing.order/express', 'sharing.Order/express');
Route::get('sharing.order/pay', 'sharing.Order/pay');
Route::post('sharing.order/receipt', 'sharing.Order/receipt');
Route::post('sharing.order/cancel', 'sharing.Order/cancel');
Route::get('sharing.order/extractQrcode', 'sharing.Order/extractQrcode');
// 拼团售后 / 评论
Route::get('sharing.refund/lists', 'sharing.Refund/lists');
Route::get('sharing.refund/apply', 'sharing.Refund/apply');
Route::post('sharing.refund/apply', 'sharing.Refund/apply');
Route::get('sharing.refund/detail', 'sharing.Refund/detail');
Route::post('sharing.refund/delivery', 'sharing.Refund/delivery');
Route::get('sharing.comment/order', 'sharing.Comment/order');
Route::post('sharing.comment/order', 'sharing.Comment/order');
Route::get('sharing.comment/lists', 'sharing.Comment/lists');
// 拼团/秒杀/砍价商品海报
Route::get('sharing.goods/poster', 'sharing.Goods/poster');
Route::get('sharp.goods/poster', 'sharp.Goods/poster');
Route::get('bargain.active/poster', 'bargain.Active/poster');
// 主订单自提核销码
Route::get('user.order/extractQrcode', 'user.Order/extractQrcode');
// 门店端核销
Route::get('shop.order/detail', 'shop.Order/detail');
Route::post('shop.order/extract', 'shop.Order/extract');

// ============ 第 14 批补齐（对齐 1146weixin 小程序完整调用面） ============
// 文章互动（种草社区：点赞/评论列表/发表评论）
Route::post('article/like', 'Article/like');
Route::get('article/commentList', 'Article/commentList');
Route::post('article/comment', 'Article/comment');
// 商品海报
Route::get('goods/poster', 'Goods/poster');
// 外送订单（小程序端；蛇形 + 点号双写法，兼容小程序 delivery_order/*）
Route::get('delivery_order/lists', 'DeliveryOrder/lists');
Route::post('delivery_order/create', 'DeliveryOrder/create');
Route::post('delivery_order/confirm', 'DeliveryOrder/confirm');
Route::get('delivery.order/lists', 'DeliveryOrder/lists');
Route::post('delivery.order/create', 'DeliveryOrder/create');
Route::post('delivery.order/confirm', 'DeliveryOrder/confirm');
// wxapp 兼容（help 原依赖默认解析，显式注册；base/getWxappStore 移植自 TP5）
Route::get('wxapp/base', 'Wxapp/base');
Route::get('wxapp/help', 'Wxapp/help');
Route::get('wxapp/getWxappStore', 'Wxapp/getWxappStore');
// 砍价下单（点号 + 斜杠双写法）
Route::get('bargain.order/checkout', 'bargain.Order/checkout');
Route::post('bargain.order/checkout', 'bargain.Order/checkout');
Route::get('bargain/order/checkout', 'bargain.Order/checkout');
Route::post('bargain/order/checkout', 'bargain.Order/checkout');
// 分销子动作 4 段路径（点号 + 斜杠双写法；目标为 user/dealer 子目录控制器）
Route::post('user.dealer.apply/submit', 'user.dealer.Apply/submit');
Route::get('user.dealer.order/lists', 'user.dealer.Order/lists');
Route::get('user.dealer.qrcode/poster', 'user.dealer.Qrcode/poster');
Route::get('user.dealer.team/lists', 'user.dealer.Team/lists');
Route::post('user.dealer.withdraw/submit', 'user.dealer.Withdraw/submit');
Route::get('user.dealer.withdraw/lists', 'user.dealer.Withdraw/lists');
Route::post('user/dealer/apply/submit', 'user.dealer.Apply/submit');
Route::get('user/dealer/order/lists', 'user.dealer.Order/lists');
Route::get('user/dealer/qrcode/poster', 'user.dealer.Qrcode/poster');
Route::get('user/dealer/team/lists', 'user.dealer.Team/lists');
Route::post('user/dealer/withdraw/submit', 'user.dealer.Withdraw/submit');
Route::get('user/dealer/withdraw/lists', 'user.dealer.Withdraw/lists');
