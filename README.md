# Slim 4 Skeleton with Twig & Doctrine DBAL

A lightweight Slim 4 project skeleton with Twig templating, PHP-DI container, Doctrine DBAL, and Dotenv support.

**Perfect for building fast, clean, and flexible PHP applications.**

---

## Features

- 🛠️ Slim 4 (Minimal and fast)
- 🎨 Twig 3 Template Engine
- 📦 PHP-DI (Dependency Injection Container)
- 🗄️ Doctrine DBAL (Database Abstraction Layer)
- 🔐 Dotenv (.env configuration support)
- 🛎️ Slim Flash Message (Session flash message)
- 📝 Monolog (Logging)
- 🎯 PSR-7 HTTP Messages (via slim/psr7)

---

## Installation

```bash
composer create-project youraccount/your-slim4-skeleton project-name
```

Or clone the repository:

```bash
git clone https://github.com/youraccount/your-slim4-skeleton.git
cd your-slim4-skeleton
composer install
```

---

## Project Structure

```
app/
  Actions/      → 控制器類別，處理 HTTP 請求
    BaseAction.php      → 基礎控制器類別
    HomeAction.php      → 首頁控制器
    PostDemoAction.php  → 文章示範控制器
    Opanel/             → 後台管理相關控制器
  Middleware/    → 中介層類別
    AdminLogMiddleware.php → 後台操作日誌中介層
    PermissionMiddleware.php → 權限檢查中介層
  Models/        → 資料模型類別
    AdminUsersModel.php → 後台使用者模型
    PostModel.php       → 文章模型
  Routes/        → 路由定義檔案
    home.php            → 首頁路由
    opanel_access.php   → 後台權限管理路由
    opanel_auth.php     → 後台認證路由
    opanel_dashboard.php → 後台儀表板路由
    opanel_users.php    → 後台使用者管理路由
    posts.php           → 文章路由
  Templates/     → Twig 模板檔案
    hello.twig          → 歡迎頁模板
    landing.twig        → 首頁模板
    posts.twig          → 文章列表模板
    opanel/             → 後台模板目錄
  Utils/         → 工具類別
    CustomJsonFormatter.php → JSON 格式化工具
    GptUtils.php        → GPT 相關工具
    LogUtil.php         → 日誌工具
    PermissionChecker.php → 權限檢查工具
  database.php   → 資料庫連線設定 (Doctrine DBAL)
  dependencies.php → 容器依賴設定
  middleware.php → 全域中介層設定
  routes.php     → 路由聚合
  settings.php   → 應用程式設定

cache/
  twig/         → Twig 模板快取

docker/
  mysql/        → MySQL 相關配置
    conf.d/     → MySQL 自定義配置
    init.sql    → 初始化資料庫腳本
  nginx/        → Nginx 相關配置
    app.conf    → 應用程式 Nginx 配置
    phpmyadmin.conf → PHPMyAdmin Nginx 配置
    logs/       → Nginx 日誌目錄
  php/          → PHP 相關配置
    php.ini     → PHP 自定義配置
  phpmyadmin/   → PHPMyAdmin 安裝目錄
  ssl/          → SSL 憑證目錄
  promtail-config.yml → Promtail 日誌收集配置

docs/
  sql/          → SQL 資料庫結構檔案
    add_deleted_at_to_admin_users.sql → 使用者軟刪除欄位
    add_status_to_admin_users.sql     → 使用者狀態欄位
    auth_table.sql                    → 權限管理相關資料表

logs/            → 應用程式日誌目錄
  access.log     → 訪問日誌
  admin_operation-*.log → 後台操作日誌 (依日期分割)
  app.log        → 應用程式日誌
  error.log      → 錯誤日誌
  nginx_*.log    → Nginx 相關日誌
  php-error.log  → PHP 錯誤日誌

public/
  index.php     → 應用程式入口點
  assets/       → 前端靜態資源
  js/           → JavaScript 檔案
  tabler-dev/   → Tabler UI 套件

resources/       → 前端資源原始檔
  react-opanel/ → React 後台應用
  tabler/       → Tabler UI 原始檔

vendor/         → Composer 依賴套件
```

---

## Environment Configuration

Copy `.env.example` and create your own `.env` file:

```bash
cp .env.example .env
```

Set your environment variables:

