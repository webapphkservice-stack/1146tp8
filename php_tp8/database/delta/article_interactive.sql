-- 文章互动功能：点赞、评论、收藏
-- 在 yoshop 数据库中执行（前缀 mybaixin_）
-- 执行方式：mysql -u 用户名 -p 1146_mybaixin_co < sql/article_interactive.sql

CREATE TABLE IF NOT EXISTS `mybaixin_article_like` (
  `like_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '文章ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID（未登录为0）',
  `client_id` varchar(40) NOT NULL DEFAULT '' COMMENT '客户端标识（Cookie）',
  `wxapp_id` int(11) unsigned NOT NULL DEFAULT '0',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`like_id`),
  UNIQUE KEY `article_client` (`article_id`, `client_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章点赞';

CREATE TABLE IF NOT EXISTS `mybaixin_article_favorite` (
  `favorite_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '文章ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID（未登录为0）',
  `client_id` varchar(40) NOT NULL DEFAULT '' COMMENT '客户端标识（Cookie）',
  `wxapp_id` int(11) unsigned NOT NULL DEFAULT '0',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`favorite_id`),
  UNIQUE KEY `article_client` (`article_id`, `client_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章收藏';

CREATE TABLE IF NOT EXISTS `mybaixin_article_comment` (
  `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '文章ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID（未登录为0）',
  `client_id` varchar(40) NOT NULL DEFAULT '' COMMENT '客户端标识（Cookie）',
  `nickname` varchar(30) NOT NULL DEFAULT '游客' COMMENT '昵称',
  `content` text NOT NULL COMMENT '评论内容',
  `like_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论点赞数',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1显示 0隐藏',
  `wxapp_id` int(11) unsigned NOT NULL DEFAULT '0',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章评论';
