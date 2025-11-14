# 加盟合作模組 MySQL Schema 規劃

## 表：franchise_steps
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| step_no | INT | 步驟編號（1/2/3） |
| title | VARCHAR(120) | 步驟標題 |
| description | TEXT | 步驟敘述 |
| category | ENUM('store','people','other') | 流程分類（店／人／其他） |
| media_id | BIGINT FK | 圖示或背景圖 |
| sort_order | INT |
| is_active | TINYINT |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：franchise_contacts
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| label | VARCHAR(80) | 顯示標題（CONTACT US 等） |
| value | VARCHAR(255) | 內容（電話、Email 等） |
| type | ENUM('phone','email','cta','note') | 類型 |
| sort_order | INT |
| is_active | TINYINT |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：franchise_applications
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| applicant_name | VARCHAR(120) |
| email | VARCHAR(120) |
| phone | VARCHAR(40) |
| topic | ENUM('加盟','了解','其他') |
| message | TEXT |
| status | ENUM('new','in_progress','closed') |
| assigned_to | BIGINT FK | 指派處理的後台使用者 |
| created_at | DATETIME |
| updated_at | DATETIME |
| closed_at | DATETIME | 結案時間（可 null） |

> 表單附件可另建 `franchise_application_files`。若需與 CRM 串接，可新增 `external_id` 欄位。
