# 關於我們模組 MySQL Schema 規劃

## 表：about_stories
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK | 主鍵 |
| title | VARCHAR(120) | 標題 |
| content | LONGTEXT | 敘事內容 (t.1) |
| media_id | BIGINT FK | 對應主圖片（p.1 或 p.2） |
| status | ENUM('draft','published') | 狀態 |
| sort_order | INT | 排序 |
| created_at | DATETIME | 建立時間 |
| updated_at | DATETIME | 更新時間 |

## 表：about_metrics
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| label | VARCHAR(80) | 指標副標（店家數等） |
| value | VARCHAR(40) | 數值（20+） |
| description | VARCHAR(120) | 補充說明 |
| sort_order | INT | 排序 |
| status | TINYINT | 啟用狀態 |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：about_timeline_events
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| year | VARCHAR(20) | 年份主標（2022 等） |
| title | VARCHAR(120) | 副標或事件標題 |
| description | TEXT | 內容敘述 |
| media_id | BIGINT FK | 圖片（p.3～p.5） |
| sort_order | INT | 排序 |
| status | TINYINT | 啟用狀態 |
| created_at | DATETIME |
| updated_at | DATETIME |