```dotenv
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=pdo_mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

---

## Docker 開發環境

本專案支援 Docker 開發環境，包含 PHP 8.3、Nginx、MySQL、PHPMyAdmin 和 Redis。

### 前置需求

- Docker
- Docker Compose
- mkcert (用於生成本地信任的 SSL 憑證)
- 在本機 hosts 檔案中加入以下設定：
  ```
  127.0.0.1 ndev.local
  127.0.0.1 ndba.local
  ```

### 安裝 mkcert

```bash
# macOS 使用 Homebrew 安裝
brew install mkcert
brew install nss  # 如果使用 Firefox

# 安裝本地 CA
mkcert -install
```

### 生成 SSL 憑證

```bash
# 確保 SSL 目錄存在
mkdir -p ./docker/ssl

# 生成 ndev.local 的憑證
mkcert -key-file ./docker/ssl/ndev.local.key -cert-file ./docker/ssl/ndev.local.crt ndev.local "*.ndev.local"

# 生成 ndba.local 的憑證
mkcert -key-file ./docker/ssl/ndba.local.key -cert-file ./docker/ssl/ndba.local.crt ndba.local "*.ndba.local"
```

### 設定環境變數

```bash
# 複製環境變數檔案
cp .env.example .env
```

編輯 `.env` 檔案，設定資料庫連線資訊：

```dotenv
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=pdo_mysql
DB_HOST=mysql
DB_DATABASE=demo_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

REDIS_HOST=redis
REDIS_PORT=6379
```

### 啟動 Docker 環境

```bash
# 啟動 Docker 容器
docker compose up -d
```

### 存取服務

- 網站：https://ndev.local:8243
- PHPMyAdmin：https://ndba.local:8243 (使用本地下載的 PHPMyAdmin)
- MySQL：
  - 主機：mysql
  - 連接埠：3306
  - 資料庫：demo_db
  - 使用者：如 .env 檔案中設定
- Redis：
  - 主機：redis
  - 連接埠：6379

### 資料庫字元編碼設定

本專案已設定 MySQL 使用 utf8mb4 字元編碼和 utf8mb4_general_ci 排序規則，確保正確處理中文和其他多字節字元。相關設定在：

- `docker-compose.yml` 中的 MySQL 服務環境變數
- `docker/mysql/conf.d/charset.cnf` 中的 MySQL 配置
- `docker/mysql/init.sql` 中的資料表定義

### 停止和重置 Docker 環境

```bash
# 停止容器
docker compose down

# 停止容器並移除卷存儲 (重置資料庫和 Redis 資料)
docker compose down -v
```

---

## Basic Usage

### Define a Route

In `app/routes.php`:

```php
$app->get('/hello/{name}', App\HomeAction::class);
```

### Create an Action

In `app/src/HomeAction.php`:

```php
public function __invoke(Request $request, Response $response, array $args): Response
{
    $name = $args['name'] ?? 'Guest';
    return $this->view->render($response, 'hello.twig', ['name' => $name]);
}
```

### Create a Twig Template

In `app/templates/hello.twig`:

```twig
<h1>Hello, {{ name }}!</h1>
```

---

## Requirements

- PHP 8.0+
- Composer
- Web Server (Apache / Nginx)

---

## License

This project is open-sourced under the [MIT license](LICENSE).

---

## 更新新聞

### 2025-04-25
- 新增後台使用者管理功能，包含列表、新增、編輯、重設密碼、啟用/停用、刪除等操作
- 使用 React 18 + Vite 實作前端介面，支援 API 模式與傳統表單提交
- 後台操作日誌自動記錄為標準 JSON 格式，符合 SIEM 規範
- 整合 Loki+Grafana 日誌監控，確保日誌保留 90 天
- 後台選單整合，新增使用者管理選項
- UI/UX 改進：採用 Tabler UI 樣式，統一 Toast 通知，操作按鈕均有 SVG 圖標與提示

### 2025-04-21
- 新增 Docker 開發環境支援，包含 PHP 8.3、Nginx、MySQL、PHPMyAdmin 和 Redis
- 加入本地 SSL 憑證生成功能
- 資料庫套件變更：從 Eloquent ORM 改為使用 Doctrine DBAL
- 環境變數處理方式變更：從 `getenv()` 改為使用 `$_ENV`
- 資料庫連線驅動變更：在 .env 檔案中將 `DB_CONNECTION` 從 `mysql` 改為 `pdo_mysql`
- 改進依賴注入方式：在 Action 類別中使用容器獲取服務，而非直接注入
- 在 BaseAction 中增加 `protected ContainerInterface $container` 屬性
- 簡化 PostDemoAction 的錯誤處理邏輯

### 2025-04-07
- 初始版本發布
- 完成基本功能設置