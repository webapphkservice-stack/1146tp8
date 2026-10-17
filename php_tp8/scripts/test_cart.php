<?php
/**
 * H5 购物车 端到端测试（原生 PHP，零依赖）
 * 运行：php scripts/test_cart.php
 *
 * 覆盖 Cart model + Cart controller + Goods detail 加购链路
 */
$root = __DIR__ . '/../public';
$base = 'http://127.0.0.1:8070';
$passport = "$base/h5/passport/doLogin.html";
$cartAdd  = "$base/h5/cart/add.html";
$cartSub  = "$base/h5/cart/sub.html";
$cartDel  = "$base/h5/cart/delete.html";
$cartIdx  = "$base/h5/cart/index.html";
$goodsDet = "$base/h5/goods/detail.html";

// -------- 工具 --------
function httpPost($url, $body, $headers = []) {
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n" . implode("\r\n", $headers),
            'content' => is_array($body) ? http_build_query($body) : $body,
            'timeout' => 5,
        ],
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    $code = 0;
    foreach (($http_response_header ?? []) as $h) {
        if (preg_match('/HTTP\/\S+\s+(\d+)/', $h, $m)) { $code = (int)$m[1]; break; }
    }
    return ['code' => $code, 'body' => $resp ?: '', 'json' => json_decode($resp, true)];
}
function httpGet($url) {
    $ctx = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 5]]);
    $resp = @file_get_contents($url, false, $ctx);
    $code = 0;
    foreach (($http_response_header ?? []) as $h) {
        if (preg_match('/HTTP\/\S+\s+(\d+)/', $h, $m)) { $code = (int)$m[1]; break; }
    }
    return ['code' => $code, 'body' => $resp ?: ''];
}
function ok($name, $cond, $detail = '') {
    global $pass, $fail;
    $pass = $pass ?? 0; $fail = $fail ?? 0;
    if ($cond) {
        echo "  ✅ $name\n"; $pass++;
    } else {
        echo "  ❌ $name  $detail\n"; $fail++;
    }
}

// -------- 测试准备 --------
echo "=== Cart E2E Tests ===\n\n";

// 1) 登录拿真实 token
echo "1. 登录\n";
$r = httpPost($passport, ['account' => '13918668419', 'password' => '1']);
assert($r['code'] === 200, 'login HTTP 200');
assert($r['json']['code'] === 1, 'login 成功');
$token = $r['json']['data']['token'];
ok('登录成功', !empty($token));

// 2) 清空残留购物车
echo "\n2. 清空旧数据\n";
$r = httpPost("$cartDel?token=$token", ['cart_ids' => '']);
ok('清空购物车', $r['json']['code'] === 1);

// 3) 未登录 → 401/302
echo "\n3. 未登录访问 add\n";
$r = httpPost($cartAdd, ['goods_id' => 10002, 'goods_sku_id' => 0, 'goods_num' => 1]);
ok('未登录加购返回错误', $r['json']['code'] === 0 && strpos($r['json']['msg'], '登录') !== false);

// 4) 不存在商品 → 错误
echo "\n4. 不存在商品 → 错误\n";
$r = httpPost("$cartAdd?token=$token", ['goods_id' => 99999, 'goods_sku_id' => 0, 'goods_num' => 1]);
ok('不存在商品返回 false', $r['json']['code'] === 0);

// 5) 正常加购 × 2 件
echo "\n5. 加购 goods_id=10002 × 2\n";
$r = httpPost("$cartAdd?token=$token", ['goods_id' => 10002, 'goods_sku_id' => 0, 'goods_num' => 2]);
ok('加购成功', $r['json']['code'] === 1);
ok('total_num = 2', $r['json']['data']['total_num'] == 2);
ok('list 有 1 行', count($r['json']['data']['list']) === 1);
ok('price = 200.00', (float)$r['json']['data']['list'][0]['goods_price'] === 200.0);
ok('total_price = 400.00', (float)$r['json']['data']['total_price'] === 400.0);

// 6) 同商品重复加购 → 累加
echo "\n6. 同商品再加 3 件 → 累加到 5\n";
$r = httpPost("$cartAdd?token=$token", ['goods_id' => 10002, 'goods_sku_id' => 0, 'goods_num' => 3]);
ok('累加成功', $r['json']['code'] === 1);
ok('数量 = 5', $r['json']['data']['list'][0]['total_num'] == 5);
ok('total_price = 1000', (float)$r['json']['data']['total_price'] === 1000.0);

