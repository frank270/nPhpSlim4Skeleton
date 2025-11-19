# 門市據點管理功能開發筆記

## 功能概述

開發完整的門市據點管理功能，包括後端 API、前端 React 頁面，以及台灣地址選擇器整合。

## 開發時間軸與問題記錄

### 1. 初始規劃階段

**需求確認：**
- 建立 `location_stores` 資料表（不包含 `location_cities`）
- 不使用外鍵約束（`map_link_id`, `order_link_id`）
- 使用 `jQuery-TWzipcode` 套件處理郵遞區號（後續改為自訂地址選擇器）

**已建立檔案：**
- `docs/locations/schema.md` - 資料表結構定義
- `docs/locations/plan.md` - 功能規劃文件
- `docs/locations/todo.md` - 待辦事項清單

---

### 2. 資料庫遷移

**檔案：** `migrations/Version20251119121547.php`

**注意事項：**
- ✅ 已正確建立 `location_stores` 表
- ✅ 未使用外鍵約束（符合需求）
- ✅ 包含所有必要欄位：`county`, `district`, `zipcode`, `address`, `phone`, `latitude`, `longitude`, `map_link_id`, `order_link_id`, `status`, `sort_order`, `notes`

---

### 3. 後端開發

#### 3.1 Model 層

**檔案：** `app/Models/LocationStoresModel.php`

**功能：**
- ✅ CRUD 操作
- ✅ 分頁查詢
- ✅ 取得不重複縣市列表

**無重大問題**

#### 3.2 Action 層

**檔案：** `app/Actions/Opanel/LocationAction.php`

**功能：**
- ✅ `index()` - React 容器頁面
- ✅ `apiList()` - 列表 API
- ✅ `apiGetOne()` - 單筆查詢 API
- ✅ `apiCreate()` - 新增 API
- ✅ `apiUpdate()` - 更新 API
- ✅ `apiDelete()` - 刪除 API
- ✅ `apiToggleStatus()` - 狀態切換 API（循環切換：open → pause → closed → open）
- ✅ `apiGetCounties()` - 取得縣市列表 API

**無重大問題**

#### 3.3 路由設定

**檔案：** `app/Routes/opanel_locations.php`

**已正確整合到：** `app/routes.php` 的 `/opanel` 群組中

**無重大問題**

---

### 4. 前端開發

#### 4.1 React 頁面

**檔案：** `resources/react-opanel/src/pages/Locations.jsx`

**功能：**
- ✅ 列表顯示
- ✅ 新增/編輯表單
- ✅ 刪除確認
- ✅ 狀態切換

#### 4.2 遇到的問題

##### 問題 1：Dashboard 側邊欄缺少連結

**問題描述：** 後台管理頁面的左側選單沒有「門市據點管理」的連結

**解決方案：**
- 在 `app/Templates/opanel/layout.twig` 中加入導航連結
- 使用適當的 SVG 圖示

**檔案位置：** `app/Templates/opanel/layout.twig`

##### 問題 2：狀態切換功能無效

**問題描述：** 點擊「切換狀態」按鈕後，狀態沒有更新

**原因分析：**
- 後端 `apiToggleStatus` 設計為循環切換狀態（不接收特定狀態值）
- 前端 `handleToggleStatus` 原本可能傳送了錯誤的資料格式

**解決方案：**
- 確認後端 API 設計：傳送空 JSON `{}` 讓後端自動循環切換
- 前端更新：使用 `handleToggleStatus` 傳送空物件，並根據後端回傳的 `status` 更新 UI

**修正位置：** `resources/react-opanel/src/pages/Locations.jsx` 的 `handleToggleStatus` 函數

##### 問題 3：jQuery-TWzipcode 整合失敗

**問題描述：** 
- 嘗試整合 `jQuery-TWzipcode` 套件處理郵遞區號
- UI 無法正常顯示
- 多次嘗試後仍無法解決

**嘗試的解決方案：**
1. 在 React 組件中動態載入 jQuery 和 TWzipcode（失敗）
2. 在 Twig 模板中預載入腳本（部分成功但仍有問題）
3. 使用延遲和重試邏輯（仍無法穩定運作）

