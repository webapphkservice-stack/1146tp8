<?php
/**
 * H5 用户协议页（独立页面，不继承 H5 layout，干净文档风）
 * URL: /h5/passport/agreement
 */
$storeName = $storeName ?? '百信优选';
$year = date('Y');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>用户协议 - <?= htmlspecialchars($storeName) ?></title>
    <link rel="icon" type="image/png" href="/assets/common/i/favicon.ico"/>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", sans-serif;
            background: #f5f6f8;
            color: #1f2937;
            line-height: 1.8;
            padding-bottom: 80px;
        }
        /* 顶部导航 */
        .doc-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #e8e8e8;
            display: flex;
            align-items: center;
            padding: 12px 16px;
        }
        .doc-header__back {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #374151;
            text-decoration: none;
            font-size: 14px;
            padding: 6px 10px;
            border-radius: 8px;
            transition: background .15s;
        }
        .doc-header__back:hover { background: #f3f4f6; }
        .doc-header__title {
            flex: 1;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-right: 60px; /* 抵消 back 按钮占位 */
        }
        /* 正文卡片 */
        .doc-body {
            max-width: 720px;
            margin: 16px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            padding: 28px 24px 32px;
        }
        .doc-title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }
        .doc-meta {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid #f0f0f0;
        }
        .doc-section {
            margin-bottom: 22px;
        }
        .doc-h2 {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 10px;
            padding-left: 10px;
            border-left: 3px solid #2563eb;
        }
        .doc-h3 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin: 12px 0 6px;
        }
        .doc-p {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 8px;
            text-align: justify;
        }
        .doc-ul {
            padding-left: 20px;
            margin-bottom: 8px;
        }
        .doc-ul li {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 4px;
        }
        .doc-tip {
            background: #eff6ff;
            border-left: 3px solid #3b82f6;
            padding: 10px 14px;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
            color: #1e40af;
            margin: 10px 0;
        }
        .doc-footer {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid #f0f0f0;
        }
        @media (max-width: 480px) {
            .doc-body { margin: 12px; padding: 22px 18px 26px; border-radius: 12px; }
            .doc-title { font-size: 18px; }
            .doc-p, .doc-ul li { font-size: 13.5px; }
        }
    </style>