// 7) 再加一个不同商品
echo "\n7. 加购 goods_id=10003 × 1\n";
$r = httpPost("$cartAdd?token=$token", ['goods_id' => 10003, 'goods_sku_id' => 0, 'goods_num' => 1]);
ok('第二个商品成功', $r['json']['code'] === 1);
ok('total_num = 6', $r['json']['data']['total_num'] == 6);
ok('list 有 2 行', count($r['json']['data']['list']) === 2);
// 200*5 + 266*1 = 1266
ok('total_price = 1266', (float)$r['json']['data']['total_price'] === 1266.0);

// 8) sub 减少 10002
echo "\n8. sub goods_id=10002 × 1 → 4\n";
$r = httpPost("$cartSub?token=$token", ['goods_id' => 10002, 'goods_sku_id' => 0]);
ok('sub 成功', $r['json']['code'] === 1);
$good1 = null;
foreach ($r['json']['data']['list'] as $g) if ($g['goods_id'] == 10002) $good1 = $g;
ok('10002 数量 = 4', $good1 && $good1['total_num'] == 4);

// 9) sub 到 1 不再减
echo "\n9. sub 到 1 不再减\n";
for ($i = 0; $i < 10; $i++) {
    $r = httpPost("$cartSub?token=$token", ['goods_id' => 10003, 'goods_sku_id' => 0]);
}
$good2 = null;
foreach ($r['json']['data']['list'] as $g) if ($g['goods_id'] == 10003) $good2 = $g;
ok('10003 永远 ≥ 1', $good2 && $good2['total_num'] >= 1, '实际=' . ($good2['total_num'] ?? 'null'));

// 10) delete 单商品
echo "\n10. delete goods_id=10003\n";
$r = httpPost("$cartDel?token=$token", ['cart_ids' => '10003_0']);
ok('删除成功', $r['json']['code'] === 1);
ok('list 只剩 1 行', count($r['json']['data']['list']) === 1);
ok('total_num = 4', $r['json']['data']['total_num'] == 4);

// 11) 清空
echo "\n11. 清空购物车\n";
$r = httpPost("$cartDel?token=$token", ['cart_ids' => '']);
ok('清空成功', $r['json']['code'] === 1);
ok('total_num = 0', $r['json']['data']['total_num'] == 0);
ok('list 空', count($r['json']['data']['list']) === 0);

// 12) 空 cart index 渲染
echo "\n12. 空购物车 GET 渲染\n";
$r = httpGet("$cartIdx?token=$token");
ok('HTTP 200', $r['code'] === 200);
ok('空状态文案', strpos($r['body'], '购物车还是空的') !== false);

// 13) 有商品 GET 渲染
echo "\n13. 加购后 GET 渲染\n";
httpPost("$cartAdd?token=$token", ['goods_id' => 10002, 'goods_sku_id' => 0, 'goods_num' => 2]);
httpPost("$cartAdd?token=$token", ['goods_id' => 10003, 'goods_sku_id' => 0, 'goods_num' => 1]);
$r = httpGet("$cartIdx?token=$token");
ok('HTTP 200', $r['code'] === 200);
ok('商品名 DurianBB', strpos($r['body'], 'DurianBB') !== false);
ok('商品名 LABUBU', strpos($r['body'], 'LABUBU') !== false);
ok('去结算按钮', strpos($r['body'], '去结算') !== false);

// 14) goods detail 渲染（含加购按钮）
echo "\n14. 商品详情页\n";
$r = httpGet("$goodsDet?goods_id=10002&token=$token");
ok('HTTP 200', $r['code'] === 200);
ok('加入购物车按钮', strpos($r['body'], '加入购物车') !== false);
ok('SKU 选择器', strpos($r['body'], 'h5-detail-skus') !== false);

// 15) token 拼错 → 跳登录
echo "\n15. 假 token → 跳登录\n";
$r = httpGet("$cartIdx?token=fake_token_xxxxx");
ok('重定向到登录', strpos($r['body'], 'passport/login') !== false || $r['code'] === 302);

// -------- 汇总 --------
$pass = $GLOBALS["pass"] ?? 0; $fail = $GLOBALS["fail"] ?? 0;
$total = $pass + $fail;
echo "\n========== $pass/$total 通过 ==========\n";
exit($fail === 0 ? 0 : 1);
