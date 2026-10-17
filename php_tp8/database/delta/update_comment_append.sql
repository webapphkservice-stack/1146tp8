-- 评价表新增「追评」字段（用于买家侧追评功能）
-- 执行方式：在 yoshop 数据库中执行本文件
ALTER TABLE `mybaixin_comment`
  ADD COLUMN `append_content` text NOT NULL COMMENT '追评内容' AFTER `content`,
  ADD COLUMN `append_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '追评时间' AFTER `append_content`;
