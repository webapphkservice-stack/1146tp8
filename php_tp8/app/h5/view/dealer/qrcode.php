<?php
/**
 * @var array $words @var array $user @var string $shareUrl
 */
$W = $words['qrcode'] ?? [];
$vw = fn($k, $d = '') => htmlspecialchars((string)($W['words'][$k]['value'] ?? $d));
?>
<div class="h5-shell">
    <div class="h5-nav-bar">
        <a href="javascript:history.back()" class="h5-back">‹</a>
        <h4><?= htmlspecialchars($words['qrcode']['title']['value'] ?? '推广二维码') ?></h4>
    </div>

    <div class="h5-body">
        <div class="h5-dealer-qr">
            <div class="h5-dealer-qr__card">
                <div class="h5-dealer-qr__code" aria-label="推广二维码">
                    <svg viewBox="0 0 21 21" shape-rendering="crispEdges">
                        <rect width="21" height="21" fill="#fff"/>
                        <!-- 三个定位角 -->
                        <g fill="#1a1a1a">
                            <path d="M0 0h7v7H0V0zm1 1v5h5V1H1zm1 1h3v3H2V2z"/>
                            <path d="M14 0h7v7h-7V0zm1 1v5h5V1h-5zm1 1h3v3h-3V2z"/>
                            <path d="M0 14h7v7H0v-7zm1 1v5h5v-5H1zm1 1h3v3H2v-3z"/>
                        </g>
                        <!-- 数据点阵（示意图案） -->
                        <g fill="#1a1a1a">
                            <rect x="9" y="0" width="1" height="1"/><rect x="11" y="2" width="1" height="1"/>
                            <rect x="9" y="4" width="1" height="1"/><rect x="12" y="5" width="1" height="1"/>
                            <rect x="0" y="9" width="1" height="1"/><rect x="2" y="9" width="1" height="1"/>
                            <rect x="4" y="9" width="1" height="1"/><rect x="6" y="10" width="1" height="1"/>
                            <rect x="8" y="9" width="1" height="1"/><rect x="10" y="9" width="1" height="1"/>
                            <rect x="12" y="9" width="1" height="1"/><rect x="14" y="10" width="1" height="1"/>
                            <rect x="16" y="9" width="1" height="1"/><rect x="18" y="9" width="1" height="1"/>
                            <rect x="20" y="9" width="1" height="1"/><rect x="9" y="11" width="1" height="1"/>
                            <rect x="11" y="11" width="1" height="1"/><rect x="13" y="12" width="1" height="1"/>
                            <rect x="15" y="11" width="1" height="1"/><rect x="17" y="11" width="1" height="1"/>
                            <rect x="19" y="12" width="1" height="1"/><rect x="10" y="13" width="1" height="1"/>
                            <rect x="12" y="13" width="1" height="1"/><rect x="14" y="14" width="1" height="1"/>
                            <rect x="16" y="13" width="1" height="1"/><rect x="18" y="13" width="1" height="1"/>
                            <rect x="20" y="14" width="1" height="1"/><rect x="9" y="15" width="1" height="1"/>
                            <rect x="11" y="16" width="1" height="1"/><rect x="13" y="15" width="1" height="1"/>
                            <rect x="15" y="15" width="1" height="1"/><rect x="17" y="16" width="1" height="1"/>
                            <rect x="19" y="15" width="1" height="1"/><rect x="10" y="17" width="1" height="1"/>
                            <rect x="12" y="18" width="1" height="1"/><rect x="14" y="17" width="1" height="1"/>
                            <rect x="16" y="18" width="1" height="1"/><rect x="18" y="17" width="1" height="1"/>
                            <rect x="20" y="18" width="1" height="1"/><rect x="9" y="19" width="1" height="1"/>
                            <rect x="11" y="20" width="1" height="1"/><rect x="13" y="19" width="1" height="1"/>
                            <rect x="15" y="20" width="1" height="1"/><rect x="17" y="20" width="1" height="1"/>
                            <rect x="19" y="20" width="1" height="1"/>
                            <rect x="9" y="9" width="3" height="3"/>
                        </g>
                    </svg>
                </div>
                <div class="h5-dealer-qr__logo">
                    <?php if (!empty($user['avatarUrl'])): ?>
                        <img src="<?= htmlspecialchars($user['avatarUrl']) ?>" alt=""/>
                    <?php endif; ?>
                </div>
            </div>

            <p class="h5-dealer-qr__name"><?= htmlspecialchars($user['nickName'] ?? '') ?></p>
            <p class="h5-dealer-qr__tip"><?= $vw('tip', '邀请好友扫码或打开链接，即可成为您的下级分销商') ?></p>

            <div class="h5-dealer-qr__link">
                <span id="q-url"><?= htmlspecialchars($shareUrl) ?></span>
                <button type="button" id="q-copy">复制</button>
            </div>

            <button type="button" class="h5-dealer-btn h5-dealer-btn--block" id="q-share">分享给好友</button>
        </div>
    </div>
</div>

<script>
(function () {
    var shareUrl = <?= json_encode($shareUrl) ?>;

    function copyText(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function (resolve, reject) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); resolve(); }
            catch (e) { reject(e); }
            document.body.removeChild(ta);
        });
    }

    document.getElementById('q-copy').addEventListener('click', function () {
        copyText(shareUrl).then(function () {
            alert('链接已复制，快去分享给好友吧');
        }).catch(function () {
            alert('复制失败，请长按链接手动复制');
        });
    });

    document.getElementById('q-share').addEventListener('click', function () {
        var abs = shareUrl.indexOf('http') === 0 ? shareUrl : (location.origin + shareUrl);
        if (navigator.share) {
            navigator.share({ title: '分销推广', text: '加入我们的分销，一起赚佣金', url: abs }).catch(function () {});
        } else {
            copyText(shareUrl).then(function () { alert('链接已复制，快去分享给好友吧'); });
        }
    });
})();
</script>