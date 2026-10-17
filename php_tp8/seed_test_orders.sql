-- 测试数据：余额准备 + 4 个待支付订单（微信/支付宝/Stripe/余额）
UPDATE yoshop_user SET balance = 200.00 WHERE user_id = 10022;

INSERT INTO yoshop_order
  (order_no, user_id, wxapp_id, order_status, pay_status, delivery_status, receipt_status,
   total_price, order_price, pay_price, express_price, buyer_remark, express_company,
   express_no, transaction_id, create_time, update_time)
VALUES
  ('TESTBAL001', 10022, 10001, 10, 10, 10, 10, 100.00, 100.00, 100.00, 0.00, '', '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  ('TESTWX002',  10022, 10001, 10, 10, 10, 10, 100.00, 100.00, 100.00, 0.00, '', '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  ('TESTALI003', 10022, 10001, 10, 10, 10, 10, 100.00, 100.00, 100.00, 0.00, '', '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  ('TESTSTR004', 10022, 10001, 10, 10, 10, 10, 100.00, 100.00, 100.00, 0.00, '', '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
