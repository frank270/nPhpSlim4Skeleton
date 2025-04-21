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
  src/          → PHP Source files (Actions, Controllers)
  templates/    → Twig templates
  settings.php  → Application settings
  database.php  → Database connection setup (Doctrine DBAL)
  dependencies.php → Container dependencies
  middleware.php → Global middlewares
  routes.php    → Route definitions

cache/
  twig/         → Twig cache

log/
  app.log       → Application logs

public/
  index.php     → Entry point
  .htaccess     → Apache rewrite rules
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