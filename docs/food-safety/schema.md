# 食品安全檢驗模組 MySQL Schema 規格 (Implemented)

## 表：food_safety_categories
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| name | VARCHAR(120) | 類別名稱 |
| slug | VARCHAR(120) UNIQUE |
| description | TEXT |
| sort_order | INT | 預設 0 |
| is_active | TINYINT | 預設 1 |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：food_safety_items
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| category_id | BIGINT FK | 關聯分類 |
| name | VARCHAR(120) | 報告名稱 / 項目名稱 |
| description | TEXT |
| image_path | VARCHAR(255) | 預覽圖片路徑 (Optional) |
| file_path | VARCHAR(255) | PDF 檔案路徑 (Optional) |
| report_date | DATE | 檢驗日期 |
| expired_at | DATE | 到期日期 |
| tags | TEXT | 標籤 (Optional) |
| is_highlight | TINYINT | 重點標記 (預設 0) |
| status | VARCHAR(20) | 'draft', 'published', 'archived' |
| published_at | DATETIME | 上架時間 |
| created_at | DATETIME |
| updated_at | DATETIME |

> 備註：此表結構已於 2025-12-16 實作並部署。

