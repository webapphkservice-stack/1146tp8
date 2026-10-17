-- 售后退款对账字段：补「商户退款单号 / 网关退款单号 / 退款成功时间」
-- 用途：财务对账时能通过本表直接定位到微信/支付宝的退款流水，无需再反查网关。
-- 关联逻辑：app/store/model/OrderRefund::receipt() 在 Refund::execute() 成功后写入。
-- 拼团售后表 sharing_order_refund 一并补字段（目前拼团 receipt 未接真退款，字段预留）。

ALTER TABLE `mybaixin_order_refund`
  ADD COLUMN `refund_no`   varchar(64) NOT NULL DEFAULT '' COMMENT '商户退款单号(网关幂等键 RF+售后单id)' AFTER `refund_money`,
  ADD COLUMN `refund_id`   varchar(64) NOT NULL DEFAULT '' COMMENT '网关退款单号(微信refund_id/支付宝trade_no,余额为空)' AFTER `refund_no`,
  ADD COLUMN `refund_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '退款成功时间(来自网关,余额即时)' AFTER `refund_id`;

ALTER TABLE `mybaixin_sharing_order_refund`
  ADD COLUMN `refund_no`   varchar(64) NOT NULL DEFAULT '' COMMENT '商户退款单号(网关幂等键 RF+售后单id)' AFTER `refund_money`,
  ADD COLUMN `refund_id`   varchar(64) NOT NULL DEFAULT '' COMMENT '网关退款单号(微信refund_id/支付宝trade_no,余额为空)' AFTER `refund_no`,
  ADD COLUMN `refund_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '退款成功时间(来自网关,余额即时)' AFTER `refund_id`;

-- 商户退款单号建索引，便于按退款单号对账检索
ALTER TABLE `mybaixin_order_refund` ADD INDEX `idx_refund_no` (`refund_no`);
ALTER TABLE `mybaixin_sharing_order_refund` ADD INDEX `idx_refund_no` (`refund_no`);
