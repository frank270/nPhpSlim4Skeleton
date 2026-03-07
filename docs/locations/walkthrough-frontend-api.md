# Locations Module Frontend API Walkthrough

## 變更摘要
本次更新完成了「門市據點」模組的前台 API 開發，讓前端頁面可以獲取門市列表、篩選縣市與搜尋關鍵字。

### 新增檔案
- `app/Actions/Front/LocationAction.php`: 處理前台 API 請求的邏輯核心。
- `app/Routes/front_locations.php`: 定義前台路由。

### 修改檔案
- `app/routes.php`: 註冊新的前台 API 路由群組 `/api/front`。

## 功能驗證

### 1. 取得門市列表
**Endpoint:** `GET /api/front/locations`

**測試指令:**
```bash
curl -k "https://ndev.local:8243/api/front/locations"
```

**預期結果:**
回傳 JSON 格式的門市列表，僅包含狀態為 `open` 的門市。

### 2. 依縣市篩選
**Endpoint:** `GET /api/front/locations?county=台北市`

**測試指令:**
```bash
curl -k "https://ndev.local:8243/api/front/locations?county=台北市"
```

### 3. 關鍵字搜尋
**Endpoint:** `GET /api/front/locations?keyword=中山`

**測試指令:**
```bash
curl -k "https://ndev.local:8243/api/front/locations?keyword=中山"
```
搜尋範圍包含：門市名稱、地址、電話。

### 4. 取得單一門市
**Endpoint:** `GET /api/front/locations/{id}`

**測試指令:**
```bash
curl -k "https://ndev.local:8243/api/front/locations/1"
```

### 5. 取得縣市列表
**Endpoint:** `GET /api/front/locations/cities`

**測試指令:**
```bash
curl -k "https://ndev.local:8243/api/front/locations/cities"
```
回傳所有已建立門市的縣市清單，供前端製作篩選選單。

## 下一步
建議前端工程師依據上述 API 進行頁面串接，完成門市據點的最終呈現。
