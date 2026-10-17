-- 出租车预约订单表
-- 说明：程序内已内置 CREATE TABLE IF NOT EXISTS 自动建表，此文件用于手工执行/审计
CREATE TABLE IF NOT EXISTS `mybaixin_taxi_order` (
  `order_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `order_no` varchar(30) NOT NULL DEFAULT '' COMMENT '订单编号',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `airport_name` varchar(120) NOT NULL DEFAULT '' COMMENT '抵达机场',
  `car_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '车型：0普通 1商务',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '出租车价格',
  `currency` varchar(10) NOT NULL DEFAULT 'RM' COMMENT '货币符号',
  `flight_no` varchar(30) NOT NULL DEFAULT '' COMMENT '航班号',
  `departure_time` varchar(30) NOT NULL DEFAULT '' COMMENT '出发时间',
  `takeoff_time` varchar(30) NOT NULL DEFAULT '' COMMENT '起飞时间',
  `landing_time` varchar(30) NOT NULL DEFAULT '' COMMENT '降落时间',
  `hotel_name` varchar(120) NOT NULL DEFAULT '' COMMENT '酒店名称',
  `hotel_address` varchar(255) NOT NULL DEFAULT '' COMMENT '酒店地址',
  `contact_name` varchar(50) NOT NULL DEFAULT '' COMMENT '联系人',
  `contact_phone` varchar(30) NOT NULL DEFAULT '' COMMENT '联系电话',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0待接单 1已接单 2已完成 3已取消',
  `wxapp_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '小程序ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `wxapp_id` (`wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='出租车预约订单表';
