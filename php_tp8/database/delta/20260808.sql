-- 2026-08-08 海外用户 Google 登录：mybaixin_user 新增 google_id 字段（唯一索引）
-- 说明：整站部署于吉隆坡，可直连 Google 验签。
-- google_id 使用 NULL 默认值：MySQL 唯一索引允许多个 NULL，但每个非空的 google_id 必须唯一，
-- 因此已注册但非 Google 登录的老用户（google_id 为 NULL）不会与任何 Google 账号冲突。

ALTER TABLE `mybaixin_user`
    ADD COLUMN `google_id` VARCHAR(255) NULL DEFAULT NULL
        COMMENT 'Google 用户唯一标识（id_token 中的 sub）' AFTER `alipay_user_id`,
    ADD UNIQUE KEY `UNIQ_google_id` (`google_id`);
