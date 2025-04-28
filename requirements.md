# Slim 4 專案需求文件

**版本：1.3.0**
**最後更新：2025-04-28**

## 專案重點摘要

✨ **核心技術梳理**
1. 基於 **Slim 4 + Twig 3 + PHP-DI + Doctrine DBAL** 的輕量級架構
2. 後台使用 **React 18 + Vite** 實作前端介面，支援 API 模式
3. 全面的 **CMS 內容管理系統**，支援多種內容類型、分類和標籤
4. **Docker 開發環境**，包含 PHP 8.3、Nginx、MySQL、Redis
5. 動態的 **權限管理系統**，支援自動註冊新功能和群組權限控制

🔥 **開發規範重點**
1. Action 類別均繼承 BaseAction，使用容器獲取相依服務
2. API 回應使用標準格式：`{success: true, data: ...}` 或 `{success: false, message: ...}`
3. 後台 API 路由命名規則：`/opanel/{module}/{resource}/{action}`
4. 後台操作日誌自動記錄為標準 JSON 格式

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

#### 2.4 內容管理系統 (CMS)
- 內容類型管理：支援不同類型的內容（如文章、區塊等）
- 分類管理：支援分類的新增、編輯、刪除和排序
- 內容管理：支援內容的 CRUD 操作，包含標題、內文、摘要、SEO 資訊等
- 標籤管理：支援標籤的搜尋、新增和與內容關聯

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

#### 2.2 CMS 內容管理相關資料表

##### 2.2.1 內容類型表 (cms_content_types)
- id：主鍵
- name：類型名稱
- slug：類型標識
- description：類型描述
- created_at：建立時間
- updated_at：更新時間

##### 2.2.2 內容表 (cms_contents)
- id：主鍵
- content_type_id：內容類型 ID
- title：標題
- slug：標識
- content：內容
- summary：摘要
- featured_image：特色圖片
- meta_title：SEO 標題
- meta_description：SEO 描述
- status：狀態
- sort_order：排序
- author_id：作者 ID
- created_at：建立時間
- updated_at：更新時間

##### 2.2.3 分類表 (cms_categories)
- id：主鍵
- parent_id：父分類 ID
- name：分類名稱
- slug：分類標識
- description：分類描述
- sort_order：排序
- created_at：建立時間
- updated_at：更新時間

##### 2.2.4 標籤表 (cms_tags)
- id：主鍵
- name：標籤名稱
- slug：標籤標識
- created_at：建立時間
- updated_at：更新時間

##### 2.2.5 內容與分類關聯表 (cms_content_category)
- content_id：內容 ID
- category_id：分類 ID

## 系統架構

### 1. 主要目錄結構
```
app/
  Actions/      → 控制器類別，處理 HTTP 請求
  Middleware/    → 中介層類別
  Models/       → 資料模型類別
  Routes/       → 路由定義檔案
  Templates/    → Twig 模板檔案
  Utils/         → 工具類別

docker/         → Docker 相關配置
  mysql/        → MySQL 配置和初始化腳本
  nginx/        → Nginx 配置
  php/          → PHP 配置
  ssl/          → SSL 憑證目錄

public/         → 公開訪問目錄
  index.php     → 應用程式入口點

resources/      → 前端資源原始檔
  react-opanel/ → React 後台應用
```

### 2. 設計模式與架構特點
- **MVC 架構**：Action 作為控制器，Model 處理資料，Twig 負責視圖
- **依賴注入**：使用 PHP-DI 容器自動解析和注入依賴
- **路由組織**：依功能模組分割路由檔案，方便維護
- **容器注入**：Action 類別透過容器取得相依服務，降低耦合度

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

### 2025-04-26
- 新增權限管理中建立及刪除角色功能
- 重構後台使用者管理，支援軟刪除與模型抽象化
- 修正日誌路徑及更新 Nginx 和 PHPMyAdmin 日誌配置，新增 Promtail 日誌收集設定

