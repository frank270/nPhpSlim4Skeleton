-- 為 admin_users 資料表添加 status 欄位
ALTER TABLE `admin_users` ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '狀態：1=啟用，0=停用' AFTER `group_id`;

-- 更新現有使用者狀態為啟用
UPDATE `admin_users` SET `status` = 1;
