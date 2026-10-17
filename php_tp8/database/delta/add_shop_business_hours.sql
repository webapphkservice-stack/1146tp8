-- 本地外送 - 店铺新增「营业时间」字段
-- 用于后台「本地外送 / 店铺管理」填写营业时间，并在小程序端展示
ALTER TABLE `mybaixin_local_delivery_shop`
  ADD COLUMN `business_hours` varchar(500) NOT NULL DEFAULT '' COMMENT '营业时间（如：每日 10:00-22:00）' AFTER `whatsapp`;
