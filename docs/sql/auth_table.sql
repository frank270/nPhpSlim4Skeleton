-- 權限管理資料表（依照 Assistant 規劃，符合原畫布邏輯）

-- 功能控制表
CREATE TABLE `permissions_ctrl_func` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(64) NOT NULL UNIQUE COMMENT '功能代碼（供程式對照）',
    `name` VARCHAR(128) NOT NULL COMMENT '顯示名稱',
    `controller` VARCHAR(128) NOT NULL COMMENT '控制器類別（如 AuthAction）',
    `method` VARCHAR(128) NOT NULL COMMENT '方法名稱（如 login）',
    `type` ENUM('backend','frontend') DEFAULT 'backend' COMMENT '分類用途',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 角色群組表
CREATE TABLE `permissions_groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(64) NOT NULL UNIQUE COMMENT '群組代碼',
    `name` VARCHAR(128) NOT NULL COMMENT '群組名稱',
    `memo` TEXT NULL COMMENT '備註',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 權限設定表（群組×功能）
CREATE TABLE `permissions_matrix` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group_id` INT UNSIGNED NOT NULL,
    `func_id` INT UNSIGNED NOT NULL,
    `enabled` TINYINT(1) DEFAULT 1 COMMENT '是否啟用權限',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_matrix_group` FOREIGN KEY (`group_id`) REFERENCES `permissions_groups`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_matrix_func` FOREIGN KEY (`func_id`) REFERENCES `permissions_ctrl_func`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 後台使用者帳號
CREATE TABLE `admin_users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(64) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `display_name` VARCHAR(128) NULL,
    `group_id` INT UNSIGNED,
    `last_login_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_admin_group` FOREIGN KEY (`group_id`) REFERENCES `permissions_groups`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `permissions_groups` (`code`, `name`, `memo`)
VALUES ('superadmin', '超級管理員', '系統最高權限');
INSERT INTO `admin_users` (`username`, `password_hash`, `display_name`, `group_id`)
VALUES (
  'admin',
  '$2y$10$WZtLMrV1v2rbQbiHhzzuqOAKdTr2JZzKNCpZYm89X6FZuMFTQcvce',  -- 密碼為 test1234
  '系統管理員',
  1  -- group_id，對應上一步的 superadmin
);
