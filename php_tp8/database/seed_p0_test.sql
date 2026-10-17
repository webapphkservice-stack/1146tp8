-- P0 端到端测试种子数据（store 发货 / task 定时维护）
-- 幂等：可重复执行（先清理本次测试订单）
-- 用法：mysql -u root yoshop_tp8 < database/seed_p0_test.sql

SET NAMES utf8mb4;

-- 清理历史测试数据
-- 注意：必须先删 order_goods 再删 order，否则重跑脚本时 order_goods 主键会冲突，
-- 导致脚本中断、后续订单未插入（曾因此造成本测试大面积失败）。
DELETE FROM mybaixin_order_goods WHERE order_id BETWEEN 900000 AND 900099;
DELETE FROM mybaixin_order WHERE order_no LIKE 'P0TEST%';
DELETE FROM mybaixin_dealer_order WHERE order_id >= 900000;
DELETE FROM mybaixin_dealer_capital WHERE `describe` LIKE '订单佣金：P0TEST%';
DELETE FROM mybaixin_store_user WHERE user_name = 'p0admin';

-- 0. Stripe 配置（供 e2e_p0_payment_test.py 验证原生 Webhook 验签）
--    密钥为测试占位值，真实环境在后台「Stripe 配置」中填写
DELETE FROM mybaixin_setting WHERE `key` = 'stripe' AND wxapp_id = 10001;
INSERT INTO mybaixin_setting (`key`, `describe`, `values`, wxapp_id, update_time)
VALUES ('stripe', 'Stripe配置',
        '{"secret_key":"sk_test_p0","publishable_key":"pk_test_p0","webhook_secret":"whsec_p0test123","currency":"myr","merchant_name":"P0Shop"}',
        10001, UNIX_TIMESTAMP());

-- 1. 商家后台账号（密码 123456）
-- 哈希值 = md5(md5('123456') . 'mybaixin_salt_SmTRx')，与 php_my 的 mybaixin_hash() 一致
-- 注意：MySQL 9.x 已移除 MD5() 内建函数，故此处使用 PHP 预计算值
INSERT INTO mybaixin_store_user
  (store_user_id, user_name, password, real_name, is_super, is_delete, wxapp_id, create_time, update_time)
