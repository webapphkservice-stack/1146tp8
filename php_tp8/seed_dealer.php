<?php
/**
 * 分销（dealer）H5 测试数据播种
 *
 * 用途：让 /h5/dealer/* 各页面有真实数据可渲染（团队 / 提现明细）
 * 前置：dealer_user(10011 一级, 10012 二级)、dealer_order(900001) 已存在
 * 幂等：先清理本脚本产出的测试行，再重新写入
 *
 * 用法：php seed_dealer.php
 */

$host = '127.0.0.1';
$port = '3306';
$user = 'root';
$pass = '';
$db   = 'yoshop_tp8';
$wxappId = 10001;

$pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$now = time();

// ---------- 1) 推荐关系（团队） ----------
// 10011 的团队：10012(一级) / 10013(二级) / 10014(三级)
// 10012 的团队：10013(一级)
$pdo->exec("DELETE FROM mybaixin_dealer_referee WHERE wxapp_id={$wxappId} AND dealer_id IN (10011,10012)");
$refereeRows = [
    [10011, 10012, 1, $now - 86400 * 6],
    [10011, 10013, 2, $now - 86400 * 5],
    [10011, 10014, 3, $now - 86400 * 4],
    [10012, 10013, 1, $now - 86400 * 3],
];
$stmt = $pdo->prepare("INSERT INTO mybaixin_dealer_referee (dealer_id,user_id,level,wxapp_id,create_time) VALUES (?,?,?,?,?)");
foreach ($refereeRows as $r) {
    $stmt->execute([$r[0], $r[1], $r[2], $wxappId, $r[3]]);
}
echo "✓ dealer_referee 写入 " . count($refereeRows) . " 行\n";

// ---------- 2) 团队成员数冗余字段 ----------
$pdo->exec("UPDATE mybaixin_dealer_user SET first_num=1, second_num=1, third_num=1 WHERE wxapp_id={$wxappId} AND user_id=10011");
$pdo->exec("UPDATE mybaixin_dealer_user SET first_num=1, second_num=0, third_num=0 WHERE wxapp_id={$wxappId} AND user_id=10012");
echo "✓ dealer_user 成员数已同步\n";

// ---------- 3) 提现明细 ----------
$pdo->exec("DELETE FROM mybaixin_dealer_withdraw WHERE wxapp_id={$wxappId} AND user_id IN (10011,10012)");
$withdrawRows = [
    // user_id, money, pay_type, apply_status, days_ago
    [10011, 20.00, 30, 10, 4],  // 审核中
    [10011, 15.00, 30, 20, 8],  // 审核通过
    [10011,  5.00, 30, 30, 12], // 驳回
    [10011, 25.00, 30, 40, 20], // 已打款
    [10012, 10.00, 30, 40, 15], // 已打款
];
$stmt = $pdo->prepare("INSERT INTO mybaixin_dealer_withdraw
    (user_id,money,pay_type,bank_name,bank_account,bank_card,apply_status,wxapp_id,create_time,update_time)
    VALUES (?,?,?,?,?,?,?,?,?,?)");
foreach ($withdrawRows as $r) {
    $t = $now - 86400 * $r[4];
    $stmt->execute([$r[0], $r[1], $r[2], '中国银行', 'GUOZHIWEI', '6217000000000000', $r[3], $wxappId, $t, $t]);
}
echo "✓ dealer_withdraw 写入 " . count($withdrawRows) . " 行\n";

// ---------- 校验 ----------
foreach (['dealer_referee', 'dealer_withdraw'] as $t) {
    $n = $pdo->query("SELECT COUNT(*) FROM mybaixin_{$t} WHERE wxapp_id={$wxappId}")->fetchColumn();
    echo "  {$t} 现有 {$n} 行\n";
}
echo "✅ dealer 测试数据播种完成\n";