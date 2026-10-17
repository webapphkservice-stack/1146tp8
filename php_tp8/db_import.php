<?php
/**
 * 第 0 批基建：数据库初始化脚本
 *
 * 功能：
 *   1. 连接 MySQL，按需创建目标库（env: DB_NAME，默认 yoshop_tp8）
 *   2. 导入 114 表基础结构（database/schema.sql，来自生产 php_my 全量 dump 副本）
 *   3. 幂等应用增量（database/delta/*.sql），已存在则跳过
 *
 * 用法：
 *   php db_import.php
 *
 * 说明：
 *   - 全程只读 php_my / sql 的副本，不触碰生产源码与生产库
 *   - 增量里部分 ALTER 在基础 dump 中已含，执行报 "Duplicate column / already exists" 会自动跳过
 *   - 可反复执行，安全幂等
 */

require __DIR__ . '/vendor/autoload.php';

/**
 * 独立脚本中 env() 助手未注册，这里轻量解析 .env 文件（兼容 KEY = VALUE 与注释行）
 */
function import_env($key, $default = '')
{
    static $env = null;
    if ($env === null) {
        $env = [];
        $file = __DIR__ . '/.env';
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, ';') || str_starts_with($line, '#')) {
                    continue;
                }
                if (preg_match('/^\s*([A-Z0-9_]+)\s*=\s*(.*)$/i', $line, $m)) {
                    $env[$m[1]] = trim($m[2], " \t\"'");
                }
            }
        }
    }
    return $env[$key] ?? $default;
}

$host = import_env('DB_HOST', '127.0.0.1');
$port = import_env('DB_PORT', '3306');
$user = import_env('DB_USER', 'root');
$pass = import_env('DB_PASS', '');
$db   = import_env('DB_NAME', 'yoshop_tp8');
$charset = 'utf8mb4';

$dsnAdmin = "mysql:host={$host};port={$port};charset={$charset}";
$opts = [
    PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
];

try {
    $pdo = new PDO($dsnAdmin, $user, $pass, $opts);
} catch (PDOException $e) {
    fwrite(STDERR, "✗ 无法连接 MySQL（请检查 .env 中 DB_HOST/DB_USER/DB_PASS）: " . $e->getMessage() . "\n");
    exit(1);
}

// 1) 创建目标库
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET {$charset} COLLATE utf8mb4_general_ci");
echo "✓ 数据库 `{$db}` 就绪\n";

// 切到目标库
$pdo->exec("USE `{$db}`");
$pdo->exec("SET NAMES {$charset}");

// 2) 基础结构（严格，失败即终止）
$base = __DIR__ . '/database/schema.sql';
if (!is_file($base)) {
    fwrite(STDERR, "✗ 找不到基础结构文件: {$base}\n");
    exit(1);
}
echo "→ 导入基础结构 database/schema.sql ...\n";
$pdo->exec(file_get_contents($base));
echo "✓ 基础结构导入完成（114 表）\n";

// 3) 增量（容错，已存在则跳过）
$deltaDir = __DIR__ . '/database/delta';
foreach (glob($deltaDir . '/*.sql') ?? [] as $file) {
    $name = basename($file);
    echo "→ 应用增量 {$name} ...\n";
    try {
        $pdo->exec(file_get_contents($file));
        echo "  ✓ {$name}\n";
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (preg_match('/Duplicate column|already exists|Duplicate entry|Table .* already exists|Unknown table/i', $msg)) {
            echo "  ⊘ 跳过（已存在）: {$msg}\n";
            continue;
        }
        fwrite(STDERR, "✗ 增量 {$name} 失败: {$msg}\n");
        exit(1);
    }
}

// 校验表数量
$count = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '{$db}'")->fetchColumn();
echo "\n✅ 完成。当前 `{$db}` 共有 {$count} 张表。\n";
