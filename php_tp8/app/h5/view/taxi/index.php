<div class="h5-shell fm-page">
    <!-- 顶部导航 -->
    <div class="h5-nav-bar">
        <a href="/h5/page/index.html" class="h5-back">‹</a>
        <div class="h5-search" style="flex:1; background: transparent; padding: 0;">
            <span class="h5-search__inner" style="justify-content:center; font-weight:600; color:#2d5016;">出租车预约</span>
        </div>
    </div>

    <!-- Hero 区块 -->
    <div class="fm-hero">
        <h1 class="fm-hero__title">出租车预约</h1>
        <p class="fm-hero__sub">选择机场与车型 · 填写航班酒店信息</p>
    </div>

    <?php if (empty($enabled)): ?>
        <div class="h5-empty">
            <div class="h5-empty__icon">🚕</div>
            <p class="h5-empty__text">出租车服务暂未开启</p>
            <a href="/h5/page/index.html" class="h5-empty__btn">返回首页</a>
        </div>
    <?php elseif (!empty($success)): ?>
        <div class="h5-empty">
            <div class="h5-empty__icon">✅</div>
            <p class="h5-empty__text">预约提交成功（单号 <?= (int)$orderId ?>）</p>
            <p style="font-size:12px; color:#8b7355; margin-top:-6px;">客服将尽快与您联系确认接机安排</p>
            <a href="/h5/page/index.html" class="h5-empty__btn">返回首页</a>
        </div>
    <?php else: ?>
        <?php if (!empty($error)): ?>
            <div class="fm-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="fm-form" method="post" action="/h5/taxi/index">
            <div class="fm-form__row">
                <label class="fm-form__label">抵达机场</label>
                <select class="fm-form__input" name="airport_name" id="fm-taxi-airport">
                    <option value="">请选择机场</option>
                    <?php foreach ($airports as $ap): ?>
                        <option value="<?= htmlspecialchars($ap['name'] ?? '') ?>"<?= ($input['airport_name'] ?? '') === ($ap['name'] ?? '') ? ' selected' : '' ?>><?= htmlspecialchars($ap['name'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fm-form__row">
                <label class="fm-form__label">车型</label>
                <div class="fm-cabin" id="fm-taxi-types">
                    <label class="fm-cabin__tab">
                        <input type="radio" name="car_type" value="0"<?= (int)($input['car_type'] ?? 0) === 0 ? ' checked' : '' ?>/>
                        <span>普通</span>
                    </label>
                    <label class="fm-cabin__tab">
                        <input type="radio" name="car_type" value="1"<?= (int)($input['car_type'] ?? 0) === 1 ? ' checked' : '' ?>/>
                        <span>商务</span>
                    </label>
                </div>
            </div>
            <div class="fm-form__row">
                <label class="fm-form__label">价格</label>
                <div class="fm-price-box"><span class="fm-price-tag" id="fm-taxi-price"><?= htmlspecialchars($currency) ?> <?= $price !== '' ? htmlspecialchars($price) : '—' ?></span></div>
            </div>

            <div class="fm-form__row">
                <label class="fm-form__label">航班号</label>
                <input class="fm-form__input" type="text" name="flight_no" placeholder="请输入航班号"
                       value="<?= htmlspecialchars($input['flight_no'] ?? '') ?>"/>
            </div>
            <div class="fm-form__row">
                <label class="fm-form__label">出发日期</label>
                <input class="fm-form__input" type="date" name="departure_time"
                       min="2020-01-01" max="2030-12-31" value="<?= htmlspecialchars($input['departure_time'] ?? '') ?>"/>
            </div>
            <div class="fm-form__row">
                <label class="fm-form__label">起飞时间</label>
                <input class="fm-form__input" type="time" name="takeoff_time" value="<?= htmlspecialchars($input['takeoff_time'] ?? '') ?>"/>
            </div>
            <div class="fm-form__row">
                <label class="fm-form__label">降落时间</label>
                <input class="fm-form__input" type="time" name="landing_time" value="<?= htmlspecialchars($input['landing_time'] ?? '') ?>"/>
            </div>
            <div class="fm-form__row">
                <label class="fm-form__label">酒店名称</label>
                <input class="fm-form__input" type="text" name="hotel_name" placeholder="请输入酒店名称"
                       value="<?= htmlspecialchars($input['hotel_name'] ?? '') ?>"/>
            </div>
            <div class="fm-form__row">
                <label class="fm-form__label">酒店地址</label>
                <input class="fm-form__input" type="text" name="hotel_address" placeholder="请输入酒店地址"
                       value="<?= htmlspecialchars($input['hotel_address'] ?? '') ?>"/>
            </div>
            <div class="fm-form__row">
                <label class="fm-form__label">联系人</label>
                <input class="fm-form__input" type="text" name="contact_name" placeholder="请输入联系人姓名"
                       value="<?= htmlspecialchars($input['contact_name'] ?? '') ?>"/>
            </div>
            <div class="fm-form__row">
                <label class="fm-form__label">联系电话</label>
                <input class="fm-form__input" type="text" name="contact_phone" placeholder="请输入联系电话"
                       value="<?= htmlspecialchars($input['contact_phone'] ?? '') ?>"/>
            </div>
            <div class="fm-form__row">
                <label class="fm-form__label">备注</label>
                <input class="fm-form__input" type="text" name="remark" placeholder="选填，如行李件数"
                       value="<?= htmlspecialchars($input['remark'] ?? '') ?>"/>
            </div>
            <button type="submit" class="fm-form__btn">提交预约</button>
        </form>

        <script>
        (function () {
            var airports = <?= json_encode($airports, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '[]' ?>;
            var cur = <?= json_encode($currency, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '"RM"' ?>;
            var elAp = document.getElementById('fm-taxi-airport');
            var elPr = document.getElementById('fm-taxi-price');
            function refresh() {
                var name = elAp ? elAp.value : '';
                var type = document.querySelector('input[name="car_type"]:checked');
                var typeVal = type ? type.value : '0';
                var price = '—';
                for (var i = 0; i < airports.length; i++) {
                    if (airports[i] && airports[i].name === name) {
                        price = (typeVal === '1' ? (airports[i].price_business != null ? airports[i].price_business : '—')
                                                 : (airports[i].price_normal != null ? airports[i].price_normal : '—'));
                        break;
                    }
                }
                if (elPr) { elPr.textContent = cur + ' ' + price; }
            }
            if (elAp) { elAp.addEventListener('change', refresh); }
            var radios = document.querySelectorAll('input[name="car_type"]');
            for (var j = 0; j < radios.length; j++) { radios[j].addEventListener('change', refresh); }
        })();
        </script>
    <?php endif; ?>
</div>
