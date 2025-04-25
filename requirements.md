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
- **通知系統**：自定義 Toast 通知（基於 Tabler UI）

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

#### 2.2 後台使用者管理
- **開發重點**：
  - 完整的後台使用者（admin_users）管理功能，包含列表、新增、編輯、重設密碼、啟用/停用、刪除等操作
  - 使用 React 18 + Vite 實作前端介面，支援 API 模式與傳統表單提交
  - 所有操作均自動記錄為標準 JSON 格式日誌，符合 SIEM 規範
  - 整合 Loki+Grafana 監控，確保日誌保留 90 天
  - 敏感資料過濾（使用 LogUtil::filterSensitiveData）
  - 安全性設計：不可刪除當前登入者、密碼強雜湊、所有變更皆有日誌追蹤
  - UI/UX 設計：採用 Tabler UI 樣式，統一 Toast 通知，操作按鈕均有圖標與提示

#### 2.3 權限管理
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

cache/                  → 快取目錄

docs/                   → 文件目錄
  sql/                  → SQL 資料庫結構檔案
    auth_table.sql      → 權限管理相關資料表
    post.sql            → 文章資料表

docker/                 → Docker 相關配置
  mysql/                → MySQL 配置
    conf.d/             → MySQL 自定義配置
    init.sql            → 初始化資料庫腳本
  nginx/                → Nginx 配置
  php/                  → PHP 配置
  phpmyadmin/           → PHPMyAdmin 配置
  ssl/                  → SSL 憑證

log/                    → 應用程式日誌目錄

public/                 → 公開訪問目錄
  index.php             → 應用程式入口點
  assets/               → 前端資源檔案
    tabler/             → Tabler UI 套件
  js/                   → JavaScript 檔案
    toast.js            → Toast 通知系統
  .htaccess             → Apache 重寫規則

resources/              → 前端資源原始檔
  react-opanel/         → React 後台應用
    src/                → React 原始碼
      pages/            → React 頁面元件

vendor/                 → Composer 依賴
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


## 重大技術變動記錄

### 2025-04-24

#### 1. AJAX 請求格式變更

- **變更內容**：從 JSON 格式變更為表單格式（form-urlencoded）
- **影響範圍**：後台 API 請求，特別是權限管理相關功能
- **變更原因**：
  - Slim 4 框架預設自動解析表單格式的請求，但需要額外的中介層來解析 JSON 格式
  - 簡化後端處理邏輯，不需要額外的 JSON 解析中介層
- **實作方式**：
  ```javascript
  // 變更前（JSON 格式）
  const response = await fetch('/api/endpoint', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ key: value })
  });
  
  // 變更後（表單格式）
  const formData = new URLSearchParams();
  formData.append('key', value);
  const response = await fetch('/api/endpoint', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: formData
  });
  ```

#### 2. Toast 通知系統升級

- **變更內容**：實作符合 Tabler UI 設計風格的 Toast 通知系統
- **新增功能**：
  - 支援四種訊息類型：success（成功）、error（錯誤）、info（訊息）、warning（警告）
  - 每種類型都有對應的顏色、圖示和標題
  - 自動消失功能（預設 7 秒）
  - 支援手動關閉
- **使用方式**：
  ```javascript
  // 顯示成功訊息
  window.showToast('操作成功', 'success');
  
  // 顯示錯誤訊息
  window.showToast('發生錯誤', 'error');
  
  // 顯示警告訊息
  window.showToast('請注意', 'warning');
  
  // 顯示一般訊息
  window.showToast('提示資訊', 'info');
  ```

## Slim Action 類別設計原則與依賴注入行為

### 基本架構
- 所有 Action 均繼承 BaseAction，統一管理 view/logger/conn 等服務注入
- BaseAction 提供共用功能，各子類實作具體業務邏輯

### 依賴注入規則
| 情境 | 處理方式 | 說明 |
|------|---------|------|
| 無自定 constructor | ✅ 自動解析，不需註冊 | PHP-DI 會自動處理 |
| constructor 僅使用已註冊服務 | ✅ 自動解析，不需註冊 | 容器會自動注入已知服務 |
| constructor 含未註冊服務 | ⚠️ 需手動註冊 | 在 dependencies.php 中明確定義 |

### 路由綁定方式
| 綁定語法 | 觸發方法 | 範例用途 |
|---------|---------|--------|
| `SomeAction::class` | 觸發 `__invoke()` | 單一職責的 Action |
| `[SomeAction::class, 'methodName']` | 呼叫具名方法 | 多功能 Action，如 `stats()` 或 `handleGet()` |

### 技術說明
- PHP 不限制子類別覆寫 `__invoke()`，與繼承 BaseAction 完全相容
- Slim 只會呼叫路由中具體指定的方法，不會呼叫父類方法
- 使用具名方法可增加權限中介層的可見性，便於追蹤與除錯