</head>
<body>
    <header class="doc-header">
        <a href="javascript:history.back()" class="doc-header__back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            返回
        </a>
        <div class="doc-header__title">用户协议</div>
    </header>

    <main class="doc-body">
        <h1 class="doc-title"><?= htmlspecialchars($storeName) ?> 用户协议</h1>
        <div class="doc-meta">生效日期：<?= $year ?> 年 1 月 1 日 &nbsp;·&nbsp; 更新日期：<?= $year ?> 年 1 月 1 日</div>

        <div class="doc-section">
            <p class="doc-p">欢迎您使用 <b><?= htmlspecialchars($storeName) ?></b>（以下简称"本平台"）提供的服务。本协议是您与本平台之间就使用本平台服务所订立的协议。请您在使用本平台服务前，仔细阅读并充分理解本协议的全部内容。</p>
            <div class="doc-tip">当您开始注册、登录或使用本平台服务时，即视为您已充分阅读、理解并接受本协议的全部内容。</div>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">一、协议的接受与修改</h2>
            <p class="doc-p"><b>1.1</b> 您通过网络页面确认、实际使用本平台服务等行为，即表示您已阅读、理解并同意受本协议全部条款的约束。</p>
            <p class="doc-p"><b>1.2</b> 本平台有权根据法律法规、监管政策或实际运营需要，对本协议进行修改。修改后的协议将在本平台公布，若您在协议修改后继续使用本平台服务，即视为您接受修改后的协议。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">二、账号注册与管理</h2>
            <p class="doc-p"><b>2.1</b> 您在注册账号时，应提供真实、准确、完整的个人资料，并在资料发生变更时及时更新。因您提供虚假或不准确的资料引起的后果，由您自行承担。</p>
            <p class="doc-p"><b>2.2</b> 您应妥善保管账号和密码，因您保管不当造成的损失，由您自行承担。如发现账号被盗用或存在安全风险，您应立即通知本平台。</p>
            <p class="doc-p"><b>2.3</b> 您的账号仅限您本人使用，禁止出售、出借、赠与或以其他方式转让给他人使用。本平台有权对账号使用情况进行监督，发现违规使用的，有权冻结或注销该账号。</p>
            <p class="doc-p"><b>2.4</b> 本平台有权根据实际情况，对长期未登录的账号进行清理或注销处理。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">三、用户行为规范</h2>
            <p class="doc-p">您在使用本平台服务时，不得从事以下行为：</p>
            <ul class="doc-ul">
                <li>违反宪法、法律、行政法规、部门规章的行为；</li>
                <li>危害国家安全、泄露国家秘密、颠覆国家政权、破坏国家统一的行为；</li>
                <li>侵害他人知识产权、肖像权、名誉权、隐私权等合法权益的行为；</li>
                <li>发布、传播淫秽、色情、赌博、暴力、凶杀、恐怖内容的行为；</li>
                <li>虚构事实、散布谣言、扰乱社会秩序的行为；</li>
                <li>恶意刷单、套取补贴、利用技术手段攻击本平台系统的行为；</li>
                <li>其他违反公序良俗或损害本平台利益的行为。</li>
            </ul>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">四、商品与服务</h2>
            <p class="doc-p"><b>4.1</b> 本平台展示的商品信息由合作商家或供应商提供，本平台不对商品信息的真实性、准确性作实质性审查，但会尽力保障信息的合法合规。</p>
            <p class="doc-p"><b>4.2</b> 商品价格、库存、促销活动等信息以实际下单时的展示为准。如因系统故障、人工失误等原因导致价格异常，本平台有权取消订单并全额退款。</p>
            <p class="doc-p"><b>4.3</b> 您在本平台购买商品后，应按照约定方式进行支付。支付成功后，本平台会将订单信息推送至对应商家进行发货处理。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">五、知识产权</h2>
            <p class="doc-p">本平台所有内容，包括但不限于文字、图片、音频、视频、软件、界面设计、版面编排、商标、标识等，均受《中华人民共和国著作权法》《中华人民共和国商标法》及其他相关法律保护。未经本平台书面许可，任何人不得以任何形式复制、传播、展示、修改、分发或以其他方式使用本平台内容。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">六、免责声明</h2>
            <p class="doc-p"><b>6.1</b> 因不可抗力（包括但不限于自然灾害、政府行为、法律法规变化、网络攻击、系统故障等）导致的服务中断或数据损失，本平台不承担赔偿责任。</p>
            <p class="doc-p"><b>6.2</b> 本平台展示的外部链接内容、第三方广告等，由相应主体负责，本平台不对其真实性、合法性负责。</p>
            <p class="doc-p"><b>6.3</b> 您因违反本协议或法律法规而产生的后果，由您自行承担，本平台不承担连带责任。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">七、协议终止</h2>
            <p class="doc-p"><b>7.1</b> 您有权随时注销账号并终止本协议。账号注销后，本平台有权删除您的个人信息，但法律法规另有规定的除外。</p>
            <p class="doc-p"><b>7.2</b> 如您严重违反本协议，本平台有权单方面终止向您提供服务，并注销您的账号，由此产生的损失由您自行承担。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">八、争议解决</h2>
            <p class="doc-p">本协议的订立、执行、解释及争议的解决均应适用中华人民共和国法律。因本协议引起的或与本协议有关的任何争议，双方应友好协商解决；协商不成的，任何一方均可向本平台运营方所在地有管辖权的人民法院提起诉讼。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">九、联系方式</h2>
            <p class="doc-p">如您对本协议有任何疑问或建议，可通过本平台公布的客服渠道与我们联系。我们将在收到您的反馈后尽快予以答复。</p>
        </div>

        <div class="doc-footer">
            Copyright © <?= $year ?> <?= htmlspecialchars($storeName) ?>. All Rights Reserved.
        </div>
    </main>
</body>
</html>
