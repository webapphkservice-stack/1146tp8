-- 为微信登录用户补充 WhatsApp 绑定字段（与 mobile 分开存储，可同时绑定手机号与 WhatsApp）
ALTER TABLE `mybaixin_user`
  ADD COLUMN `whatsapp` varchar(40) NOT NULL DEFAULT '' COMMENT 'WhatsApp号码(国际格式)' AFTER `mobile`;

ALTER TABLE `mybaixin_user`
  ADD INDEX `whatsapp` (`whatsapp`);
