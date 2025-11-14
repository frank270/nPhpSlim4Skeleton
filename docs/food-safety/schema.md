# 食品安全檢驗模組 MySQL Schema 規劃

## 表：food_safety_categories
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| name | VARCHAR(80) | 類別名稱（飲品 / 食材 / 肉品 / 其他） |
| slug | VARCHAR(80) UNIQUE |
| media_id | BIGINT FK | 圖示（p.2～p.5） |
| sort_order | INT |
| is_active | TINYINT |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：food_safety_reports
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| category_id | BIGINT FK |
| title | VARCHAR(255) | 報告名稱 |
| file_path | VARCHAR(255) | PDF 位置 |
| report_date | DATE | 檢驗日期 |
| version | VARCHAR(40) | 報告批號或版本 |
| is_highlight | TINYINT | 重點標記 |
| published_at | DATETIME | 上架時間 |
| expired_at | DATETIME | 到期時間（可 null） |
| status | ENUM('draft','published','archived') |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：food_safety_download_logs
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| report_id | BIGINT FK |
| user_ip | VARCHAR(45) |
| downloaded_at | DATETIME |

> 檔案建議存於物件儲存（S3 等）。`file_path` 可搭配 CDN。
