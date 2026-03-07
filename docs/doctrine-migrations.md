# Doctrine Migrations 使用說明

## 1. 環境需求
- 已安裝 `doctrine/migrations` 套件（已加入 `composer.json`）。
- 需啟動 Docker 開發環境，使 MySQL 服務可供連線。
  ```bash
  docker compose up -d
  ```
- `.env` 內需填寫資料庫連線資訊（`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`）。

## 2. 設定檔位置
- `migrations.php`：Doctrine Migrations 主要設定（版本儲存表、遷移檔路徑）。
- `migrations-db.php`：資料庫連線設定，使用 `.env` 內容載入。
- 遷移檔案目錄：`/migrations`，命名空間為 `App\Migrations`。

## 3. 常用指令（需在容器內執行）
以下範例以 `app` 容器為主，確保使用與應用程式一致的環境與主機名稱。

### 3.1 查看狀態
```bash
docker compose exec app php vendor/bin/doctrine-migrations status \
  --configuration=/var/www/migrations.php \
  --db-configuration=/var/www/migrations-db.php
```

### 3.2 建立新遷移檔
```bash
docker compose exec app php vendor/bin/doctrine-migrations generate \
  --configuration=/var/www/migrations.php
```
遷移檔將建立於 `/migrations` 目錄，請依需求撰寫 `up` / `down` 方法。

### 3.3 執行遷移
```bash
docker compose exec app php vendor/bin/doctrine-migrations migrate \
  --configuration=/var/www/migrations.php \
  --db-configuration=/var/www/migrations-db.php
```

### 3.4 復原指定版本（可選）
```bash
docker compose exec app php vendor/bin/doctrine-migrations execute <version> \
  --down --configuration=/var/www/migrations.php \
  --db-configuration=/var/www/migrations-db.php
```

## 4. 開發流程建議
1. 修改資料表前，先生成遷移檔並撰寫內容。
2. 本機測試遷移成功後，再提交程式碼與遷移檔。
3. 部署環境請同步執行 `migrate` 指令，確保資料庫與程式一致。

## 5. 注意事項
- 確認 Docker 服務已啟動，否則 `mysql` host 將無法解析。
- 若在非容器環境執行指令，請調整 `--db-configuration` 內的連線主機名稱。
- 建議在 `doctrine_migration_versions` 表做備份或監控，避免遷移資訊遺失。