### 2025-04-25
- 新增後台使用者管理功能：列表、新增、編輯、重設密碼、啟用/停用、刪除等操作
- 使用 React 18 + Vite 實作前端介面，支援 API 模式與傳統表單提交
- 後台操作日誌自動記錄為標準 JSON 格式，整合 Loki+Grafana 監控，日誌保留 90 天
- 後台選單整合，新增使用者管理選項，UI/UX 採用 Tabler UI 樣式並統一 Toast 通知

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

## 開發規範與標準

### 1. Action 類別設計規範

#### 🔥 核心原則
- 所有 Action 均繼承 BaseAction，統一管理 view/logger/conn 等服務注入
- BaseAction 已包含 ContainerInterface $container 屬性，可用於取得其他服務
- 方法命名規範：
  - 前台頁面：`index()`、`show()`、`create()`、`edit()`
  - API：`apiGetAll()`、`apiGetOne()`、`apiCreate()`、`apiUpdate()`、`apiDelete()`

#### 路由綁定方式
| 綁定語法 | 觸發方法 | 範例用途 |
|---------|---------|--------|
| `SomeAction::class` | 觸發 `__invoke()` | 單一職責的 Action |
| `[SomeAction::class, 'methodName']` | 呼叫具名方法 | 多功能 Action，如 `index()` 或 `apiGetAll()` |

#### API 回應格式規範
使用 BaseAction 中的 `respondJson()` 方法返回標準格式的 JSON 回應：
```php
// 成功回應
 return $this->respondJson($response, [
    'success' => true,
    'data' => $data
]);

// 失敗回應
 return $this->respondJson($response, [
    'success' => false,
    'message' => $errorMessage,
    'errors' => $validationErrors // 可選
], $statusCode);
```

### 2. 前端開發規範

#### React 元件開發
- 使用 React 18 + Vite 實作後台管理介面
- 元件檔案存放於 `resources/react-opanel/src/pages/` 目錄下
- 檔案命名規則：使用 PascalCase，如 `CmsCategories.jsx`
- API 網址直接在 React 元件中定義，而非透過 data 屬性傳遞

#### 後台 API 路由規範
- 後台頁面路由：`/opanel/{module}/{resource}`
- 後台 API 路由：`/opanel/{module}/{resource}/{action}`
- 單一資源操作：`/opanel/{module}/{resource}/{id}/{action}`
- HTTP 方法：GET（查詢）、POST（建立）、PUT/PATCH（更新）、DELETE（刪除）

## 常見問題解答 (FAQ)

### 1. 開發環境相關

#### Q: 如何快速設置開發環境？
A: 使用 Docker：`docker compose up -d`。確保已安裝 mkcert 並生成 SSL 憑證。

#### Q: 為何需要使用 mkcert？
A: 為了生成本地信任的 SSL 憑證，避免瀏覽器顯示安全警告。

#### Q: 資料庫中文顯示亂碼如何解決？
A: 確保使用 utf8mb4 編碼和 utf8mb4_general_ci 排序規則，相關設定在 docker/mysql/conf.d/charset.cnf 中。

### 2. 後台開發相關

#### Q: 如何建立新的後台功能？
A: 建立繼承 BaseAction 的控制器類別，在 routes 中註冊路由，權限系統會自動註冊新功能。

#### Q: 如何建立新的 CMS 內容類型？
A: 在 cms_content_types 資料表中新增記錄，然後使用 CmsContentTypeModel 的方法進行管理。

#### Q: 如何處理後台操作日誌？
A: 使用 AdminLogMiddleware 自動記錄後台操作，或使用 LogUtil 手動記錄。所有日誌以 JSON 格式存儲。

### 3. 前端開發相關

#### Q: 如何新增 React 元件到後台？
A: 在 resources/react-opanel/src/pages/ 中新增 JSX 檔案，然後在 vite.config.js 中註冊入口點。

#### Q: 如何處理前端與後端的資料交換？
A: 使用 fetch API 進行請求，回應格式為標準 JSON：`{success: true, data: ...}` 或 `{success: false, message: ...}`。