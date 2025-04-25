-- 為 admin_users 資料表新增 deleted_at 欄位以支援軟刪除功能
ALTER TABLE admin_users ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL COMMENT '軟刪除時間戳記';

-- 為 deleted_at 欄位添加索引以提升查詢效能
ALTER TABLE admin_users ADD INDEX idx_admin_users_deleted_at (deleted_at);
