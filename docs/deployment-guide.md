# 部署指南

**最後更新日期:2026-02-03**

## 環境需求

### 開發環境
- Docker Desktop
- Docker Compose
- mkcert (本地 SSL 憑證)
- Git

### 生產環境
- PHP 8.3+
- Nginx 或 Apache
- MySQL 8.0+
- Redis
- Composer
- Node.js 18+ (用於前端建置)

---

## 本地開發環境設定

### 1. 安裝 mkcert

```bash
# macOS
brew install mkcert
brew install nss  # 如果使用 Firefox

# 安裝本地 CA
mkcert -install
```

### 2. 生成 SSL 憑證

```bash
# 確保 SSL 目錄存在
mkdir -p ./docker/ssl

# 生成 ndev.local 的憑證
mkcert -key-file ./docker/ssl/ndev.local.key \
       -cert-file ./docker/ssl/ndev.local.crt \
       ndev.local "*.ndev.local"

# 生成 ndba.local 的憑證
mkcert -key-file ./docker/ssl/ndba.local.key \
       -cert-file ./docker/ssl/ndba.local.crt \
       ndba.local "*.ndba.local"
```

### 3. 設定 Hosts

編輯 `/etc/hosts` 加入:

```
127.0.0.1 ndev.local
127.0.0.1 ndba.local
```

### 4. 設定環境變數

```bash
# 複製環境變數檔案
cp .env.docker.sample .env.docker

# 編輯 .env.docker
# 設定資料庫連線資訊
```

### 5. 啟動 Docker 環境

```bash
# 啟動容器
docker compose up -d

# 查看容器狀態
docker compose ps

# 查看日誌
docker compose logs -f
```

### 6. 安裝依賴套件

```bash
# 進入 PHP 容器
docker compose exec php bash

# 安裝 Composer 套件
composer install

# 執行資料庫遷移
php bin/doctrine-migrations migrations:migrate

# 退出容器
exit
```

### 7. 建置前端資源

```bash
# 進入 React 專案目錄
cd resources/react-opanel

# 安裝依賴
npm install

# 開發模式 (熱重載)
npm run dev

# 或建置生產版本
npm run build
```

### 8. 存取服務

- **網站:** https://ndev.local:8243
- **PHPMyAdmin:** https://ndba.local:8243
- **MySQL:** localhost:3306
- **Redis:** localhost:6379

---

## 生產環境部署

### 1. 伺服器準備

#### 1.1 安裝必要軟體

```bash
# 更新套件
sudo apt update && sudo apt upgrade -y

# 安裝 PHP 8.3
sudo add-apt-repository ppa:ondrej/php
sudo apt install php8.3-fpm php8.3-mysql php8.3-redis \
                 php8.3-xml php8.3-mbstring php8.3-curl \
                 php8.3-zip php8.3-gd

# 安裝 Nginx
sudo apt install nginx

# 安裝 MySQL
sudo apt install mysql-server

# 安裝 Redis
sudo apt install redis-server

# 安裝 Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### 1.2 設定 MySQL

```bash
# 執行安全設定
sudo mysql_secure_installation

# 建立資料庫與使用者
sudo mysql -u root -p
```

```sql
CREATE DATABASE breakfast_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'breakfast_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON breakfast_db.* TO 'breakfast_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. 部署程式碼

#### 2.1 Clone 專案

```bash
# 建立專案目錄
sudo mkdir -p /var/www/1fbreakfast
sudo chown -R $USER:$USER /var/www/1fbreakfast

# Clone 專案
cd /var/www/1fbreakfast
git clone <repository-url> .
```

#### 2.2 設定環境變數

```bash
# 複製環境變數檔案
cp .env.sample .env

# 編輯 .env
nano .env
```

設定內容:
```dotenv
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=pdo_mysql
DB_HOST=localhost
DB_DATABASE=breakfast_db
DB_USERNAME=breakfast_user
DB_PASSWORD=your_password

REDIS_HOST=localhost
REDIS_PORT=6379
```

#### 2.3 安裝依賴

```bash
# 安裝 Composer 套件 (不含開發套件)
composer install --no-dev --optimize-autoloader

# 執行資料庫遷移
php bin/doctrine-migrations migrations:migrate --no-interaction
```

#### 2.4 建置前端資源

```bash
# 安裝 Node.js (如果尚未安裝)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# 建置 React 後台
cd resources/react-opanel
npm install
npm run build

# 回到專案根目錄
cd ../..
```

#### 2.5 設定檔案權限

```bash
# 設定擁有者
sudo chown -R www-data:www-data /var/www/1fbreakfast

# 設定目錄權限
sudo find /var/www/1fbreakfast -type d -exec chmod 755 {} \;
sudo find /var/www/1fbreakfast -type f -exec chmod 644 {} \;

# 設定可寫入目錄
sudo chmod -R 775 /var/www/1fbreakfast/cache
sudo chmod -R 775 /var/www/1fbreakfast/logs
sudo chmod -R 775 /var/www/1fbreakfast/public/upload
```

### 3. 設定 Nginx

#### 3.1 建立 Nginx 設定檔

```bash
sudo nano /etc/nginx/sites-available/1fbreakfast
```

設定內容:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    
    # 重導向到 HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    root /var/www/1fbreakfast/public;
    index index.php index.html;

    # SSL 憑證
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    # SSL 設定
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # 上傳檔案大小限制
    client_max_body_size 128M;

    # 日誌
    access_log /var/log/nginx/1fbreakfast_access.log;
    error_log /var/log/nginx/1fbreakfast_error.log;

    # 主要路由
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP 處理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # 靜態資源快取
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # 隱藏敏感檔案
    location ~ /\. {
        deny all;
    }

    location ~ /(vendor|cache|logs|migrations)/ {
        deny all;
    }
}
```

#### 3.2 啟用網站

```bash
# 建立符號連結
sudo ln -s /etc/nginx/sites-available/1fbreakfast /etc/nginx/sites-enabled/

