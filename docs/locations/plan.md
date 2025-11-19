# 門市據點模組規劃驅動文件

## 功能目標
- 維護各城市門市資訊（電話、地址、座標、地圖與訂餐連結）。
- 前台可依城市或關鍵字搜尋門市資訊。

## 使用者情境
- 營運人員新增或更新門市資料、調整營業狀態。
- 前台顯示城市列表、門市資訊、Google Maps 與線上點餐按鈕。

## 資料結構
- ✅ 門市資料表：`location_stores`（不包含城市分組表）
- ✅ 每筆門市詳情：名稱、電話、地址（縣市、行政區、郵遞區號、完整地址）、座標、地圖連結、點餐連結
- ⏸️ 英雄區與表單內容（可選，若保留需同時管理）

## 後台需求

### 已完成 ✅
- ✅ 門市資料管理：電話、地址（縣市/行政區/郵遞區號）、座標、備註、營業狀態（open/pause/closed）
- ✅ 門市 CRUD 操作（新增、編輯、刪除）
- ✅ 狀態切換功能（循環切換：open → pause → closed → open）
- ✅ 列表顯示與分頁
- ✅ 地址選擇器整合（台灣郵遞區號三層聯動）

### 待實作 ⏸️
- ⏸️ 搜尋與篩選（依縣市、關鍵字、狀態）
- ⏸️ 排序功能（使用 `sort_order` 欄位）
- ⏸️ 批次更新功能
- ⏸️ 若保留表單區：欄位設定、提交通知、資料匯出

## 前端與 API

### 後台 API（已完成）✅
- ✅ `GET /opanel/locations/list`：門市列表（含分頁）
- ✅ `GET /opanel/locations/{id}`：單一門市資訊
- ✅ `POST /opanel/locations/create`：新增門市
- ✅ `POST /opanel/locations/{id}/edit`：更新門市
- ✅ `DELETE /opanel/locations/{id}/delete`：刪除門市
- ✅ `POST /opanel/locations/{id}/toggle-status`：切換狀態（循環切換）

### 前台 API（待實作）⏸️
- ⏸️ `GET /api/front/locations/cities`：回傳縣市與門市數量
- ⏸️ `GET /api/front/locations?county=xxx`：回傳指定縣市門市
- ⏸️ `GET /api/front/locations/store/{id}`：單一門市資訊
- ⏸️ Cache：縣市列表可快取；門市更新時清除

## 依賴與整合

### 已整合 ✅
- ✅ 外部連結設定（`map_link_id`, `order_link_id` 欄位已建立，待實作連結選擇器）
- ✅ 權限控管：使用 `AdminLogMiddleware` 記錄操作日誌
- ✅ 地址選擇器：使用台灣郵遞區號資料（`public/js/data/taiwan-zipcode-data.json`）

### 待整合 ⏸️
- ⏸️ 媒體資源庫（若門市頁有 hero 圖或宣傳圖）：使用 `public/upload/{Y}/{m}/uuid.ext`
- ⏸️ 表單模組（若保留）
- ⏸️ Google Maps 整合（使用 `map_link_id`）
- ⏸️ 點餐連結整合（使用 `order_link_id`）

## 風險與備註
- 地址需轉換座標，可透過 Geocoding API 預處理。
- 點餐連結若變動頻繁，需提供批次更新工具。
- 表單若啟用，需防止垃圾訊息並設通知流程。
