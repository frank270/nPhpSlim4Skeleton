# 菜單模組 MySQL Schema 規劃

## 表：menu_categories
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| name | VARCHAR(120) | 類別名稱 |
| slug | VARCHAR(120) UNIQUE | 路由識別 |
| description | TEXT | 描述（可選） |
| sort_order | INT | 排序 |
| is_active | TINYINT | 是否顯示 |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：menu_items
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| category_id | BIGINT FK | 主分類 |
| name | VARCHAR(120) | 商品名稱 |
| description | TEXT | 商品描述 |
| price_original | DECIMAL(10,2) | 原價 |
| price_sale | DECIMAL(10,2) | 特價（可 null） |
| media_id | BIGINT FK | 主圖 |
| is_best_seller | TINYINT | 熱銷標記 |
| status | ENUM('draft','published','archived') |
| published_at | DATETIME | 上線時間 |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：menu_item_tags
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| item_id | BIGINT FK |
| tag_id | BIGINT FK |
| PRIMARY KEY | (item_id, tag_id) | 多對多關係 |

## 表：menu_price_history
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| item_id | BIGINT FK |
| price_before | DECIMAL(10,2) |
| price_after | DECIMAL(10,2) |
| changed_by | BIGINT FK | 操作者 |
| changed_at | DATETIME |

> 標籤建議共用 `tags` 資料表，並以 `tag_type = 'menu'` 區別。
