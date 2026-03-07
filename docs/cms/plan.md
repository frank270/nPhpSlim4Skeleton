# CMS 模組重構計畫 (Single Table + Quill)

## 1. 核心目標
建立一個輕量級、單一資料表的內容管理系統，使用 **Quill** 作為富文本編輯器，並支援圖片上傳功能。

## 2. 功能需求
*   **單一列表管理**：所有的文章/頁面統一管理，不再拆分複雜的 Categories/Tags 關聯表。
*   **Quill 編輯器集成**：
    *   基本格式 (粗體、斜體、標題)。
    *   **圖片上傳**：客製化 Handler，將圖片上傳至伺服器並插入 URL (非 Base64)。
*   **封面圖 (Cover Image)**：獨立欄位，用於列表顯示。
*   **狀態管理**：草稿 (Draft) /發布 (Published)。

## 3. 資料庫結構 (Single Table)
### Table: `cms_posts`
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| `id` | BIGINT PK | |
| `title` | VARCHAR(255) | 標題 |
| `slug` | VARCHAR(255) | 網址代稱 (Unique) |
| `cover_image` | VARCHAR(255) | 封面圖路徑 |
| `content` | LONGTEXT | 內文 (HTML) |
| `status` | ENUM | 'draft', 'published' |
| `published_at` | DATETIME | 發布時間 |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |

## 4. 開發項目

### 清理舊檔案 (Cleanup)
- [x] **Delete**: `app/Actions/Opanel/CmsCategoryAction.php`
- [x] **Delete**: `app/Models/Cms*Model.php` (Content, Category, Tag, ContentType)
- [x] **Delete**: `resources/react-opanel/src/pages/cms/CmsCategories.jsx`

### 後端 (Backend)
- [ ] **Migration**: 建立 `cms_posts` 資料表 (Drop old tables if exist).
- [ ] **Model**: `CmsPostModel` (CRUD).
- [ ] **Action**: `Opanel\CmsPostAction`.
    - `API List`, `API Create`, `API Update`, `API Delete`.
    - **Upload Strategy**: 直接使用現有的 `/opanel/media-assets/upload` API，不需重複實作。
- [ ] **Route**: 重寫 `opanel_cms.php`.

### 前端 (Frontend - React)
- [ ] **CmsList.jsx**: 文章列表。
- [ ] **CmsEditor.jsx**: 編輯頁面 (Quill).
    - **Cover Image Upload**: 呼叫 MediaAssets API 上傳，取得 URL 填入欄位。
    - **Quill Image Handler**: 呼叫 MediaAssets API 上傳，取得 URL 插入編輯器。

## 5. 預計流程
1. 執行 Database Migration。
2. 建立後端 API (含 Upload)。
3. 實作前端列表與編輯器。
4. 驗收 (圖片上傳測試)。
