-- 为小程序记录表新增「微信小程序URL地址」字段，用于 PC 顶部导航「微信」入口跳转 weixin:// 协议打开小程序
ALTER TABLE `mybaixin_wxapp`
  ADD COLUMN `weixin_url` varchar(500) NOT NULL DEFAULT '' COMMENT '微信小程序URL地址(weixin://协议)' AFTER `app_secret`;

-- 预置当前小程序（wxapp_id=10001）的跳转地址
UPDATE `mybaixin_wxapp`
  SET `weixin_url` = 'weixin://dl/business/?appid=wx18376fccf5d27b73&path=pages/index/index&env_version=release'
  WHERE `wxapp_id` = 10001;