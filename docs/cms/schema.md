# CMS 內容區塊 MySQL Schema 規劃

## 表：cms_content_blocks
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK | 主鍵 |
| route | VARCHAR(120) | 對應頁面路由（`/`, `/menu`, `/about`…） |
| section | VARCHAR(80) | 區塊代碼（`hero`, `cta`, `faq-list`…） |
| type | ENUM('rich_text','image','custom') | 區塊類型 |
| locale | VARCHAR(10) NULL | 語系，null 表示共用 |
| title | VARCHAR(120) NULL | 後台顯示用標題 |
| payload | JSON | 儲存 HTML、媒體資訊或自訂欄位 |
| sort_order | INT DEFAULT 0 | 顯示順序 |
| status | ENUM('draft','active','archived') DEFAULT 'draft' | 狀態 |
| published_at | DATETIME NULL | 發布時間 |
| deleted_at | DATETIME NULL | 軟刪除 |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP | 建立時間 |
| updated_at | DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | 更新時間 |

索引建議：
- `idx_route_section_locale` (route, section, locale, status)
- `idx_status` (status)
- `idx_deleted_at` (deleted_at)

## 表：cms_sections（選用，用於定義合法組合）
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| route | VARCHAR(120) | 對應路由 |
| section | VARCHAR(80) | 區塊代碼 |
| label | VARCHAR(120) | 顯示名稱 |
| allowed_types | JSON | 可使用的 type 陣列 |
| config | JSON NULL | 自訂欄位 schema / 驗證規則 |
| is_active | TINYINT | 是否啟用 |

可在後台提供管理 UI，或使用 seeder 匯入。

## 表：cms_content_block_versions（可選）
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| block_id | BIGINT FK -> cms_content_blocks.id |
| payload | JSON | 變更前的資料 |
| status | ENUM('draft','active','archived') |
| changed_by | BIGINT FK -> admin_users.id |
| changed_at | DATETIME |

> 若短期不需版本回溯，可先仰賴操作日誌而不建立此表。

## 其他注意
- 由於 payload 為 JSON，Doctrine DBAL 需使用 `Types::JSON`，或在 Model 中 `json_encode/json_decode`。
- route/section 欄位建議以小寫與 `-`/`_` 命名，避免大小寫差異導致 cache miss。
