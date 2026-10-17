-- 阿里云 ChatApp（WhatsApp 验证码）记录表
-- 前缀 mybaixin_，对应代码中的 Db::name('chatapp_verify_code')
CREATE TABLE IF NOT EXISTS `mybaixin_chatapp_verify_code` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `event` varchar(30) NOT NULL DEFAULT 'register' COMMENT '事件类型:register/mobilelogin/resetpwd/changemobile',
  `phone` varchar(30) NOT NULL DEFAULT '' COMMENT '手机号(国际格式)',
  `code` varchar(10) NOT NULL DEFAULT '' COMMENT '验证码',
  `channel_type` varchar(20) NOT NULL DEFAULT 'whatsapp' COMMENT '通道类型:whatsapp/messenger/instagram/viber/telegram/line',
  `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '验证次数',
  `ip` varchar(30) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `used` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已使用:0否1是',
  `msg_id` varchar(100) NOT NULL DEFAULT '' COMMENT '阿里云消息ID',
  `result` text COMMENT '发送结果(JSON)',
  `createtime` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `phone` (`phone`),
  KEY `event` (`event`),
  KEY `createtime` (`createtime`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ChatApp验证码表';
