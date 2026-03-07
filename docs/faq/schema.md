# FAQ 模組 MySQL Schema 規劃

> ✅ 已於 `Version20251119142345` 遷移建立 `faq_categories`、`faqs` 兩張資料表。

## 表：faq_categories

| 欄位 | 型別 | 說明 | 備註 |
| --- | --- | --- | --- |
| id | BIGINT PK | 主鍵 | AUTO_INCREMENT, UNSIGNED |
| name | VARCHAR(120) | 分類名稱 | NOT NULL |
| slug | VARCHAR(120) UNIQUE | 路由識別 | 僅允許小寫字母／數字／連字號 |
| sort_order | INT | 排序 | 預設 0 |
| is_active | TINYINT(1) | 是否顯示 | 1=啟用、0=停用 |
| created_at | DATETIME | 建立時間 | 預設 CURRENT_TIMESTAMP |
| updated_at | DATETIME | 更新時間 | 預設 CURRENT_TIMESTAMP ON UPDATE |

## 表：faqs

| 欄位 | 型別 | 說明 | 備註 |
| --- | --- | --- | --- |
| id | BIGINT PK | 主鍵 | AUTO_INCREMENT, UNSIGNED |
| category_id | BIGINT FK | 分類 ID | 可為 NULL 表示未分類；`ON DELETE SET NULL` |
| question | VARCHAR(255) | 問題 | NOT NULL |
| answer | LONGTEXT | 答案 | NOT NULL |
| is_highlight | TINYINT(1) | 精選標記 | 1=精選、0=一般 |
| status | ENUM('draft','published') | 顯示狀態 | 預設 `draft` |
| sort_order | INT | 排序 | 預設 0 |
| created_at | DATETIME | 建立時間 | 預設 CURRENT_TIMESTAMP |
| updated_at | DATETIME | 更新時間 | 預設 CURRENT_TIMESTAMP ON UPDATE |

> **布林欄位儲存方式：** `is_active`、`is_highlight` 皆使用 `TINYINT(1)` 儲存，程式寫入時需轉為 `0/1`，避免發生 `Incorrect integer value`。

## 表：faq_translations（可選，多語支援）

| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| faq_id | BIGINT FK |
| locale | VARCHAR(10) | 語系代碼 |
| question | VARCHAR(255) |
| answer | LONGTEXT |
| UNIQUE | (faq_id, locale) |

> 若需統計瀏覽或搜尋記錄，可加上 `faq_search_logs`。
