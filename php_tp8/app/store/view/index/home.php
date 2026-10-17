{__NOLAYOUT__}
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title>百信小店</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <link rel="icon" type="image/png" href="assets/common/i/favicon.ico"/>
    <link rel="stylesheet" href="assets/common/css/iconfont-font_783249.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            color: #333;
        }
        
        /* 顶部导航栏 */
        .header {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
        }
        
        .logo-icon {
            font-size: 32px;
            color: #ff6b6b;
            margin-right: 12px;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
        
        .nav-menu {
            display: flex;
            gap: 30px;
        }
        
        .nav-link {
            text-decoration: none;
            color: #666;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            color: #ff6b6b;
        }
        
        .nav-link.active {
            color: #ff6b6b;
        }
        
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -26px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #ff6b6b;
        }
        
        .user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .btn-primary {
            background: #ff6b6b;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            color: #666;
            border-color: #ddd;
        }
        
        .btn-outline:hover {
            border-color: #ff6b6b;
            color: #ff6b6b;
        }
        
        /* 英雄区域 */
        .hero {
            background: linear-gradient(135deg, #c6a348 0%, #8a6d2e 100%);
            color: #fff;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
            animation: float 20s linear infinite;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(100px, 100px); }
        }
        
        .hero-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-large {
            padding: 12px 32px;
            font-size: 16px;
            border-radius: 30px;
        }
        
        /* 功能特点区域 */
        .features {
            padding: 100px 0;
            background: #fff;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 80px;
        }
        
        .section-title h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }
        
        .section-title p {
            font-size: 18px;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }
        
        .feature-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border-color: #c6a348;
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #c6a348, #8a6d2e);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 32px;
            color: #fff;
        }
        
        .feature-card h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        
        .feature-card p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }
        
        /* 热门商品区域 */
        .products {
            padding: 100px 0;
            background: #f8f9fa;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .product-image {
            height: 200px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #ddd;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: #ff6b6b;
            margin-bottom: 15px;
        }
        
        .product-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #c6a348;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .product-btn:hover {
            background: #8a6d2e;
            transform: translateY(-2px);
        }
        
        /* 服务保障区域 */
        .services {
            padding: 100px 0;
            background: #fff;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }
        
        .service-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }
        
        .service-icon {
            font-size: 32px;
            color: #c6a348;
            margin-top: 5px;
        }
        
        .service-content h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .service-content p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }
        
        /* 客户评价区域 */
        .testimonials {
            padding: 100px 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }
        
        .testimonial-card {
            background: #fff;
            border-radius: 12px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 10px;
            left: 20px;
            font-size: 60px;
            color: #f0f0f0;
            font-family: Georgia, serif;
        }
        
        .testimonial-text {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
            font-style: italic;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #666;
        }
        
        .author-info h4 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .author-info p {
            font-size: 12px;
            color: #999;
        }
        
        /* 底部区域 */
        .footer {
            background: #333;
            color: #fff;
            padding: 80px 0 40px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-section h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #fff;
        }
        
        .footer-section p,
        .footer-section a {
            font-size: 14px;
            color: #ccc;
            line-height: 1.6;
            text-decoration: none;
            margin-bottom: 10px;
            display: block;
        }
        
        .footer-section a:hover {
            color: #c6a348;
        }
        
        .footer-bottom {
            border-top: 1px solid #444;
            padding-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #999;
        }
        
        /* 响应式设计 */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .hero-title {
                font-size: 32px;
            }
            
            .hero-subtitle {
                font-size: 16px;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-large {
                width: 200px;
            }
            
            .section-title h2 {
                font-size: 28px;
            }
            
            .section-title p {
                font-size: 16px;
            }
            
            .features,
            .products,
            .services,
            .testimonials {
                padding: 60px 0;
            }
        }
    </style>