**最終決策：**
- 用戶要求移除 `jQuery-TWzipcode`
- 改為使用三個簡單的輸入欄位（`county`, `district`, `zipcode`）

**移除的檔案/程式碼：**
- `app/Templates/opanel/locations/index.twig` 中的 jQuery 和 TWzipcode 腳本引用
- `resources/react-opanel/src/pages/Locations.jsx` 中的 TWzipcode 初始化邏輯

##### 問題 4：地址選擇器開發

**需求：** 根據郵遞區號 JSON 資料建立原生 HTML datalist 地址選擇器

**開發過程：**
1. 將 `docs/103.12.25-臺灣地區郵遞區號前3碼一覽表.txt` 轉換為 JSON 格式
2. 建立 `public/js/data/taiwan-zipcode-data.json`
3. 開發 `public/js/taiwan-address-selector.js` 類別
4. 整合到 `Locations.jsx`

**功能特點：**
- 三層聯動：城市 → 行政區 → 郵遞區號
- 使用原生 HTML5 datalist
- 支援自訂 CSS class 和外觀
- 支援 onChange 回呼函數

**整合問題：**
- 初始化時機：需要等待 DOM 渲染完成
- 腳本載入時機：需要確保 `TaiwanAddressSelector` 已載入
- 編輯模式：需要正確設定預設值

**解決方案：**
- 使用 `useEffect` 監聽 `showForm` 狀態
- 使用遞迴函數 `initAddressSelector` 檢查容器和腳本是否就緒
- 使用 `setTimeout` 延遲初始化，確保 DOM 已渲染
- 編輯時使用 `setTimeout` 鏈式設定值（城市 → 行政區 → 郵遞區號）

**檔案位置：**
- `public/js/taiwan-address-selector.js` - 地址選擇器類別
- `public/js/data/taiwan-zipcode-data.json` - 郵遞區號資料
- `docs/taiwan-address-selector-usage.md` - 使用說明文件

##### 問題 5：狀態顯示文字配色問題

**問題描述：** 狀態 badge 的文字顏色在深色背景上不夠清晰

**嘗試的解決方案：**
1. 第一次修正：使用 `text-white`（用戶反映仍不清晰）
2. 第二次修正：改用 Tabler 的 `text-{color}-fg` 類別（不符合需求）
3. 最終修正：改回 `text-white`（符合需求）

**最終解決方案：**
- 所有狀態 badge 使用 `bg-{color} text-white`
- 確保在深色背景上文字清晰可見

**修正的檔案：**
- `resources/react-opanel/src/pages/Locations.jsx`
- `resources/react-opanel/src/pages/ExternalLinks.jsx`
- `resources/react-opanel/src/pages/MediaLibrary.jsx`
- `resources/react-opanel/src/pages/AdminUsers.jsx`

---

### 5. 遺漏或需要改進的程序

#### 5.1 測試

**遺漏項目：**
- ❌ 未建立 PHP 單元測試
- ❌ 未建立 React 組件測試
- ❌ 未進行整合測試

**建議：**
- 建立 `tests/` 目錄結構
- 使用 PHPUnit 測試後端 API
- 使用 Vitest/RTL 測試 React 組件

#### 5.2 表單驗證

**目前狀態：**
- ✅ 前端有基本的 HTML5 驗證（`required`）
- ⚠️ 缺少後端驗證邏輯的詳細檢查

**建議：**
- 在 `LocationAction` 中加入更嚴格的資料驗證
- 驗證郵遞區號格式
- 驗證經緯度範圍
- 驗證電話號碼格式

#### 5.3 錯誤處理

**目前狀態：**
- ✅ 基本錯誤處理已實作
- ⚠️ 錯誤訊息可能不夠詳細

**建議：**
- 統一錯誤訊息格式
- 提供更友善的錯誤提示
- 記錄錯誤日誌

#### 5.4 效能優化

**目前狀態：**
- ✅ 列表使用分頁
- ⚠️ 地址選擇器 JSON 檔案較大（420 行）

