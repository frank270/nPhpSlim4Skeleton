# 門市據點模組待辦清單

## 後端
- [x] 建立門市資料表（`location_stores`，不包含城市資料表）
- [x] 完成後台門市管理介面（列表、新增、編輯、刪除、狀態切換）
- [x] 實作後台 API（`/opanel/locations/*`）
- [ ] 實作前台 `GET /api/front/locations` 系列 API 與快取
- [ ] 表單（若保留）欄位設定與提交流程

## 前端（後台）
- [x] 建立 React 管理頁面（`Locations.jsx`）
- [x] 整合地址選擇器（台灣郵遞區號）
- [x] 表單驗證與送出
- [x] 狀態切換功能
- [x] 列表顯示與分頁

## 前端（前台）
- [ ] 城市選單／Accordion 切換與 RWD
- [ ] Google Maps 與線上點餐按鈕測試
- [ ] 門市列表卡片排版、Lazyload
- [ ] 搜尋功能

## 內容與測試
- [x] 地址、電話、座標資料驗證（基本驗證）
- [ ] Google Maps & yoyvip 連結測試
- [x] 門市狀態（open/pause/closed）切換測試
- [ ] 表單通知與資料匯出流程檢查
- [ ] 單元測試（PHP）
- [ ] 單元測試（React）
- [ ] 整合測試

## 已完成項目詳情

### 後端
- ✅ `LocationStoresModel.php` - 資料存取層
- ✅ `LocationAction.php` - API 端點處理
- ✅ `opanel_locations.php` - 路由設定
- ✅ `index.twig` - React 容器頁面
- ✅ 資料庫遷移檔案

### 前端
- ✅ `Locations.jsx` - 完整的管理介面
- ✅ `taiwan-address-selector.js` - 地址選擇器
- ✅ `taiwan-zipcode-data.json` - 郵遞區號資料
- ✅ Vite 設定（`locations.bundle.js`）

### 文件
- ✅ `schema.md` - 資料表結構
- ✅ `plan.md` - 功能規劃
- ✅ `development-notes.md` - 開發筆記
- ✅ `taiwan-address-selector-usage.md` - 地址選擇器使用說明
