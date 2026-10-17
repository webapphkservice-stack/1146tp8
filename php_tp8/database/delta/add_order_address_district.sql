-- 补齐 mybaixin_order_address.district（区县名）
--
-- 背景（重要）：第 4 批结算台改造时，为对齐原版 TP5「订单地址快照含区/县名」，
-- 当时只在线上库直接 ALTER 加了本列，**没有同步写入 schema.sql 或 delta/**。
-- 后果：任何从 database/schema.sql 全新部署的环境，order_address 都缺少该列，
--       用户一旦提交订单（order/cart、order/buyNow）就会抛出
--       「数据表字段不存在:[district]」，下单功能整体不可用。
--
-- 本脚本为幂等增量，新环境与存量环境均可重复执行。

SET NAMES utf8mb4;

-- 通过 information_schema 判断，避免重复执行报错
SET @exist := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'mybaixin_order_address'
      AND COLUMN_NAME = 'district'
);
SET @sql := IF(@exist = 0,
    'ALTER TABLE `mybaixin_order_address` ADD COLUMN `district` varchar(255) DEFAULT NULL COMMENT ''区县名称'' AFTER `region_id`',
    'SELECT ''skip: district already exists''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT COLUMN_NAME, COLUMN_TYPE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'mybaixin_order_address'
  AND COLUMN_NAME = 'district';
