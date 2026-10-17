-- P1 优惠券 / 积分抵扣 端到端测试种子数据（幂等，可重复执行）
-- 用法：mysql -u root yoshop_tp8 < database/seed_p1_discount_test.sql
-- 清理：mysql -u root yoshop_tp8 < database/cleanup_p1_discount_test.sql

SET NAMES utf8mb4;

-- ==================== 清理 ====================
DELETE FROM mybaixin_user_coupon WHERE coupon_id BETWEEN 90001 AND 90010;
DELETE FROM mybaixin_coupon WHERE coupon_id BETWEEN 90001 AND 90010;
-- 清空测试用户的积分流水。
-- 注意：不要写成 `user_id=10011 AND describe LIKE 'xxx' OR describe LIKE 'yyy'`——
-- AND 优先级高于 OR，且订单号由 date('YmdHis')+随机数生成（并非 P1 前缀），
-- 会导致条件永远匹配不到、历史流水累积，测试断言计数失真。
DELETE FROM mybaixin_user_points_log WHERE user_id = 10011;
DELETE FROM mybaixin_order_goods WHERE order_id BETWEEN 910000 AND 910099;
DELETE FROM mybaixin_order WHERE order_no LIKE 'P1TEST%';
DELETE FROM mybaixin_order_address WHERE order_id BETWEEN 910000 AND 910099;

-- ==================== 1. 优惠券（模板） ====================
-- 90001 满 100 减 20（全场通用）
-- 90002 满 100 享 9 折（全场通用）
-- 90003 满 1000 减 100（门槛不满足，预期不出现在可用列表）
-- 90004 满 100 减 30，仅限商品 99999（适用范围不匹配，预期 is_apply=false）
INSERT INTO mybaixin_coupon
  (coupon_id, name, color, coupon_type, reduce_price, discount, min_price,
   expire_type, expire_day, start_time, end_time, apply_range, apply_range_config,
   total_num, receive_num, sort, is_delete, wxapp_id, create_time, update_time)
VALUES
  (90001, 'P1测试-满100减20', 10, 10, 20.00, 0, 100.00,
   10, 30, 0, 0, 10, '{}',
   100, 0, 100, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (90002, 'P1测试-满100享9折', 20, 20, 0.00, 9, 100.00,
   10, 30, 0, 0, 10, '{}',
   100, 0, 100, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (90003, 'P1测试-满1000减100', 10, 10, 100.00, 0, 1000.00,
   10, 30, 0, 0, 10, '{}',
   100, 0, 100, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (90004, 'P1测试-指定商品券', 20, 10, 30.00, 0, 100.00,
   10, 30, 0, 0, 20, '{"applyGoodsIds":[99999]}',
   100, 0, 100, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ==================== 2. 发放给用户 10011 ====================
INSERT INTO mybaixin_user_coupon
  (user_coupon_id, coupon_id, name, color, coupon_type, reduce_price, discount, min_price,
   expire_type, expire_day, start_time, end_time, apply_range, apply_range_config,
   is_expire, is_use, user_id, wxapp_id, create_time, update_time)
VALUES
  (91001, 90001, 'P1测试-满100减20', 10, 10, 20.00, 0, 100.00,
   10, 30, UNIX_TIMESTAMP() - 86400, UNIX_TIMESTAMP() + 30*86400, 10, '{}',
   0, 0, 10011, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (91002, 90002, 'P1测试-满100享9折', 20, 20, 0.00, 9, 100.00,
   10, 30, UNIX_TIMESTAMP() - 86400, UNIX_TIMESTAMP() + 30*86400, 10, '{}',
   0, 0, 10011, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (91003, 90003, 'P1测试-满1000减100', 10, 10, 100.00, 0, 1000.00,
   10, 30, UNIX_TIMESTAMP() - 86400, UNIX_TIMESTAMP() + 30*86400, 10, '{}',
   0, 0, 10011, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (91004, 90004, 'P1测试-指定商品券', 20, 10, 30.00, 0, 100.00,
   10, 30, UNIX_TIMESTAMP() - 86400, UNIX_TIMESTAMP() + 30*86400, 20, '{"applyGoodsIds":[99999]}',
   0, 0, 10011, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- ==================== 3. 开启积分抵扣 + 赠送 ====================
-- 原值备份到临时表，便于 cleanup 还原
DROP TEMPORARY TABLE IF EXISTS tmp_p1_points_backup;
CREATE TEMPORARY TABLE tmp_p1_points_backup AS
  SELECT `values` FROM mybaixin_setting WHERE `key` = 'points' AND wxapp_id = 10001;

UPDATE mybaixin_setting
SET `values` = JSON_UNQUOTE(JSON_REPLACE(`values`,
     '$.is_shopping_discount', '1',
     '$.is_shopping_gift', '1',
     '$.gift_ratio', '100',
     '$.discount.full_order_price', '100.00',
     '$.discount.max_money_ratio', '10',
     '$.discount.discount_ratio', '0.01'))
WHERE `key` = 'points' AND wxapp_id = 10001;

-- ==================== 4. 给用户 10011 积分与收货地址 ====================
UPDATE mybaixin_user SET points = 30, balance = 1000.00 WHERE user_id = 10011;

-- 确保有收货地址（下单必需）
DELETE FROM mybaixin_user_address WHERE address_id = 91001;
INSERT INTO mybaixin_user_address
  (address_id, name, phone, province_id, city_id, region_id, district,
   country, state, city, detail, user_id, wxapp_id, create_time, update_time)
VALUES
  (91001, 'P1测试收货人', '13800138000', 1964, 1988, 1991, '南山区',
   'CN', '广东省', '深圳市', '科技园路1号', 10011, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
UPDATE mybaixin_user SET address_id = 91001 WHERE user_id = 10011;

-- ==================== 5. 测试商品：10002（DurianBB，售价 200.00，sku 10013） ====================
-- 注意：不选用 goods_id=10001，该商品在本库中 is_delete=1（已软删除），
--       结算时会直接报「商品不存在或已下架」。
UPDATE mybaixin_goods_sku SET stock_num = 1000 WHERE goods_sku_id = 10013;
UPDATE mybaixin_goods SET is_points_discount = 1, is_points_gift = 1 WHERE goods_id = 10002;

-- 输出校验
SELECT 'p1 seed ok' AS result,
  (SELECT COUNT(*) FROM mybaixin_user_coupon WHERE user_id = 10011 AND coupon_id BETWEEN 90001 AND 90010) AS coupons,
  (SELECT points FROM mybaixin_user WHERE user_id = 10011) AS points,
  (SELECT JSON_EXTRACT(`values`, '$.is_shopping_discount') FROM mybaixin_setting WHERE `key`='points' AND wxapp_id=10001) AS pts_discount_on;
