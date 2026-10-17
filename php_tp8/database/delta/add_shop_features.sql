-- 外送店铺新增「接单功能」「配送功能」开关
ALTER TABLE `mybaixin_local_delivery_shop`
  ADD COLUMN `accept_order` tinyint(1) NOT NULL DEFAULT 1 COMMENT '接单功能：1接单中 0暂不接单' AFTER `business_hours`,
  ADD COLUMN `delivery_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '配送功能：1开启配送 0仅到店自提' AFTER `accept_order`;
