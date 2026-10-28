<div class="h5-shell fm-page">
    <!-- 顶部导航 -->
    <div class="h5-nav-bar">
        <a href="/h5/page/index.html" class="h5-back">‹</a>
        <div class="h5-search" style="flex:1; background: transparent; padding: 0;">
            <span class="h5-search__inner" style="justify-content:center; font-weight:600; color:#2d5016;">航班查询</span>
        </div>
    </div>

    <!-- Hero 区块 -->
    <div class="fm-hero">
        <h1 class="fm-hero__title">航班查询</h1>
        <p class="fm-hero__sub">免费参考航班信息</p>
    </div>

    <!-- 查询表单 -->
    <form class="fm-form" method="get" action="/h5/flight/index">
        <div class="fm-form__row">
            <label class="fm-form__label">出发地</label>
            <input class="fm-form__input" type="text" name="origin" maxlength="3" placeholder="三字码，如 PEK"
                   value="<?= htmlspecialchars($origin ?? '') ?>" style="text-transform:uppercase;"/>
        </div>
        <div class="fm-form__row">
            <label class="fm-form__label">目的地</label>
            <input class="fm-form__input" type="text" name="destination" maxlength="3" placeholder="三字码，如 SHA"
                   value="<?= htmlspecialchars($destination ?? '') ?>" style="text-transform:uppercase;"/>
        </div>
        <div class="fm-form__row">
            <label class="fm-form__label">出发日期</label>
            <input class="fm-form__input" type="date" name="date"
                   min="2025-01-01" max="2027-12-31" value="<?= htmlspecialchars($date ?? '') ?>"/>
        </div>
        <div class="fm-form__row">
            <label class="fm-form__label">舱位</label>
            <div class="fm-cabin">
                <?php foreach ([1 => '经济', 2 => '超经', 3 => '商务', 4 => '头等'] as $k => $v): ?>
                    <label class="fm-cabin__tab">
                        <input type="radio" name="cabin" value="<?= $k ?>"<?= (int)($cabin ?? 1) === $k ? ' checked' : '' ?>/>
                        <span><?= $v ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit" class="fm-form__btn">查询航班</button>
    </form>

    <?php if (!empty($error)): ?>
        <div class="h5-empty">
            <div class="h5-empty__icon">🛫</div>
            <p class="h5-empty__text"><?= htmlspecialchars($error) ?></p>
        </div>
    <?php elseif (!empty($flights)): ?>
        <?php if (!empty($title)): ?>
            <div class="fm-tag"><?= htmlspecialchars($title) ?></div>
        <?php else: ?>
            <div class="fm-tag">数据来源：<?= $source === 'api' ? '实时接口' : ($source === 'cache' ? '接口缓存' : '推荐航班') ?></div>
        <?php endif; ?>

        <div class="fm-flight-list">
            <?php foreach ($flights as $f): ?>
                <?php
                    $isStr = is_array($f) ? false : true;
                    if ($isStr) { continue; }
                    $symbol = (($f['currency'] ?? '') === 'MYR') ? 'RM ' : '¥ ';
                    $price  = $f['price'] ?? '';
                    $price  = is_numeric($price) ? number_format((float)$price, 2) : htmlspecialchars((string)$price);
                    $stops  = (int)($f['stops'] ?? 0);
                    $exts   = is_array($f['extensions'] ?? null) ? $f['extensions'] : [];
                ?>
                <div class="fm-flight-card">
                    <div class="fm-flight-card__airline"><?= htmlspecialchars(($f['airline'] ?? '') . ' · ' . ($f['flight_no'] ?? '')) ?></div>
                    <div class="fm-flight-card__route">
                        <div class="fm-flight-point">
                            <div class="fm-flight-point__time"><?= htmlspecialchars((string)($f['departure_time'] ?? '')) ?></div>
                            <div class="fm-flight-point__airport"><?= htmlspecialchars((string)($f['departure_airport'] ?? '')) ?></div>
                        </div>
                        <div class="fm-flight-mid">
                            <div class="fm-flight-mid__line"></div>
                            <div class="fm-flight-mid__duration"><?= htmlspecialchars((string)($f['duration'] ?? '')) ?></div>
                            <div class="fm-flight-mid__stops"><?= $stops > 0 ? htmlspecialchars((string)$stops) . ' 经停' : '直飞' ?></div>
                        </div>
                        <div class="fm-flight-point fm-flight-point--right">
                            <div class="fm-flight-point__time"><?= htmlspecialchars((string)($f['arrival_time'] ?? '')) ?></div>
                            <div class="fm-flight-point__airport"><?= htmlspecialchars((string)($f['arrival_airport'] ?? '')) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($exts)): ?>
                        <div class="fm-flight-card__ext">
                            <?php foreach ($exts as $ext): ?>
                                <span class="fm-flight-card__ext-item"><?= htmlspecialchars((string)$ext) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="fm-flight-card__price">
                        <span class="fm-price-tag"><?= $symbol . $price ?></span>
                        <span class="fm-flight-card__cabin"><?= htmlspecialchars((string)($f['cabin'] ?? '')) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="h5-empty">
            <div class="h5-empty__icon">🛫</div>
            <p class="h5-empty__text"><?= !empty($searched) ? '未查询到参考航班' : '请输入航线信息后查询' ?></p>
            <a href="/h5/page/index.html" class="h5-empty__btn">返回首页</a>
        </div>
    <?php endif; ?>
</div>
