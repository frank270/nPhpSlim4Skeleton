# 門市據點模組 MySQL Schema 規劃

## 表：location_cities
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| name | VARCHAR(80) | 城市名稱 |
| slug | VARCHAR(80) UNIQUE | 路由識別 |
| sort_order | INT |
| is_active | TINYINT |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：location_stores
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| city_id | BIGINT FK | 城市 ID |
| name | VARCHAR(120) | 門市名稱 |
| phone | VARCHAR(30) | 聯絡電話 |
| address | VARCHAR(255) | 地址 |
| latitude | DECIMAL(10,6) | 緯度（可選） |
| longitude | DECIMAL(10,6) | 經度（可選） |
| map_link_id | BIGINT FK | Google Maps 連結（外部連結） |
| order_link_id | BIGINT FK | yoyvip 點餐連結 |
| status | ENUM('open','pause','closed') | 營業狀態 |
| sort_order | INT | 排序 |
| notes | TEXT | 備註（外送範圍等） |
| created_at | DATETIME |
| updated_at | DATETIME |

## 表：location_store_hours（可選，若需營業時間）
| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK |
| store_id | BIGINT FK |
| weekday | TINYINT | 0=週日～6=週六 |
| open_time | TIME |
| close_time | TIME |

> 地圖、訂餐 URL 建議由外部連結模組統一管理，`map_link_id` 與 `order_link_id` 對應該模組的 ID。
