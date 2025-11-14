# 聯絡我們模組 MySQL Schema 規劃

## 表：contact_settings
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| label | VARCHAR(80) | 顯示標題（地址、Email 等） |
| value | VARCHAR(255) | 內容 |
| type | ENUM('address','email','phone','note') | 類型 |
| sort_order | INT |
| is_active | TINYINT |
| updated_at | DATETIME |
| created_at | DATETIME |

## 表：contact_form_submissions
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| name | VARCHAR(120) |
| email | VARCHAR(120) |
| phone | VARCHAR(40) |
| topic | ENUM('餐點建議','服務回饋','其他') |
| message | TEXT |
| status | ENUM('new','in_progress','resolved') |
| handler_id | BIGINT FK | 指派處理者 |
| created_at | DATETIME |
| updated_at | DATETIME |
| resolved_at | DATETIME |

## 表：contact_form_logs（可選）
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| submission_id | BIGINT FK |
| action | VARCHAR(60) | 狀態變更、備註等 |
| detail | TEXT |
| actor_id | BIGINT FK | 操作者 |
| created_at | DATETIME |

> 建議搭配通知設定表（`notification_settings`）管理 Email/Slack 接收者。