VALUES
  (90001, 'p0admin', '9c4cb25665cf08667c815420ab383cb5', 'P0测试管理员', 1, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 2. 订单 A：已付款、待发货（用于验证 store 发货）
INSERT INTO mybaixin_order
  (order_id, order_no, total_price, order_price, coupon_id, coupon_money, points_money, points_num,
   pay_price, update_price, buyer_remark, pay_type, pay_status, pay_time, delivery_type, goods_type,
   express_price, express_id, express_company, delivery_id, express_no,
   delivery_status, delivery_time, receipt_status, receipt_time, order_status,
   points_bonus, is_settled, transaction_id, is_comment, order_source, order_source_id,
   user_id, is_delete, wxapp_id, create_time, update_time, is_vip)
VALUES
  (900001, 'P0TEST0000000001', 200.00, 200.00, 0, 0.00, 0.00, 0,
   200.00, 0.00, '', 20, 20, UNIX_TIMESTAMP(), 10, 10,
   0.00, 0, '', 0, '',
   10, 0, 10, 0, 10,
   0, 0, 'wxP0TEST001', 0, 10, 0,
   10010, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

INSERT INTO mybaixin_order_goods
  (order_goods_id, order_id, user_id, goods_id, goods_name, image_id, deduct_stock_type, spec_type, spec_sku_id,
   goods_sku_id, goods_attr, content, goods_no, goods_price, line_price, goods_weight,
   total_num, total_price, total_pay_price, is_comment, points_num, wxapp_id, create_time)
VALUES
  (900001, 900001, 10010, 10001, '黑色女装', 0, 10, 10, '0',
   10001, '', '', '', 200.00, 300.00, 0,
   1, 200.00, 200.00, 0, 0, 10001, UNIX_TIMESTAMP());

-- 3. 订单 B：已发货 20 天（超过 receive_days=10，用于验证自动确认收货）
INSERT INTO mybaixin_order
  (order_id, order_no, total_price, order_price, coupon_id, coupon_money, points_money, points_num,
   pay_price, update_price, buyer_remark, pay_type, pay_status, pay_time, delivery_type, goods_type,
   express_price, express_id, express_company, delivery_id, express_no,
   delivery_status, delivery_time, receipt_status, receipt_time, order_status,
   points_bonus, is_settled, transaction_id, is_comment, order_source, order_source_id,
   user_id, is_delete, wxapp_id, create_time, update_time, is_vip)
VALUES
  (900002, 'P0TEST0000000002', 150.00, 150.00, 0, 0.00, 0.00, 0,
   150.00, 0.00, '', 20, 20, UNIX_TIMESTAMP() - 20*86400, 10, 10,
   0.00, 10001, '顺丰速运', 0, 'SF_P0TEST_002',
   20, UNIX_TIMESTAMP() - 20*86400, 10, 0, 10,
   0, 0, 'wxP0TEST002', 0, 10, 0,
   10010, 0, 10001, UNIX_TIMESTAMP() - 20*86400, UNIX_TIMESTAMP(), 0);

-- 4. 订单 C：未支付、5 天前创建（超过 close_days=3，用于验证自动关闭）
INSERT INTO mybaixin_order
  (order_id, order_no, total_price, order_price, coupon_id, coupon_money, points_money, points_num,
   pay_price, update_price, buyer_remark, pay_type, pay_status, pay_time, delivery_type, goods_type,
   express_price, express_id, express_company, delivery_id, express_no,
   delivery_status, delivery_time, receipt_status, receipt_time, order_status,
   points_bonus, is_settled, transaction_id, is_comment, order_source, order_source_id,
   user_id, is_delete, wxapp_id, create_time, update_time, is_vip)
VALUES
  (900003, 'P0TEST0000000003', 100.00, 100.00, 0, 0.00, 0.00, 0,
   100.00, 0.00, '', 20, 10, 0, 10, 10,
   0.00, 0, '', 0, '',
   10, 0, 10, 0, 10,
   0, 0, '', 0, 10, 0,
   10010, 0, 10001, UNIX_TIMESTAMP() - 5*86400, UNIX_TIMESTAMP() - 5*86400, 0);

INSERT INTO mybaixin_order_goods
  (order_goods_id, order_id, user_id, goods_id, goods_name, image_id, deduct_stock_type, spec_type, spec_sku_id,
   goods_sku_id, goods_attr, content, goods_no, goods_price, line_price, goods_weight,
   total_num, total_price, total_pay_price, is_comment, points_num, wxapp_id, create_time)
VALUES
  (900003, 900003, 10010, 10001, '黑色女装', 0, 10, 10, '0',
   10001, '', '', '', 100.00, 100.00, 0,
   2, 200.00, 200.00, 0, 0, 10001, UNIX_TIMESTAMP() - 5*86400);

-- 5. 订单 D：已完成 10 天前（超过 refund_days=7，未结算，用于验证分销佣金结算）
INSERT INTO mybaixin_order
  (order_id, order_no, total_price, order_price, coupon_id, coupon_money, points_money, points_num,
   pay_price, update_price, buyer_remark, pay_type, pay_status, pay_time, delivery_type, goods_type,
   express_price, express_id, express_company, delivery_id, express_no,
   delivery_status, delivery_time, receipt_status, receipt_time, order_status,
   points_bonus, is_settled, transaction_id, is_comment, order_source, order_source_id,
   user_id, is_delete, wxapp_id, create_time, update_time, is_vip)
VALUES
  (900004, 'P0TEST0000000004', 300.00, 300.00, 0, 0.00, 0.00, 0,
   300.00, 0.00, '', 20, 20, UNIX_TIMESTAMP() - 12*86400, 10, 10,
   0.00, 10001, '顺丰速运', 0, 'SF_P0TEST_004',
   20, UNIX_TIMESTAMP() - 11*86400, 20, UNIX_TIMESTAMP() - 10*86400, 30,
   0, 0, 'wxP0TEST004', 0, 10, 0,
   10010, 0, 10001, UNIX_TIMESTAMP() - 12*86400, UNIX_TIMESTAMP(), 0);

-- 订单 D 对应的分销订单（佣金：一级 30，二级 10）
INSERT INTO mybaixin_dealer_order
  (id, user_id, order_id, order_type, order_no, order_price,
   first_user_id, second_user_id, third_user_id,
   first_money, second_money, third_money,
   is_invalid, is_settled, settle_time, wxapp_id, create_time, update_time)
VALUES
  (900001, 10010, 900004, 10, 'P0TEST0000000004', 300.00,
   10011, 10012, 0,
   30.00, 10.00, 0.00,
   0, 0, 0, 10001, UNIX_TIMESTAMP() - 12*86400, UNIX_TIMESTAMP());

-- 确保分销商记录存在（用于累加佣金）
INSERT IGNORE INTO mybaixin_dealer_user
  (user_id, real_name, mobile, money, freeze_money, total_money, referee_id,
   first_num, second_num, third_num, is_delete, wxapp_id, create_time, update_time)
VALUES
  (10011, '一级分销商', '', 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (10012, '二级分销商', '', 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 6. 订单 E：未支付（用于 e2e_p0_payment_test.py 验证 Stripe Webhook 回调改单）
INSERT INTO mybaixin_order
  (order_id, order_no, total_price, order_price, coupon_id, coupon_money, points_money, points_num,
   pay_price, update_price, buyer_remark, pay_type, pay_status, pay_time, delivery_type, goods_type,
   express_price, express_id, express_company, delivery_id, express_no,
   delivery_status, delivery_time, receipt_status, receipt_time, order_status,
   points_bonus, is_settled, transaction_id, is_comment, order_source, order_source_id,
   user_id, is_delete, wxapp_id, create_time, update_time, is_vip)
VALUES
  (900009, 'P0TEST0000000009', 88.00, 88.00, 0, 0.00, 0.00, 0,
   88.00, 0.00, '', 50, 10, 0, 10, 10,
   0.00, 0, '', 0, '',
   10, 0, 10, 0, 10,
   0, 0, '', 0, 10, 0,
   10010, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

-- 7. 订单 F/G/H：未支付，分别用于验证微信(20)/支付宝(40)/Stripe(50) 下单
--    user_id=10011 与 seed_p0_session.php 注入的登录态一致
INSERT INTO mybaixin_order
  (order_id, order_no, total_price, order_price, coupon_id, coupon_money, points_money, points_num,
   pay_price, update_price, buyer_remark, pay_type, pay_status, pay_time, delivery_type, goods_type,
   express_price, express_id, express_company, delivery_id, express_no,
   delivery_status, delivery_time, receipt_status, receipt_time, order_status,
   points_bonus, is_settled, transaction_id, is_comment, order_source, order_source_id,
   user_id, is_delete, wxapp_id, create_time, update_time, is_vip)
VALUES
  (900010, 'P0TEST0000000010', 50.00, 50.00, 0, 0.00, 0.00, 0,
   50.00, 0.00, '', 20, 10, 0, 10, 10,
   0.00, 0, '', 0, '',
   10, 0, 10, 0, 10,
   0, 0, '', 0, 10, 0,
   10011, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0),
  (900011, 'P0TEST0000000011', 50.00, 50.00, 0, 0.00, 0.00, 0,
   50.00, 0.00, '', 40, 10, 0, 10, 10,
   0.00, 0, '', 0, '',
   10, 0, 10, 0, 10,
   0, 0, '', 0, 10, 0,
   10011, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0),
  (900012, 'P0TEST0000000012', 50.00, 50.00, 0, 0.00, 0.00, 0,
   50.00, 0.00, '', 50, 10, 0, 10, 10,
   0.00, 0, '', 0, '',
   10, 0, 10, 0, 10,
   0, 0, '', 0, 10, 0,
   10011, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

-- 输出校验信息
SELECT 'seed ok' AS result,
       (SELECT COUNT(*) FROM mybaixin_order WHERE order_no LIKE 'P0TEST%') AS test_orders,
       (SELECT COUNT(*) FROM mybaixin_store_user WHERE user_name = 'p0admin') AS store_user;
