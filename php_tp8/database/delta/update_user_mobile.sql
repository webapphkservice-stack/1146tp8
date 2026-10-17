-- 为手机号登录补充 user 表 mobile 字段（P0 登录闭环所需）
ALTER TABLE `mybaixin_user`
  ADD COLUMN `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号' AFTER `open_id`;

ALTER TABLE `mybaixin_user`
  ADD INDEX `mobile` (`mobile`);
