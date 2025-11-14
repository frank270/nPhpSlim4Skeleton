# FAQ 模組 MySQL Schema 規劃

## 表：faq_categories
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| name | VARCHAR(120) | 分類名稱 |
| slug | VARCHAR(120) UNIQUE | 路由識別 |
| sort_order | INT | 排序 |
| is_active | TINYINT | 是否顯示 |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：faqs
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| category_id | BIGINT FK | 分類 ID（可為 null 表示共用） |
| question | VARCHAR(255) | 問題 |
| answer | LONGTEXT | 答案 |
| is_highlight | TINYINT | 精選標記 |
| status | ENUM('draft','published') |
| sort_order | INT |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：faq_translations（可選，多語支援）
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| faq_id | BIGINT FK |
| locale | VARCHAR(10) | 語系代碼 |
| question | VARCHAR(255) |
| answer | LONGTEXT |
| UNIQUE | (faq_id, locale) |

> 若需統計瀏覽或搜尋記錄，可加上 `faq_search_logs`。