**建議：**
- 考慮將郵遞區號資料載入時機優化
- 考慮使用 CDN 或快取機制
- 考慮實作虛擬滾動（如果列表很長）

#### 5.5 無障礙性（Accessibility）

**目前狀態：**
- ⚠️ 未特別關注無障礙性

**建議：**
- 為表單欄位加入 `aria-label`
- 確保鍵盤導航正常運作
- 確保螢幕閱讀器相容性

#### 5.6 國際化（i18n）

**目前狀態：**
- ⚠️ 所有文字都是中文硬編碼

**建議：**
- 考慮使用 i18n 框架
- 將文字提取到語言檔案

#### 5.7 文件完整性

**已建立：**
- ✅ `docs/locations/schema.md`
- ✅ `docs/locations/plan.md`
- ✅ `docs/taiwan-address-selector-usage.md`
- ✅ `docs/locations/development-notes.md`（本文件）

**建議：**
- 建立 API 文件
- 建立使用者手冊
- 建立部署指南

---

### 6. 重要檔案清單

#### 後端檔案

```
app/
├── Models/
│   └── LocationStoresModel.php
├── Actions/
│   └── Opanel/
│       └── LocationAction.php
├── Routes/
│   └── opanel_locations.php
└── Templates/
    └── opanel/
        └── locations/
            └── index.twig
```

#### 前端檔案

```
resources/react-opanel/
├── src/
│   └── pages/
│       └── Locations.jsx
└── vite.config.js
```

#### 資料檔案

```
public/
├── js/
│   ├── taiwan-address-selector.js
│   └── data/
│       └── taiwan-zipcode-data.json
└── js/opanel/
    └── locations.bundle.js
```

#### 資料庫

```
migrations/
└── Version20251119121547.php
```

#### 文件

```
docs/
├── locations/
│   ├── schema.md
│   ├── plan.md
│   ├── todo.md
│   └── development-notes.md
├── taiwan-address-selector-usage.md
└── 103.12.25-臺灣地區郵遞區號前3碼一覽表.txt
```

---

### 7. 部署檢查清單

**執行前確認：**
- [ ] 資料庫遷移已執行
- [ ] `npm run build` 已執行（React bundle 已產生）
- [ ] 所有路由已正確註冊
- [ ] 權限設定正確（AdminLogMiddleware 等）
- [ ] 環境變數設定正確
- [ ] 靜態資源路徑正確

**部署後測試：**
- [ ] 列表頁面正常顯示
- [ ] 新增功能正常運作
- [ ] 編輯功能正常運作
- [ ] 刪除功能正常運作
- [ ] 狀態切換正常運作
- [ ] 地址選擇器正常運作
- [ ] 表單驗證正常運作

---

### 8. 已知限制

1. **地址選擇器：**
   - 目前只支援台灣地區
   - 郵遞區號資料為靜態 JSON 檔案
   - 如果資料更新，需要手動更新 JSON 檔案

2. **狀態管理：**
   - 狀態切換為循環模式，無法直接指定目標狀態
   - 如果需要直接設定狀態，需要修改 API

3. **分頁：**
   - 目前使用簡單的分頁，未實作跳頁功能
   - 如果資料量很大，可能需要優化

---

### 9. 未來改進建議

1. **功能增強：**
   - 加入地圖顯示功能（使用 `map_link_id`）
   - 加入訂單連結功能（使用 `order_link_id`）
   - 加入批量操作功能
   - 加入匯出功能（CSV/Excel）

2. **使用者體驗：**
   - 加入搜尋功能
   - 加入排序功能
   - 加入篩選功能（依狀態、縣市等）
   - 加入拖曳排序功能（使用 `sort_order`）

3. **技術改進：**
   - 實作單元測試
   - 實作整合測試
   - 加入 API 文件（Swagger/OpenAPI）
   - 優化效能（快取、索引等）

---

## 總結

門市據點管理功能已基本完成，主要功能包括：
- ✅ 完整的 CRUD 操作
- ✅ 狀態管理
- ✅ 地址選擇器整合
- ✅ 響應式 UI

主要遇到的問題都已解決，但仍有改進空間，特別是測試、驗證和文件方面。

