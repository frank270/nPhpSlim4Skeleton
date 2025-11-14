# CMS 內容區塊模組規劃驅動文件

## 功能目標
- 以單一 CMS 模組管理所有頁面的區塊（Hero、CTA、FAQ、Banner 等），取代舊的 home content 資料表。
- 支援文字/HTML 及圖片內容，並可指定所屬路由與 section，供 Twig SSR 版型載入。
- 透過 Quill 編輯器與既有媒體資源庫提供上稿、調整執行歷程與狀態管理。

## 使用者情境
- 行銷人員更新首頁 Hero 文字、CTA 或加入新圖，立刻反映於前台模板。
- 編輯在 About/Menu/News 等頁面新增區塊或輪播圖，無需修改 Twig，僅調整 CMS 區塊資料。
- 多語站點：為 `/about` 的 `hero` section 建立 zh-TW、en-US 兩份內容，並可草稿/發布切換。

## 資料結構
- `cms_content_blocks`
  - route（`/`, `/menu`, `/about` 或 route 代稱）
  - section（`hero`, `cta`, `faq-list`, `story-card`…依設定）
  - type：`rich_text`, `image`, `custom`
  - locale（可為 null 表示共用）
  - title（可選，用於後台辨識）
  - payload JSON：
    - rich_text：`html`, `plain_text`, `quill_delta`
    - image：`media_id`, `url`, `alt`, `caption`, `link_url`, `display_mode`
    - custom：依 section schema 產生 key（例：`cards`, `button_text`）
  - sort_order、status（draft/active/archived）、published_at、created_at、updated_at
- `cms_sections`（可選）：預先定義 route + section + 說明 + required type，用來產生下拉選單與驗證。

## 後台需求
- React 介面 `/opanel/content-blocks`
  - 篩選器：route、section、type、locale、狀態、關鍵字
  - 列表：顯示標題、section、語系、狀態、最後更新時間，可拖曳排序
  - 編輯器：
    - rich_text：使用 Quill（含標題、字型、列表、連結、自訂 Image handler）
    - image：掛接媒體資源庫 modal（可多次重用），可編輯 alt/caption/link
    - custom：依 `cms_sections` 設定產生動態欄位（初期可先提供 JSON 編輯區）
  - 操作：建立、複製、草稿/發布切換、刪除（軟刪除）、批次排序
- Quill 整合：
  - Toolbar 增加「插入圖片」與「上傳圖片」；上傳串現有媒體 API，顯示進度條
  - 自訂 handler 會把 media_id、url 記錄進 payload

## 前端與 API
- 後端 API（`/opanel/content-blocks/*`）提供 CRUD，權限由 `ContentBlockAction` 控制並寫入 `logAction`
- 前台取得資料方式：
  - Service/Model：`ContentBlocksModel::findActiveByRoute($route, $locale)` 回傳 `section => blocks`
  - Twig 內依 section 渲染：`{{ block.payload.html|raw }}` 或 `<img src="{{ block.payload.url }}">`
- 可選前台 API：`GET /api/front/content-blocks?route=/menu&locale=zh-TW` 供動態頁面或第三方使用
- Cache 策略：以 `content_blocks:{route}:{locale}` 為 key；每次建立/更新/刪除 block 時清除該 route 的 cache

## 依賴與整合
- 媒體資源庫：所有圖片皆來自 `public/upload/{Y}/{m}/uuid.ext`，沿用狀態/alt/caption，並可複用既有 React modal
- 外部連結設定：CTA 類區塊可引用已存在的 external link uuid
- 權限系統：新增 `ContentBlockAction` 方法到 `PermissionChecker`，確保只有內容管理員可操作
- 錄製操作日誌：透過 `BaseAction::logAction()` 保存增刪改歷程

## 風險與備註
- Route/section key 需統一管理，避免拼錯導致前台取得不到資料（建議 `cms_sections` 或設定檔）
- Quill 輸出 HTML 需做 XSS 淨化，可在儲存或渲染時使用 `HTMLPurifier` / `strip_tags` whitelist
- 若未來需要版本回溯，可新增 `cms_content_block_versions` 或直接將 payload 存於 Log 中
- 快取與多語：更新內容後需即時清除 cache；不同 locale 不互相覆蓋
