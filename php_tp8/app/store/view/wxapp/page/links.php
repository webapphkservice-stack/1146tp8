<?php
/**
 * 页面链接速查 —— 小程序 pages/ 路径为主 + H5 对照表
 * URL: /store/wxapp.page/links.html
 *
 * 【重要】后台 wxapp_page.page_data 里存的组件跳转链接，统一是
 * 微信小程序路径（pages/xxx/index）。小程序前端 wx.navigateTo({url})
 * 直接使用。H5 端通过 LinkUrlTransformer 在渲染时自动转换为 /h5/*.html。
 * 所以装修器里配置时选 **小程序路径** 即可，一套配置两端共用。
 */
use app\common\library\LinkUrlTransformer;
$rows = LinkUrlTransformer::getMappingTable();
?>
<style>
    .links-wrap { padding: 20px; background: #f5f6f8; min-height: 100%; }
    .links-header { margin-bottom: 18px; }
    .links-header h2 { font-size: 18px; font-weight: 600; color: #1f2937; margin: 0 0 6px; }
    .links-header p  { font-size: 13px; color: #6b7280; margin: 0; line-height: 1.7; }
    .links-header b  { color: #111827; }
    .links-header code {
        background: #e0e7ff; color: #4338ca; padding: 1px 6px; border-radius: 4px;
        font-family: "SF Mono", Menlo, Consolas, monospace; font-size: 12px;
    }

    .links-table {
        width: 100%; border-collapse: collapse; background: #fff;
        border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;
        font-size: 13px;
    }
    .links-table thead tr { background: #f9fafb; }
    .links-table th, .links-table td {
        padding: 12px 14px; text-align: left; border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    .links-table th {
        font-size: 12px; font-weight: 600; color: #374151;
        letter-spacing: .3px; text-transform: uppercase;
    }
    .links-table tbody tr:last-child td { border-bottom: none; }
    .links-table tbody tr:hover { background: #f8fafc; }

    .links-table .col-note  { width: 130px; color: #6b7280; font-size: 12.5px; }
    .links-table .col-mini  { width: 38%; }
    .links-table .col-h5    { width: 38%; }
    .links-table .col-mark  { width: 60px; text-align: center; }

    .path-chip {
        display: inline-block; font-family: "SF Mono", Menlo, Consolas, monospace;
        font-size: 12px; padding: 3px 8px; border-radius: 5px;
        border: 1px solid; word-break: break-all; cursor: pointer;
        transition: background .15s, transform .1s; line-height: 1.5;
    }
    .path-chip:hover { transform: translateY(-1px); }
    .path-chip.mini { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .path-chip.mini:hover { background: #ffedd5; }
    .path-chip.h5   { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .path-chip.h5:hover   { background: #dbeafe; }
    .path-chip.copied { background: #dcfce7 !important; color: #15803d !important; border-color: #86efac !important; }

    .tag {
        display: inline-block; font-size: 10.5px; font-weight: 600;
        padding: 2px 7px; border-radius: 9px; letter-spacing: .3px;
    }
    .tag-mini { background: #fef3c7; color: #b45309; }
    .tag-h5   { background: #dbeafe; color: #1d4ed8; }
    .tag-both { background: #dcfce7; color: #15803d; }

    .links-foot {
        margin-top: 18px; display: flex; gap: 12px; flex-wrap: wrap;
    }
    .links-tip {
        flex: 1; min-width: 280px; padding: 12px 16px; border-radius: 10px; font-size: 13px; line-height: 1.7;
    }
    .links-tip.t1 { background: #eff6ff; border-left: 3px solid #3b82f6; color: #1e40af; }
    .links-tip.t2 { background: #fff7ed; border-left: 3px solid #f59e0b; color: #9a3412; }
    .links-tip code {
        background: rgba(255,255,255,.7); padding: 1px 5px; border-radius: 4px;
        font-family: "SF Mono", Menlo, Consolas, monospace; font-size: 12px;
    }

    @media (max-width: 900px) {
        .links-table { display: block; overflow-x: auto; }
        .links-table th, .links-table td { white-space: nowrap; }
    }
</style>

<div class="links-wrap">
    <div class="links-header">
        <h2>📎 页面链接速查（小程序 pages/ 为主）</h2>
        <p>
            后台组件配置跳转链接时，选左侧 <b>小程序路径</b>（<code>pages/xxx/index</code>）即可。
            H5 端会在渲染时通过 <code>LinkUrlTransformer</code> 自动转换成右侧 H5 URL。
            一套配置，两端共用。点击任意路径可复制。
        </p>
    </div>

    <table class="links-table">
        <thead>
            <tr>
                <th class="col-note">功能</th>
                <th class="col-mini">小程序路径（配置用）</th>
                <th class="col-h5">H5 对应 URL（自动转换）</th>
                <th class="col-mark">状态</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td class="col-note"><?= htmlspecialchars($row['note']) ?></td>
                <td class="col-mini">
                    <span class="path-chip mini" data-copy="<?= htmlspecialchars($row['mini']) ?>">
                        <?= htmlspecialchars($row['mini']) ?>
                    </span>
                </td>
                <td class="col-h5">
                    <span class="path-chip h5" data-copy="<?= htmlspecialchars($row['h5']) ?>">
                        <?= htmlspecialchars($row['h5']) ?>
                    </span>
                </td>
                <td class="col-mark">
                    <?php
                        $both  = strpos($row['h5'], '/h5/') === 0;
                        echo $both
                            ? '<span class="tag tag-both">双端</span>'
                            : '<span class="tag tag-mini">仅小程序</span>';
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="links-foot">
        <div class="links-tip t1">
            💡 <b>工作原理</b>：后台 → 存 <code>pages/goods/detail?goods_id=xx</code> →
            小程序直接 <code>wx.navigateTo</code> → H5 端由
            <code>app\common\library\LinkUrlTransformer::transformPageData()</code> 自动把
            <code>pages/*</code> 替换成 <code>/h5/*.html</code> → H5 前端
            <code>location.href</code> 正常跳转。
        </div>
        <div class="links-tip t2">
            ⚠️ <b>外部链接</b>（如 <code>https://...</code>）保持不变，两端都能正常跳转。
            H5 端新页面开发完成后，在 <code>LinkUrlTransformer::MAP</code> 里补一条映射即可支持自动转换。
        </div>
    </div>
</div>

<script>
    (function () {
        document.addEventListener('click', function (e) {
            var el = e.target.closest('.path-chip');
            if (!el) return;
            var text = el.getAttribute('data-copy') || el.textContent.trim();
            if (!text) return;
            var done = function () {
                el.classList.add('copied');
                var origDisplay = el.textContent;
                el.textContent = '✓ 已复制';
                setTimeout(function () {
                    el.classList.remove('copied');
                    el.textContent = origDisplay;
                }, 800);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(done).catch(function () { fallbackCopy(text, done); });
            } else {
                fallbackCopy(text, done);
            }
        });
        function fallbackCopy(text, done) {
            var ta = document.createElement('textarea');
            ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); done(); } catch (err) {}
            document.body.removeChild(ta);
        }
    })();
</script>
