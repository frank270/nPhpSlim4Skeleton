# 首頁內容模組 MySQL Schema 規劃

## 表：home_contents
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK | 主鍵 |
| hero_title | VARCHAR(120) | Hero 主標 (t.1) |
| hero_subtitle | VARCHAR(120) | Hero 副標 (t.2) |
| hero_tagline | VARCHAR(120) | 宣傳詞 (t.3) |
| hero_cta_text | VARCHAR(80) | CTA 文案 (t.4) |
| hero_cta_link_id | BIGINT FK | 外部連結 ID |
| mission_title | VARCHAR(80) | 使命標題 |
| mission_content | TEXT | 使命內容 (t.7) |
| vision_title | VARCHAR(80) | 願景標題 |
| vision_content | TEXT | 願景內容 (t.8) |
| narrative | LONGTEXT | 長文 (t.15) |
| status | ENUM('draft','published') | 狀態 |
| published_at | DATETIME | 發布時間 |
| created_at | DATETIME | 建立時間 |
| updated_at | DATETIME | 更新時間 |

## 表：home_navigation_links
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK | 主鍵 |
| home_content_id | BIGINT FK | 關聯首頁內容 |
| label | VARCHAR(80) | 顯示文字 |
| target_slug | VARCHAR(80) | 導向頁代碼 |
| link_id | BIGINT FK | 外部連結（可選） |
| sort_order | INT | 排序 |
| is_active | TINYINT(1) | 啟用狀態 |

## 圖片關聯
- 透過共用的 `media_assets` 資料表維護。可另建 `home_media_relations`（欄位：home_content_id、media_id、slot）。
