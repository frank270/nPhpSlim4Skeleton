# 最新消息模組 MySQL Schema 規劃

## 表：news_categories
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| name | VARCHAR(80) |
| slug | VARCHAR(80) UNIQUE |
| sort_order | INT |
| is_active | TINYINT |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：news_articles
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| category_id | BIGINT FK |
| title | VARCHAR(150) |
| subtitle | VARCHAR(150) |
| summary | TEXT |
| content | LONGTEXT |
| hero_media_id | BIGINT FK | 主圖（p.1／p.2…） |
| link_url | VARCHAR(255) | 相關連結（可選） |
| is_featured | TINYINT | 精選標記 |
| publish_at | DATETIME | 發布時間 |
| expire_at | DATETIME | 下架時間（可 null） |
| status | ENUM('draft','scheduled','published','archived') |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：news_media
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| article_id | BIGINT FK |
| media_id | BIGINT FK |
| sort_order | INT |
| caption | VARCHAR(120) | 圖片說明（可選） |

> 可加上 `news_tags`、`news_article_tags` 以支援標籤功能。