# 測試設定
sudo nginx -t

# 重新載入 Nginx
sudo systemctl reload nginx
```

### 4. 設定 SSL 憑證 (Let's Encrypt)

```bash
# 安裝 Certbot
sudo apt install certbot python3-certbot-nginx

# 取得憑證
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# 測試自動更新
sudo certbot renew --dry-run
```

### 5. 設定 PHP-FPM

```bash
# 編輯 PHP-FPM 設定
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

調整設定:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

編輯 PHP 設定:
```bash
sudo nano /etc/php/8.3/fpm/php.ini
```

調整設定:
```ini
upload_max_filesize = 128M
post_max_size = 128M
memory_limit = 256M
max_execution_time = 300
```

重啟 PHP-FPM:
```bash
sudo systemctl restart php8.3-fpm
```

### 6. 設定 Redis

```bash
# 編輯 Redis 設定
sudo nano /etc/redis/redis.conf
```

調整設定:
```
maxmemory 256mb
maxmemory-policy allkeys-lru
```

重啟 Redis:
```bash
sudo systemctl restart redis-server
```

---

## 部署檢查清單

### 部署前
- [ ] 程式碼已推送到 Git repository
- [ ] 環境變數已設定
- [ ] 資料庫已建立
- [ ] SSL 憑證已準備

### 部署中
- [ ] 程式碼已部署
- [ ] Composer 套件已安裝
- [ ] 資料庫遷移已執行
- [ ] 前端資源已建置
- [ ] 檔案權限已設定
- [ ] Nginx 設定已完成
- [ ] PHP-FPM 已設定
- [ ] Redis 已設定

### 部署後
- [ ] 網站可正常存取
- [ ] HTTPS 正常運作
- [ ] 後台登入正常
- [ ] 檔案上傳功能正常
- [ ] 資料庫連線正常
- [ ] Redis 快取正常
- [ ] 日誌檔案正常寫入

---

## 常見問題排解

### 1. 檔案上傳失敗

**問題:** 上傳檔案時出現 413 錯誤

**解決方案:**
```bash
# 檢查 Nginx 設定
grep client_max_body_size /etc/nginx/sites-available/1fbreakfast

# 檢查 PHP 設定
php -i | grep upload_max_filesize
php -i | grep post_max_size

# 重啟服務
sudo systemctl restart nginx php8.3-fpm
```

### 2. 資料庫連線失敗

**問題:** 無法連線到資料庫

**解決方案:**
```bash
# 檢查 MySQL 服務
sudo systemctl status mysql

# 測試連線
mysql -u breakfast_user -p breakfast_db

# 檢查 .env 設定
cat .env | grep DB_
```

### 3. 權限錯誤

**問題:** 無法寫入 cache/logs/upload 目錄

**解決方案:**
```bash
# 重新設定權限
sudo chown -R www-data:www-data /var/www/1fbreakfast
sudo chmod -R 775 /var/www/1fbreakfast/cache
sudo chmod -R 775 /var/www/1fbreakfast/logs
sudo chmod -R 775 /var/www/1fbreakfast/public/upload
```

---

## 監控與維護

### 日誌檔案位置

- **Nginx Access Log:** `/var/log/nginx/1fbreakfast_access.log`
- **Nginx Error Log:** `/var/log/nginx/1fbreakfast_error.log`
- **PHP-FPM Log:** `/var/log/php8.3-fpm.log`
- **應用程式 Log:** `/var/www/1fbreakfast/logs/`

### 定期維護

```bash
# 清理舊日誌 (每週)
find /var/www/1fbreakfast/logs -name "*.log" -mtime +30 -delete

# 清理 Twig 快取 (必要時)
rm -rf /var/www/1fbreakfast/cache/twig/*

# 更新 Composer 套件 (每月)
cd /var/www/1fbreakfast
composer update --no-dev

# 備份資料庫 (每日)
mysqldump -u breakfast_user -p breakfast_db > backup_$(date +%Y%m%d).sql
```

---

## 備份策略

### 資料庫備份

```bash
# 建立備份腳本
sudo nano /usr/local/bin/backup-db.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/1fbreakfast"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR
mysqldump -u breakfast_user -p'your_password' breakfast_db | gzip > $BACKUP_DIR/db_$DATE.sql.gz
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete
```

```bash
# 設定權限
sudo chmod +x /usr/local/bin/backup-db.sh

# 設定 Cron (每日凌晨 2 點)
sudo crontab -e
```

加入:
```
0 2 * * * /usr/local/bin/backup-db.sh
```

### 檔案備份

```bash
# 備份上傳檔案
rsync -avz /var/www/1fbreakfast/public/upload/ /var/backups/1fbreakfast/upload/
```

---

## 效能優化建議

### 1. 啟用 OPcache

```bash
sudo nano /etc/php/8.3/fpm/conf.d/10-opcache.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 2. 設定 Redis 快取

確保 `.env` 中 Redis 設定正確:
```dotenv
REDIS_HOST=localhost
REDIS_PORT=6379
```

### 3. 啟用 Gzip 壓縮

在 Nginx 設定中加入:
```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
```

---

## 安全性建議

1. **定期更新系統套件**
2. **使用強密碼**
3. **限制 SSH 存取**
4. **設定防火牆 (UFW)**
5. **定期備份**
6. **監控日誌檔案**
7. **使用 HTTPS**
8. **限制檔案上傳類型**

---

## 聯絡資訊

如有部署問題,請聯絡開發團隊。
