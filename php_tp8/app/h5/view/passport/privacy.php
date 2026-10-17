<?php
/**
 * H5 隐私政策页（独立页面，不继承 H5 layout，干净文档风）
 * URL: /h5/passport/privacy
 */
$storeName = $storeName ?? '百信优选';
$year = date('Y');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>隐私政策 - <?= htmlspecialchars($storeName) ?></title>
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
            margin-right: 60px;
        }
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
        .doc-section { margin-bottom: 22px; }
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
        .doc-ul { padding-left: 20px; margin-bottom: 8px; }
        .doc-ul li { font-size: 14px; color: #4b5563; margin-bottom: 4px; }
        .doc-grid {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 8px 14px;
            font-size: 13.5px;
            background: #f9fafb;
            padding: 14px 16px;
            border-radius: 10px;
            margin: 10px 0;
        }
        .doc-grid dt {
            color: #6b7280;
            font-weight: 500;
        }
        .doc-grid dd {
            color: #374151;
            margin: 0;
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
        <div class="doc-header__title">隐私政策</div>
    </header>

    <main class="doc-body">
        <h1 class="doc-title"><?= htmlspecialchars($storeName) ?> 隐私政策</h1>
        <div class="doc-meta">生效日期：<?= $year ?> 年 1 月 1 日 &nbsp;·&nbsp; 更新日期：<?= $year ?> 年 1 月 1 日</div>

        <div class="doc-section">
            <p class="doc-p"><b><?= htmlspecialchars($storeName) ?></b>（以下简称"本平台"）深知个人信息对您的重要性，并将按照法律法规的规定，采取相应安全保护措施，尽力保护您的个人信息安全可控。</p>
            <div class="doc-tip">本隐私政策将帮助您了解本平台如何收集、使用、存储和保护您的个人信息。请您在使用本平台服务前仔细阅读本政策。</div>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">一、我们收集的信息</h2>
            <p class="doc-p">我们仅在为您提供服务所必需的范围内收集以下信息：</p>
            <dl class="doc-grid">
                <dt>账户信息</dt>
                <dd>您注册时填写的手机号、昵称等基本信息</dd>
                <dt>交易信息</dt>
                <dd>订单记录、收货地址、支付方式、消费金额等</dd>
                <dt>设备信息</dt>
                <dd>设备型号、操作系统版本、浏览器类型、IP 地址</dd>
                <dt>位置信息</dt>
                <dd>您授权同意的定位信息（用于同城配送等服务）</dd>
                <dt>日志信息</dt>
                <dd>您在本平台的操作记录、搜索历史、访问时间等</dd>
            </dl>
            <p class="doc-p">我们<b>不会</b>收集您的银行账号密码、支付密码、人脸识别信息等敏感数据，这些数据由第三方支付机构直接处理。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">二、我们如何使用信息</h2>
            <ul class="doc-ul">
                <li>为您提供商品浏览、下单、支付、配送等核心服务；</li>
                <li>完成身份核验、订单管理、售后服务等业务流程；</li>
                <li>保障账号安全、防范欺诈、保障交易安全；</li>
                <li>向您推送个性化商品推荐、营销活动信息（您可随时关闭）；</li>
                <li>改进产品和服务质量、优化用户体验；</li>
                <li>遵守法律法规、履行法定义务。</li>
            </ul>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">三、信息的共享、转让与公开</h2>
            <h3 class="doc-h3">3.1 共享</h3>
            <p class="doc-p">我们不会与任何第三方共享您的个人信息，但以下情况除外：</p>
            <ul class="doc-ul">
                <li>获得您的明确同意；</li>
                <li>为完成交易必须共享给合作方（如物流公司），合作方仅能获得完成服务所必需的信息；</li>
                <li>根据法律法规、诉讼、仲裁、政府主管部门强制性要求；</li>
                <li>为维护本平台、用户或社会公众的合法权益。</li>
            </ul>
            <h3 class="doc-h3">3.2 转让</h3>
            <p class="doc-p">我们不会将您的个人信息转让给任何公司、组织和个人，但以下情况除外：</p>
            <ul class="doc-ul">
                <li>获得您的明确同意；</li>
                <li>在涉及合并、收购或破产清算时，我们会要求新持有方继续受本政策约束。</li>
            </ul>
            <h3 class="doc-h3">3.3 公开</h3>
            <p class="doc-p">我们仅会在获得您明确同意或法律法规允许的范围内公开您的个人信息。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">四、信息的存储与保护</h2>
            <h3 class="doc-h3">4.1 存储范围</h3>
            <p class="doc-p">您的个人信息存储在中华人民共和国境内。如需跨境传输，我们会按照法律法规的要求进行安全评估并获得您的同意。</p>
            <h3 class="doc-h3">4.2 存储期限</h3>
            <p class="doc-p">我们仅在实现服务目的所必需的最短期限内保留您的个人信息。超出期限后，我们将及时删除或匿名化处理。法律法规另有规定的除外。</p>
            <h3 class="doc-h3">4.3 安全措施</h3>
            <ul class="doc-ul">
                <li>采用加密传输（HTTPS/SSL）保护数据传输安全；</li>
                <li>对敏感信息进行加密存储；</li>
                <li>实施严格的数据访问权限控制和内部管理制度；</li>
                <li>定期进行安全审计和漏洞检测。</li>
            </ul>
            <div class="doc-tip">尽管我们采取了合理的安全措施，但互联网环境并非绝对安全。如发生个人信息安全事件，我们将及时通知您并采取补救措施。</div>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">五、您的权利</h2>
            <p class="doc-p">按照相关法律法规，您对您的个人信息享有以下权利：</p>
            <ul class="doc-ul">
                <li><b>查阅与更正</b>：您可以在个人中心查看和修改您的账户信息；</li>
                <li><b>删除</b>：您可以申请删除您的个人信息，我们将在符合法律法规的前提下予以删除；</li>
                <li><b>撤回授权</b>：您可以随时撤回之前给予的授权同意；</li>
                <li><b>注销账号</b>：您有权随时注销您的账号；</li>
                <li><b>投诉举报</b>：如您对我们处理个人信息的方式有异议，可通过本平台客服渠道投诉。</li>
            </ul>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">六、Cookie 和类似技术</h2>
            <p class="doc-p">我们使用 Cookie 和类似技术来保障服务安全、提升用户体验、进行统计分析。您可以通过浏览器设置拒绝或管理 Cookie，但这可能影响部分功能的使用。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">七、未成年人保护</h2>
            <p class="doc-p">本平台不面向未满 18 周岁的未成年人提供服务。如您是未成年人，请在监护人的陪同下使用本平台服务，并确保已获得监护人的同意。我们不会主动收集未成年人的个人信息。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">八、政策更新</h2>
            <p class="doc-p">我们可能会不时更新本隐私政策。更新后的政策将在本平台公布，重大变更时我们会通过站内通知、短信等方式告知您。您继续使用本平台服务即视为同意受更新后的政策约束。</p>
        </div>

        <div class="doc-section">
            <h2 class="doc-h2">九、联系我们</h2>
            <p class="doc-p">如您对本隐私政策有任何疑问、意见或建议，可通过本平台公布的客服渠道与我们联系。我们将在 15 个工作日内予以答复。</p>
        </div>

        <div class="doc-footer">
            Copyright © <?= $year ?> <?= htmlspecialchars($storeName) ?>. All Rights Reserved.
        </div>
    </main>
</body>
</html>
