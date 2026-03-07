# 門市據點模組 MySQL Schema 規劃

## 表：location_stores

**狀態：** ✅ 已實作（2025-11-19）

| 欄位 | 型別 | 說明 | 備註 |
| --- | --- | --- | --- |
| id | BIGINT PK | 主鍵 | AUTO_INCREMENT, UNSIGNED |
| name | VARCHAR(120) | 門市名稱 | NOT NULL |
| county | VARCHAR(80) | 縣市 | 可選 |
| district | VARCHAR(80) | 行政區 | 可選 |
| zipcode | VARCHAR(10) | 郵遞區號 | 可選 |
| address | VARCHAR(255) | 完整地址 | 可選 |
| phone | VARCHAR(30) | 聯絡電話 | 可選 |
| latitude | DECIMAL(10,6) | 緯度 | 可選 |
| longitude | DECIMAL(10,6) | 經度 | 可選 |
| map_link_id | BIGINT | Google Maps 連結 ID | 可選，無外鍵約束 |
| order_link_id | BIGINT | 點餐連結 ID | 可選，無外鍵約束 |
| status | ENUM('open','pause','closed') | 營業狀態 | 預設 'open' |
| sort_order | INT | 排序 | 預設 0 |
| notes | TEXT | 備註（外送範圍等） | 可選 |
| created_at | DATETIME | 建立時間 | 預設 CURRENT_TIMESTAMP |
| updated_at | DATETIME | 更新時間 | 預設 CURRENT_TIMESTAMP ON UPDATE |

### 設計決策

1. **不包含城市資料表：** 直接使用 `county`、`district`、`zipcode` 欄位儲存地址資訊，簡化資料結構。
2. **無外鍵約束：** `map_link_id` 和 `order_link_id` 不使用外鍵約束，提供更靈活的資料管理。
3. **地址欄位分離：** 將地址拆分為縣市、行政區、郵遞區號和完整地址，方便搜尋與顯示。

### 遷移檔案

- `migrations/Version20251119121547.php` - 建立 `location_stores` 表

---

## 表：location_store_hours（未來擴充）

**狀態：** ⏸️ 未實作（可選功能）

| 欄位 | 型別 | 說明 |
| --- | --- | --- |
| id | BIGINT PK | 主鍵 |
| store_id | BIGINT FK | 門市 ID |
| weekday | TINYINT | 0=週日～6=週六 |
| open_time | TIME | 營業開始時間 |
| close_time | TIME | 營業結束時間 |

> **注意：** 地圖、訂餐 URL 建議由外部連結模組統一管理，`map_link_id` 與 `order_link_id` 對應該模組的 ID（目前無外鍵約束）。
