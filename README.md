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
- 在本機 hosts 檔案中加入以下設定：
  ```
  127.0.0.1 ndev.local
  127.0.0.1 ndba.local
  ```

### 啟動 Docker 環境

```bash
# 複製 Docker 環境變數檔案
cp .env.docker .env

# 啟動 Docker 容器
docker-compose up -d
```

### 存取服務

- 網站：https://ndev.local:8243
- PHPMyAdmin：https://ndba.local:8243
- MySQL：localhost:3306
- Redis：localhost:6379

### 停止 Docker 環境

```bash
docker-compose down
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