</head>
<body>
    <!-- 顶部导航栏 -->
    <header class="header">
        <div class="nav-container">
            <a href="#" class="logo">
                <i class="iconfont icon-shop logo-icon"></i>
                <span class="logo-text">百信小店</span>
            </a>
            <nav class="nav-menu">
                <a href="#" class="nav-link active">首页</a>
                <a href="#" class="nav-link">商品分类</a>
                <a href="#" class="nav-link">热门商品</a>
                <a href="#" class="nav-link">关于我们</a>
                <a href="#" class="nav-link">联系我们</a>
            </nav>
            <div class="user-actions">
                <a href="<?= url('passport/login') ?>" class="btn btn-outline">登录</a>
                <a href="#" class="btn btn-primary" id="register-btn">注册</a>
            </div>
        </div>
    </header>

    <!-- 英雄区域 -->
    <section class="hero">
        <div class="hero-container">
            <h1 class="hero-title">欢迎来到 百信小店</h1>
            <p class="hero-subtitle">一站式购物体验，品质保证，贴心服务</p>
            <div class="hero-buttons">
                <a href="#products" class="btn btn-large btn-primary">浏览商品</a>
                <a href="#about" class="btn btn-large btn-outline">了解更多</a>
            </div>
        </div>
    </section>

    <!-- 功能特点区域 -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>我们的优势</h2>
                <p>提供高品质商品和优质服务，满足您的所有购物需求</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="iconfont icon-goods"></i>
                    </div>
                    <h3>丰富的商品种类</h3>
                    <p>涵盖多个品类，从日常生活用品到高端奢侈品，满足您的各种购物需求</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="iconfont icon-price"></i>
                    </div>
                    <h3> competitive prices</h3>
                    <p>我们承诺提供最具竞争力的价格，让您以更优惠的价格购买到优质商品</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="iconfont icon-truck"></i>
                    </div>
                    <h3>快速配送服务</h3>
                    <p>高效的物流体系，确保您的商品快速送达，让您享受便捷的购物体验</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="iconfont icon-haoping2"></i>
                    </div>
                    <h3>优质的客户服务</h3>
                    <p>专业的客服团队，为您提供全方位的购物咨询和售后服务</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="iconfont icon-security"></i>
                    </div>
                    <h3>安全的支付方式</h3>
                    <p>多种安全支付方式，保障您的交易安全，让您购物更放心</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="iconfont icon-refund"></i>
                    </div>
                    <h3>无忧退换货</h3>
                    <p>完善的退换货政策，让您购物无后顾之忧</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 热门商品区域 -->
    <section class="products" id="products">
        <div class="container">
            <div class="section-title">
                <h2>热门商品</h2>
                <p>精选优质商品，满足您的购物需求</p>
            </div>
            <div class="products-grid">
                <!-- 商品卡片 1 -->
                <div class="product-card">
                    <div class="product-image">
                        <i class="iconfont icon-goods"></i>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">时尚连衣裙</h3>
                        <p class="product-description">高品质面料，舒适透气，时尚百搭</p>
                        <div class="product-price">¥299.00</div>
                        <a href="#" class="product-btn">立即购买</a>
                    </div>
                </div>
                <!-- 商品卡片 2 -->
                <div class="product-card">
                    <div class="product-image">
                        <i class="iconfont icon-goods"></i>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">智能手机</h3>
                        <p class="product-description">最新款智能手机，强大性能，拍照清晰</p>
                        <div class="product-price">¥3999.00</div>
                        <a href="#" class="product-btn">立即购买</a>
                    </div>
                </div>
                <!-- 商品卡片 3 -->
                <div class="product-card">
                    <div class="product-image">
                        <i class="iconfont icon-goods"></i>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">运动鞋</h3>
                        <p class="product-description">轻便舒适，防滑耐磨，适合各种运动</p>
                        <div class="product-price">¥499.00</div>
                        <a href="#" class="product-btn">立即购买</a>
                    </div>
                </div>
                <!-- 商品卡片 4 -->
                <div class="product-card">
                    <div class="product-image">
                        <i class="iconfont icon-goods"></i>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">护肤套装</h3>
                        <p class="product-description">天然成分，深层滋养，让肌肤焕发活力</p>
                        <div class="product-price">¥599.00</div>
                        <a href="#" class="product-btn">立即购买</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 服务保障区域 -->
    <section class="services">
        <div class="container">
            <div class="section-title">
                <h2>服务保障</h2>
                <p>我们致力于为您提供最优质的购物体验</p>
            </div>
            <div class="services-grid">
                <div class="service-item">
                    <i class="iconfont icon-truck service-icon"></i>
                    <div class="service-content">
                        <h3>快速配送</h3>
                        <p>全国大部分地区支持次日达，让您尽快收到商品</p>
                    </div>
                </div>
                <div class="service-item">
                    <i class="iconfont icon-refund service-icon"></i>
                    <div class="service-content">
                        <h3>7天无理由退换</h3>
                        <p>商品质量问题，7天内无理由退换货</p>
                    </div>
                </div>
                <div class="service-item">
                    <i class="iconfont icon-security service-icon"></i>
                    <div class="service-content">
                        <h3>正品保障</h3>
                        <p>所有商品均为正品，假一赔十</p>
                    </div>
                </div>
                <div class="service-item">
                    <i class="iconfont icon-phone service-icon"></i>
                    <div class="service-content">
                        <h3>24小时客服</h3>
                        <p>专业客服团队，随时为您解答疑问</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 客户评价区域 -->
    <section class="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>客户评价</h2>
                <p>听听我们的客户怎么说</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">商城的商品质量非常好，价格也很实惠，客服态度也很棒，下次还会再来购买！</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">李</div>
                        <div class="author-info">
                            <h4>李女士</h4>
                            <p>忠实客户</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">配送速度很快，商品包装也很精美，完全符合预期，非常满意的一次购物体验。</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">王</div>
                        <div class="author-info">
                            <h4>王先生</h4>
                            <p>新客户</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">售后服务非常到位，遇到问题及时解决，让我非常放心，强烈推荐给大家！</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">张</div>
                        <div class="author-info">
                            <h4>张女士</h4>
                            <p>常客</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 底部区域 -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>关于我们</h3>
                    <p>我们是一家专业的在线商城，致力于为客户提供高品质的商品和优质的服务。</p>
                    <p>成立于2020年，已为超过10万客户提供了满意的购物体验。</p>
                </div>
                <div class="footer-section">
                    <h3>快速链接</h3>
                    <a href="#">首页</a>
                    <a href="#">商品分类</a>
                    <a href="#">热门商品</a>
                    <a href="#">关于我们</a>
                    <a href="#">联系我们</a>
                </div>
                <div class="footer-section">
                    <h3>联系方式</h3>
                    <p><i class="iconfont icon-phone"></i> 客服电话：400-888-8888</p>
                    <p><i class="iconfont icon-email"></i> 邮箱：support@example.com</p>
                    <p><i class="iconfont icon-address"></i> 地址：北京市朝阳区某某大厦</p>
                </div>
                <div class="footer-section">
                    <h3>关注我们</h3>
                    <p>扫描二维码关注我们的公众号，获取最新优惠信息</p>
                    <div style="width: 120px; height: 120px; background: #f0f0f0; border-radius: 8px; margin-top: 10px;"></div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> 百信小店 版权所有 | 京ICP备12345678号</p>
            </div>
        </div>
    </footer>

    <!-- 注册模态框 -->
    <div id="register-modal" class="modal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0,0,0); background-color: rgba(0,0,0,0.4);">
        <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
            <div class="modal-header" style="padding: 10px 0; border-bottom: 1px solid #e0e0e0; margin-bottom: 20px;">
                <h2 style="margin: 0; font-size: 24px; color: #333;">注册新商城</h2>
                <button type="button" class="close" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</button>
            </div>
            <form id="register-form" style="padding: 0 20px;">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="store_name" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">商城名称 <span style="color: #ff6b6b;">*</span></label>
                    <input type="text" id="store_name" name="store[store_name]" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <input type="hidden" name="store[sort]" value="100">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="user_name" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">商家账户名 <span style="color: #ff6b6b;">*</span></label>
                    <input type="text" id="user_name" name="store[user_name]" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <small style="color: #999; font-size: 12px; display: block; margin-top: 5px;">商家后台用户名</small>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="password" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">商家账户密码 <span style="color: #ff6b6b;">*</span></label>
                    <input type="password" id="password" name="store[password]" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                <div class="form-group" style="margin-bottom: 30px;">
                    <label for="password_confirm" style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">确认密码 <span style="color: #ff6b6b;">*</span></label>
                    <input type="password" id="password_confirm" name="store[password_confirm]" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                <div class="form-group" style="text-align: center;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 16px; cursor: pointer;">提交注册</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // 注册模态框
        const registerModal = document.getElementById('register-modal');
        const registerBtn = document.getElementById('register-btn');
        const closeBtn = registerModal.querySelector('.close');
        const registerForm = document.getElementById('register-form');

        // 打开模态框
        registerBtn.addEventListener('click', function() {
            registerModal.style.display = 'block';
        });

        // 关闭模态框
        closeBtn.addEventListener('click', function() {
            registerModal.style.display = 'none';
        });

        // 点击模态框外部关闭
        window.addEventListener('click', function(event) {
            if (event.target === registerModal) {
                registerModal.style.display = 'none';
            }
        });

        // 表单提交
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // 表单验证
            const storeName = document.getElementById('store_name').value;
            const userName = document.getElementById('user_name').value;
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            
            if (!storeName || !userName || !password || !passwordConfirm) {
                alert('请填写所有必填字段');
                return;
            }
            
            if (password !== passwordConfirm) {
                alert('两次输入的密码不一致');
                return;
            }
            
            // 构建表单数据
            const formData = new FormData(registerForm);
            
            // 发送注册请求
            fetch('<?= url('admin/store/add') ?>', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.code === 1) {
                    alert('注册成功！');
                    registerModal.style.display = 'none';
                    // 可以跳转到登录页面或其他页面
                    window.location.href = '<?= url('passport/login') ?>';
                } else {
                    alert('注册失败：' + (data.msg || '未知错误'));
                }
            })
            .catch(error => {
                console.error('注册请求失败:', error);
                alert('注册请求失败，请稍后重试');
            });
        });
    </script>
</body>
</html>
