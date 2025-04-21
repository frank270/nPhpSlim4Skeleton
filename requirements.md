# Slim 4 專案需求文件

## 專案概述

這是一個基於 Slim 4 框架的 PHP 專案，旨在提供一個輕量級但功能完整的網站框架，包含前台展示和後台管理系統。專案使用 Twig 模板引擎、PHP-DI 依賴注入容器、Doctrine DBAL 資料庫抽象層和 Dotenv 環境變數管理。

## 技術規格

### 核心技術

- **PHP 版本**：PHP 8.3+
- **框架**：Slim 4
- **模板引擎**：Twig 3
- **依賴注入**：PHP-DI
- **資料庫抽象層**：Doctrine DBAL
- **環境變數**：Dotenv
- **日誌系統**：Monolog
- **快取系統**：Redis

### 前端技術

- **後台 UI 套件**：Tabler
  - 基於 Bootstrap 的開源後台介面套件
  - 包含響應式導航欄、側邊欄、表單等元件
  - 使用 Tabler Icons 圖示庫

### 開發環境

- **Docker 容器**：
  - PHP 8.3 (php-fpm)
  - Nginx
  - MySQL 8.0
  - Redis
  - 本地 SSL 憑證 (mkcert)

### 資料庫

- **資料庫引擎**：MySQL 8.0
- **字元編碼**：utf8mb4
- **排序規則**：utf8mb4_general_ci

## 功能需求

### 1. 前台功能

#### 1.1 基本頁面
- 首頁 (Landing Page)
- 文章列表頁
- 文章詳情頁

### 2. 後台管理系統

#### 2.1 使用者認證
- 登入功能
- 登出功能
- Session 管理

#### 2.2 權限管理
- **開發重點**：
  - 使用 `RouteContext::fromRequest($request)->getRoute()` 安全取得已匹配的 Route。
  - 若無匹配 Route，使用 URI 路徑 `getUri()->getPath()` 與 HTTP 方法 `getMethod()` 組合作為功能識別符，確保所有後台請求均進入檢查。
  - 在 `public/index.php` 利用 `$app->addRoutingMiddleware()` 最先執行 Routing，再以 `$app->addErrorMiddleware()` 處理 404/500。
  - `PermissionMiddleware` 注入流程：Routing -> Flash -> Permission -> Twig，避免路由屬性為 null。
  - `PermissionChecker` 動態管理 `permissions_ctrl_func`、`permissions_matrix`，自動註冊新功能並依群組設定啟用或禁用。
  - 中介層日誌：`error_log` 追蹤路由匹配、資料庫連線與權限檢查結果。

- 基於角色的存取控制 (RBAC)
- 使用者群組管理
- 功能權限控制
- 動態權限功能：
  - 當使用者訪問尚未定義權限的功能時，系統自動處理
  - 若使用者具有最高權限角色，自動建立功能權限並開放權限
  - 若使用者不具有最高權限角色，預設禁止訪問

#### 2.3 儀表板
- 系統概覽
- 使用者資訊顯示

#### 2.4 內容管理
- 文章管理 (CRUD 操作)

## 資料模型

### 1. 權限管理相關資料表

#### 1.1 功能控制表 (permissions_ctrl_func)
- 功能代碼
- 顯示名稱
- 控制器和方法
- 功能類型 (前台/後台)

#### 1.2 角色群組表 (permissions_groups)
- 群組代碼
- 群組名稱
- 備註說明

#### 1.3 權限設定表 (permissions_matrix)
- 群組與功能的關聯
- 權限啟用狀態

#### 1.4 後台使用者表 (admin_users)
- 使用者名稱
- 密碼 (bcrypt 雜湊)
- 顯示名稱
- 所屬群組
- 登入記錄

### 2. 內容管理相關資料表

#### 2.1 文章表 (posts)
- 標題
- 內容
- 建立時間

## 系統架構

### 1. 目錄結構
```
app/
  Actions/      → 控制器類別，處理 HTTP 請求
    BaseAction.php      → 基礎控制器類別
    HomeAction.php      → 首頁控制器
    PostDemoAction.php  → 文章示範控制器
    Opanel/             → 後台管理相關控制器
  Models/       → 資料模型類別
    PostModel.php       → 文章模型
  Routes/       → 路由定義檔案
    home.php            → 首頁路由
    posts.php           → 文章路由
    opanel_auth.php     → 後台認證路由
    opanel_dashboard.php → 後台儀表板路由
  Templates/    → Twig 模板檔案
    hello.twig          → 歡迎頁模板
    landing.twig        → 首頁模板
    posts.twig          → 文章列表模板
    opanel/             → 後台模板目錄
  database.php  → 資料庫連線設定 (Doctrine DBAL)
  dependencies.php → 容器依賴設定
  middleware.php → 全域中介件
  routes.php    → 路由定義入口點
  settings.php  → 應用程式設定

cache/
  twig/         → Twig 模板快取

docs/
  sql/          → SQL 資料庫結構檔案
    auth_table.sql      → 權限管理相關資料表
    post.sql            → 文章資料表

docker/         → Docker 相關配置
  mysql/        → MySQL 配置
  nginx/        → Nginx 配置
  php/          → PHP 配置
  phpmyadmin/    → PHPMyAdmin 配置
  ssl/          → SSL 憑證

log/
  app.log       → 應用程式日誌

public/
  index.php     → 應用程式入口點
  assets/        → 前端資源檔案
    tabler/      → Tabler UI 套件
  .htaccess     → Apache 重寫規則
```

### 2. 設計模式
- MVC 架構
- 依賴注入模式
- 資源庫模式 (Repository Pattern)

## 非功能需求

### 1. 效能
- 使用 Redis 進行快取
- 優化資料庫查詢
- Twig 模板快取

### 2. 安全性
- 密碼使用 bcrypt 雜湊
- 防止 SQL 注入
- CSRF 保護
- XSS 防護

### 3. 可維護性
- 模組化代碼結構
- 清晰的命名約定
- 完整的文檔

### 4. 可擴展性
- 支援添加新模組
- 靈活的路由系統
- 可配置的依賴注入

## 環境需求

### 開發環境
- Docker
- Docker Compose
- mkcert (用於生成本地信任的 SSL 憑證)
- 本機 hosts 檔案設定：
  ```
  127.0.0.1 ndev.local
  127.0.0.1 ndba.local
  ```

### 生產環境
- PHP 8.3+
- MySQL 8.0+
- Redis
- Nginx 或 Apache
- SSL 憑證

## 部署流程

### 本地開發
1. 安裝 Docker 和 Docker Compose
2. 安裝 mkcert 並設定本地 CA
3. 生成本地 SSL 憑證
4. 設定 .env 檔案
5. 執行 `docker compose up -d`

### 生產環境
1. 設定伺服器環境
2. 設定資料庫
3. 部署程式碼
4. 設定 Web 伺服器
5. 設定 SSL 憑證

## 後台功能開發規劃

### 第一階段 To-Do 列表

| 模組 | 功能建議 | 備註 |
|------|---------|------|
| ✅ 登入/登出 | 已完成 | 可加 session timeout 機制 |
| ✅ Dashboard | 基本完成 | 可加上系統統計或快捷操作區域 |
| 🔒 權限系統 | 後台功能自動註冊 + 權限控管 | 你之前設計已納入「使用時自動註冊」，進一步完善 |
| 👤 使用者管理 | 建立/修改/停用帳號 | 一般 CMS 的核心功能 |
| 🔄 middleware 提取 session | 可選： 中介層提取 session->user | 讓 controller 不必反覆 $_SESSION[...] |


