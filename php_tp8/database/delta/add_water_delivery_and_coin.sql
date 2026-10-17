-- ============================================================
-- 送水（门店配送）+ 代币（微信虚拟支付 currency 模式）结构变更
-- 来源：合并 php/source/database/upgrade_shop_water_delivery.sql
--       与 php/source/database/upgrade_virtual_coin.sql
-- 适配 TP8：表前缀改为 mybaixin_（见 php_tp8/config/database.php）
-- 幂等：可重复执行，列/表已存在则跳过
-- ============================================================

DROP PROCEDURE IF EXISTS add_column_if_missing;
DELIMITER $$
CREATE PROCEDURE add_column_if_missing(
    IN p_table  VARCHAR(64),
    IN p_column VARCHAR(64),
    IN p_def    VARCHAR(512)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = p_table
          AND COLUMN_NAME  = p_column
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table, '` ADD COLUMN `', p_column, '` ', p_def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- ① 门店(store_shop)增加送水配送能力配置
CALL add_column_if_missing('mybaixin_store_shop', 'delivery_enabled',
    "TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否支持门店配送(送水) 0否 1是'");
CALL add_column_if_missing('mybaixin_store_shop', 'delivery_fee',
    "DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '门店配送费'");
CALL add_column_if_missing('mybaixin_store_shop', 'min_order_amount',
    "DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '起送金额(0不限制)'");
CALL add_column_if_missing('mybaixin_store_shop', 'free_delivery_amount',
    "DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '满额免配送费门槛(0不减免)'");
CALL add_column_if_missing('mybaixin_store_shop', 'deposit_enabled',
    "TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否收取押桶押金 0否 1是'");
CALL add_column_if_missing('mybaixin_store_shop', 'deposit_amount',
    "DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '押桶押金金额(单桶)'");
CALL add_column_if_missing('mybaixin_store_shop', 'water_ticket_enabled',
    "TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否支持水票 0否 1是'");
CALL add_column_if_missing('mybaixin_store_shop', 'empty_bucket_recycle',
    "TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否支持空桶回收 0否 1是'");
CALL add_column_if_missing('mybaixin_store_shop', 'delivery_time_range',
    "VARCHAR(100) NOT NULL DEFAULT '' COMMENT '配送时段(如 09:00-18:00)'");

-- ② 订单(order)增加门店派送相关字段
CALL add_column_if_missing('mybaixin_order', 'delivery_shop_id',
    "INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '派送门店id(门店配送)'");
CALL add_column_if_missing('mybaixin_order', 'delivery_shop_name',
    "VARCHAR(100) NOT NULL DEFAULT '' COMMENT '派送门店名称(快照)'");
CALL add_column_if_missing('mybaixin_order', 'shop_order_status',
    "TINYINT(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '门店订单状态 10待接单 20配送中 30已完成 40已取消'");
CALL add_column_if_missing('mybaixin_order', 'deposit_amount',
    "DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '押桶押金总额'");
CALL add_column_if_missing('mybaixin_order', 'empty_bucket_num',
    "INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '空桶回收数量'");
CALL add_column_if_missing('mybaixin_order', 'deposit_refund_status',
    "TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '押桶押金退还状态 0未退 1已退'");
CALL add_column_if_missing('mybaixin_order', 'deposit_refund_time',
    "INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '押桶押金退还时间'");
CALL add_column_if_missing('mybaixin_order', 'delivery_clerk_id',
    "INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '配送员(店员)id'");
CALL add_column_if_missing('mybaixin_order', 'water_ticket_num',
    "INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '本单使用水票数'");
CALL add_column_if_missing('mybaixin_order', 'water_ticket_money',
    "DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '本单水票抵扣金额'");

-- ③ 店员增加配送员标记（配送员由店员兼任）
CALL add_column_if_missing('mybaixin_store_shop_clerk', 'is_deliveryer',
    "TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否配送员 0否 1是'");

-- ④ 商品 / 订单商品快照：按桶计押金标记
CALL add_column_if_missing('mybaixin_goods', 'is_deposit_payable',
    "TINYINT(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否按桶计押金(门店配送) 0否 1是'");
CALL add_column_if_missing('mybaixin_order_goods', 'is_deposit_payable',
    "TINYINT(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '下单时该商品是否按桶计押金 0否 1是'");

-- ⑤ 用户表增加代币余额（虚拟支付 currency 模式，区别于普通 balance）
CALL add_column_if_missing('mybaixin_user', 'coin',
    "DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '代币余额（微信虚拟支付 currency 模式）'");

DROP PROCEDURE IF EXISTS add_column_if_missing;

-- ⑥ 门店可配送区域表（region_id=0 表示该城市全辖区可配送）
CREATE TABLE IF NOT EXISTS `mybaixin_shop_delivery_area` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `shop_id`     INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '门店id → store_shop.shop_id',
    `province_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '省',
    `city_id`     INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '市',
    `region_id`   INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '区/县，0=全市',
    `wxapp_id`    INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序ID',
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0,
    `update_time` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_shop` (`shop_id`),
    KEY `idx_area` (`province_id`, `city_id`, `region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='门店可配送区域(送水配送范围)';

-- ⑦ 用户水票账户（1票可抵扣1桶按桶计押金的水）
CREATE TABLE IF NOT EXISTS `mybaixin_user_water_ticket` (
    `user_id`          INT UNSIGNED NOT NULL COMMENT '用户ID → user.user_id',
    `water_ticket_num` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '当前可用水票数',
    `wxapp_id`         INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序ID',
    `update_time`      INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户水票账户(送水)';

-- ⑧ 用户水票流水（scene: 10下单抵扣 20后台发放 30后台扣减 40取消退回）
CREATE TABLE IF NOT EXISTS `mybaixin_user_water_ticket_log` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ID → user.user_id',
    `wxapp_id`    INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '小程序ID',
    `scene`       TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '变动场景 10下单抵扣 20后台发放 30后台扣减 40取消退回',
    `num`         INT NOT NULL DEFAULT 0 COMMENT '变动数量(正增负减)',
    `balance`     INT NOT NULL DEFAULT 0 COMMENT '变动后余额',
    `describe`    VARCHAR(255) NOT NULL DEFAULT '' COMMENT '变动说明',
    `create_time` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_wxapp` (`wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户水票流水(送水)';

-- ⑨ 代币充值订单（虚拟支付 currency 模式，回调后累加 user.coin）
CREATE TABLE IF NOT EXISTS `mybaixin_coin_order` (
    `order_id`       INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_no`       VARCHAR(40) NOT NULL DEFAULT '' COMMENT '商户订单号，前缀 CO',
    `user_id`        INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `plan_id`        INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联 recharge_plan',
    `coin_num`       DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '充值到账代币数量',
    `money`          DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '实际支付金额(RMB)',
    `pay_status`     TINYINT(3) UNSIGNED NOT NULL DEFAULT 10 COMMENT '10未支付 20已支付',
    `pay_type`       TINYINT(3) UNSIGNED NOT NULL DEFAULT 60 COMMENT '60虚拟支付',
    `transaction_id` VARCHAR(64) NOT NULL DEFAULT '',
    `wxapp_id`       INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `create_time`    INT(11) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`order_id`),
    UNIQUE KEY `order_no` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='代币充值订单（虚拟支付 currency 模式）';
