-- P1 优惠券 / 积分抵扣 测试数据清理（恢复环境到测试前状态）
-- 用法：mysql -u root yoshop_tp8 < database/cleanup_p1_discount_test.sql

SET NAMES utf8mb4;

-- 清理优惠券
DELETE FROM mybaixin_user_coupon WHERE coupon_id BETWEEN 90001 AND 90010;
DELETE FROM mybaixin_coupon WHERE coupon_id BETWEEN 90001 AND 90010;

-- 清理测试订单
DELETE FROM mybaixin_order_goods WHERE order_id BETWEEN 910000 AND 910099;
DELETE FROM mybaixin_order_address WHERE order_id BETWEEN 910000 AND 910099;
DELETE FROM mybaixin_order WHERE order_no LIKE 'P1TEST%';
DELETE FROM mybaixin_order WHERE order_id BETWEEN 910000 AND 910099;

-- 清理积分流水
DELETE FROM mybaixin_user_points_log WHERE user_id = 10011;

-- 清理测试地址，并把默认地址回退到该用户剩余的第一条（没有则 0）
DELETE FROM mybaixin_user_address WHERE address_id = 91001;
UPDATE mybaixin_user u
SET u.address_id = COALESCE(
    (SELECT MIN(a.address_id) FROM mybaixin_user_address a WHERE a.user_id = u.user_id), 0)
WHERE u.user_id = 10011;

-- 恢复用户积分 / 余额（测试前：points=30, balance=0.00）
UPDATE mybaixin_user SET points = 30, balance = 0.00 WHERE user_id = 10011;

-- 恢复商品库存（测试前：goods_sku_id=10013 的 stock_num=93）
UPDATE mybaixin_goods_sku SET stock_num = 93 WHERE goods_sku_id = 10013;
-- 商品 10002 的积分开关保持开启（is_points_discount/is_points_gift=1）——
-- 这是「允许该商品参与积分抵扣/赠送」的正常业务配置，不作为测试数据回滚。

-- 恢复积分设置（关闭积分抵扣与赠送，还原为测试前的值）
UPDATE mybaixin_setting
SET `values` = JSON_UNQUOTE(JSON_REPLACE(`values`,
     '$.is_shopping_discount', '0',
     '$.is_shopping_gift', '0'))
WHERE `key` = 'points' AND wxapp_id = 10001;

SELECT 'p1 cleanup ok' AS result,
  (SELECT COUNT(*) FROM mybaixin_user_coupon WHERE coupon_id BETWEEN 90001 AND 90010) AS left_coupons,
  (SELECT COUNT(*) FROM mybaixin_order WHERE order_no LIKE 'P1TEST%') AS left_orders,
  (SELECT points FROM mybaixin_user WHERE user_id = 10011) AS user_points,
  (SELECT JSON_EXTRACT(`values`, '$.is_shopping_discount') FROM mybaixin_setting WHERE `key`='points' AND wxapp_id=10001) AS pts_discount_on;
