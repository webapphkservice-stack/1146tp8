-- 本地外送：外送商品归属店铺
-- 将「外送商品」调整为「店铺的功能」：每条外送商品归属到 mybaixin_local_delivery_shop 中的某个店铺
ALTER TABLE `mybaixin_local_delivery`
  ADD COLUMN `shop_id` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属店铺ID（mybaixin_local_delivery_shop.shop_id），0 表示未归属'
  AFTER `delivery_id`;

ALTER TABLE `mybaixin_local_delivery`
  ADD KEY `shop_id` (`shop_id`);
