-- 本地外送 - 店铺管理表
-- 用于商户后台「本地外送 / 店铺管理」模块：店铺可设置外送商品、店名、店铺地址、WhatsApp 等
CREATE TABLE IF NOT EXISTS `mybaixin_local_delivery_shop` (
  `shop_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `shop_name` varchar(255) NOT NULL DEFAULT '' COMMENT '店名',
  `address` varchar(500) NOT NULL DEFAULT '' COMMENT '店铺地址',
  `whatsapp` varchar(60) NOT NULL DEFAULT '' COMMENT '店铺 WhatsApp（含国家区号）',
  `description` text COMMENT '外送商品说明（该店铺可提供的外送商品/经营范围）',
  `image_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '店铺图片 file_id',
  `sort` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '排序（小到大）',
  `status` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `wxapp_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '小程序ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`shop_id`),
  KEY `wxapp_id` (`wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='本地外送-店铺管理';
