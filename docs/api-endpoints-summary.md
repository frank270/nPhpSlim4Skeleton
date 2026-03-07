# API 端點總覽

**最後更新日期:2026-02-03**

本文件列出所有後台與前台 API 端點,包含請求方法、參數與回應格式。

---

## 標準回應格式

### 成功回應
```json
{
  "success": true,
  "data": {
    // 資料內容
  }
}
```

### 失敗回應
```json
{
  "success": false,
  "message": "錯誤訊息",
  "errors": {
    // 驗證錯誤 (可選)
  }
}
```

---

## 後台 API (Opanel)

### 使用者管理

#### 取得使用者列表
- **端點:** `GET /opanel/users/list`
- **參數:** 
  - `page` (int, optional): 頁碼
  - `limit` (int, optional): 每頁筆數
- **回應:**
```json
{
  "success": true,
  "data": {
    "users": [...],
    "total": 100,
    "page": 1,
    "limit": 20
  }
}
```

#### 新增使用者
- **端點:** `POST /opanel/users/create`
- **參數:**
  - `username` (string, required)
  - `password` (string, required)
  - `display_name` (string, required)
  - `group_code` (string, required)
- **回應:** 標準成功/失敗回應

#### 編輯使用者
- **端點:** `POST /opanel/users/{id}/edit`
- **參數:**
  - `display_name` (string, required)
  - `group_code` (string, required)
- **回應:** 標準成功/失敗回應

#### 重設密碼
- **端點:** `POST /opanel/users/{id}/reset-password`
- **參數:**
  - `new_password` (string, required)
- **回應:** 標準成功/失敗回應

#### 切換狀態
- **端點:** `POST /opanel/users/{id}/toggle-status`
- **回應:** 標準成功/失敗回應

#### 刪除使用者
- **端點:** `DELETE /opanel/users/{id}/delete`
- **回應:** 標準成功/失敗回應

---

### 媒體資源庫

#### 取得媒體列表
- **端點:** `GET /opanel/media-assets/list`
- **參數:**
  - `page` (int, optional)
  - `limit` (int, optional)
  - `status` (string, optional): active/inactive
- **回應:**
```json
{
  "success": true,
  "data": {
    "assets": [
      {
        "id": 1,
        "filename": "uuid.jpg",
        "original_name": "photo.jpg",
        "file_path": "/upload/2026/02/uuid.jpg",
        "file_size": 102400,
        "mime_type": "image/jpeg",
        "alt_text": "替代文字",
        "caption": "圖片說明",
        "status": "active",
        "created_at": "2026-02-03 12:00:00"
      }
    ],
    "total": 50
  }
}
```

#### 上傳檔案
- **端點:** `POST /opanel/media-assets/upload`
- **參數:**
  - `file` (file, required): 上傳檔案
  - `alt_text` (string, optional)
  - `caption` (string, optional)
- **回應:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "file_path": "/upload/2026/02/uuid.jpg",
    "url": "https://example.com/upload/2026/02/uuid.jpg"
  }
}
```

#### 編輯媒體
- **端點:** `POST /opanel/media-assets/{id}/edit`
- **參數:**
  - `alt_text` (string, optional)
  - `caption` (string, optional)
- **回應:** 標準成功/失敗回應

#### 切換狀態
- **端點:** `POST /opanel/media-assets/{id}/toggle-status`
- **回應:** 標準成功/失敗回應

#### 刪除媒體
- **端點:** `DELETE /opanel/media-assets/{id}/delete`
- **回應:** 標準成功/失敗回應

---

### 外部連結

#### 取得連結列表
- **端點:** `GET /opanel/external-links/list`
- **參數:** 同媒體資源庫
- **回應:** 標準列表回應

#### 新增連結
- **端點:** `POST /opanel/external-links/create`
- **參數:**
  - `name` (string, required)
  - `url` (string, required)
  - `description` (string, optional)
- **回應:** 標準成功/失敗回應

#### 編輯連結
- **端點:** `POST /opanel/external-links/{id}/edit`
- **參數:** 同新增連結
- **回應:** 標準成功/失敗回應

#### 切換狀態
- **端點:** `POST /opanel/external-links/{id}/toggle-status`
- **回應:** 標準成功/失敗回應

#### 刪除連結
- **端點:** `DELETE /opanel/external-links/{id}/delete`
- **回應:** 標準成功/失敗回應

---

### 門市據點

#### 取得門市列表
- **端點:** `GET /opanel/locations/list`
- **參數:**
  - `page` (int, optional)
  - `limit` (int, optional)
  - `status` (string, optional): open/pause/closed
- **回應:** 標準列表回應

#### 取得單一門市
- **端點:** `GET /opanel/locations/{id}`
- **回應:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "台北旗艦店",
    "phone": "02-12345678",
    "county": "台北市",
    "district": "信義區",
    "zipcode": "110",
    "address": "信義路五段7號",
    "latitude": 25.033,
    "longitude": 121.565,
    "status": "open",
    "created_at": "2025-11-19 12:00:00"
  }
}
```

#### 新增門市
- **端點:** `POST /opanel/locations/create`
- **參數:**
  - `name` (string, required)
  - `phone` (string, optional)
  - `county` (string, required)
  - `district` (string, required)
  - `zipcode` (string, required)
  - `address` (string, required)
  - `latitude` (decimal, optional)
  - `longitude` (decimal, optional)
  - `notes` (string, optional)
- **回應:** 標準成功/失敗回應

#### 編輯門市
- **端點:** `POST /opanel/locations/{id}/edit`
- **參數:** 同新增門市
- **回應:** 標準成功/失敗回應

#### 切換狀態
- **端點:** `POST /opanel/locations/{id}/toggle-status`
- **說明:** 循環切換 open → pause → closed → open
- **回應:** 標準成功/失敗回應

#### 刪除門市
- **端點:** `DELETE /opanel/locations/{id}/delete`
- **回應:** 標準成功/失敗回應

---

### FAQ 管理

#### 取得 FAQ 列表
- **端點:** `GET /opanel/faqs/list`
- **參數:**
  - `category_id` (int, optional)
  - `status` (string, optional): draft/published
  - `is_featured` (bool, optional)
- **回應:** 標準列表回應

#### 取得分類列表
- **端點:** `GET /opanel/faqs/categories/list`
- **回應:** 標準列表回應

#### 新增 FAQ
- **端點:** `POST /opanel/faqs/create`
- **參數:**
  - `category_id` (int, optional)
  - `question` (string, required)
  - `answer` (string, required)
  - `is_featured` (bool, optional)
  - `sort_order` (int, optional)
- **回應:** 標準成功/失敗回應

#### 編輯 FAQ
- **端點:** `POST /opanel/faqs/{id}/edit`
- **參數:** 同新增 FAQ
- **回應:** 標準成功/失敗回應

#### 切換狀態
- **端點:** `POST /opanel/faqs/{id}/toggle-status`
- **回應:** 標準成功/失敗回應

#### 刪除 FAQ
- **端點:** `DELETE /opanel/faqs/{id}/delete`
- **回應:** 標準成功/失敗回應

---

### 食品安全檢驗

#### 取得分類列表
- **端點:** `GET /opanel/food-safety/categories/list`
- **回應:** 標準列表回應

#### 取得報告列表
- **端點:** `GET /opanel/food-safety/items/list`
- **參數:**
  - `category_id` (int, optional)
  - `is_featured` (bool, optional)
- **回應:** 標準列表回應

#### 新增報告
- **端點:** `POST /opanel/food-safety/items/create`
- **參數:**
  - `category_id` (int, required)
  - `title` (string, required)
  - `inspection_date` (date, required)
  - `batch_number` (string, optional)
  - `file` (file, required): PDF 檔案
  - `is_featured` (bool, optional)
- **回應:** 標準成功/失敗回應

#### 編輯報告
- **端點:** `POST /opanel/food-safety/items/{id}/edit`
- **參數:** 同新增報告 (file 為 optional)
- **回應:** 標準成功/失敗回應

#### 刪除報告
- **端點:** `DELETE /opanel/food-safety/items/{id}/delete`
- **回應:** 標準成功/失敗回應

---

### 菜單管理

#### 取得分類列表
- **端點:** `GET /opanel/menu/categories/list`
- **回應:** 標準列表回應

#### 取得商品列表
- **端點:** `GET /opanel/menu/items/list`
- **參數:**
  - `category_id` (int, optional)
  - `status` (string, optional): draft/published/archived
  - `is_best_seller` (bool, optional)
- **回應:** 標準列表回應

#### 新增商品
- **端點:** `POST /opanel/menu/items/create`
- **參數:**
  - `category_id` (int, required)
  - `name` (string, required)
  - `description` (string, optional)
  - `image_id` (int, optional)
  - `price` (decimal, required)
  - `sale_price` (decimal, optional)
  - `tags` (string, optional): 逗號分隔
  - `is_best_seller` (bool, optional)
- **回應:** 標準成功/失敗回應

#### 編輯商品
- **端點:** `POST /opanel/menu/items/{id}/edit`
- **參數:** 同新增商品
- **回應:** 標準成功/失敗回應

#### 切換狀態
- **端點:** `POST /opanel/menu/items/{id}/toggle-status`
- **回應:** 標準成功/失敗回應

#### 刪除商品
- **端點:** `DELETE /opanel/menu/items/{id}/delete`
- **回應:** 標準成功/失敗回應

---

### 加盟洽詢

#### 取得洽詢列表
- **端點:** `GET /opanel/franchise/inquiries/list`
- **參數:**
  - `status` (string, optional): pending/processing/completed/rejected
- **回應:** 標準列表回應

#### 更新狀態
- **端點:** `POST /opanel/franchise/inquiries/{id}/update-status`
- **參數:**
  - `status` (string, required)
- **回應:** 標準成功/失敗回應

#### 新增備註
- **端點:** `POST /opanel/franchise/inquiries/{id}/add-note`
- **參數:**
  - `note` (string, required)
- **回應:** 標準成功/失敗回應

---

### CMS 內容區塊

#### 取得區塊列表
- **端點:** `GET /opanel/content-blocks/list`
- **參數:**
  - `route` (string, optional)
  - `status` (string, optional): draft/published
- **回應:** 標準列表回應

#### 新增區塊
- **端點:** `POST /opanel/content-blocks/create`
- **參數:**
  - `route` (string, required)
  - `section` (string, required)
  - `type` (string, required): text/html/image/media
  - `locale` (string, optional): 預設 zh_TW
  - `content` (string, required)
- **回應:** 標準成功/失敗回應

#### 編輯區塊
- **端點:** `POST /opanel/content-blocks/{id}/edit`
- **參數:** 同新增區塊
- **回應:** 標準成功/失敗回應

#### 刪除區塊
- **端點:** `DELETE /opanel/content-blocks/{id}/delete`
- **回應:** 標準成功/失敗回應

---

### CMS 文章/品牌歷程

#### 取得文章列表
- **端點:** `GET /opanel/cms/posts/list`
- **參數:**
  - `type` (string, optional): article/history
  - `status` (string, optional): draft/published
- **回應:** 標準列表回應

#### 新增文章
- **端點:** `POST /opanel/cms/posts/create`
- **參數:**
  - `type` (string, required): article/history
  - `title` (string, required)
  - `slug` (string, optional): 自動生成
  - `cover_image` (string, optional)
  - `content` (string, required): HTML
  - `published_at` (datetime, optional)
- **回應:** 標準成功/失敗回應

#### 編輯文章
- **端點:** `POST /opanel/cms/posts/{id}/edit`
- **參數:** 同新增文章
- **回應:** 標準成功/失敗回應

#### 刪除文章
- **端點:** `DELETE /opanel/cms/posts/{id}/delete`
- **回應:** 標準成功/失敗回應

---

## 前台 API

### 門市據點

#### 取得縣市列表
- **端點:** `GET /api/front/locations/cities`
- **回應:**
```json
{
  "success": true,
  "data": [
    {
      "county": "台北市",
      "count": 5
    },
    {
      "county": "新北市",
      "count": 3
    }
  ]
}
```

#### 取得門市列表
- **端點:** `GET /api/front/locations?county={縣市}`
- **參數:**
  - `county` (string, optional): 縣市名稱
- **回應:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "台北旗艦店",
      "phone": "02-12345678",
      "address": "台北市信義區信義路五段7號",
      "latitude": 25.033,
      "longitude": 121.565,
      "status": "open"
    }
  ]
}
```

#### 取得單一門市
- **端點:** `GET /api/front/locations/{id}`
- **回應:** 單一門市詳情

---

### 食品安全檢驗

#### 取得分類列表
- **端點:** `GET /api/front/food-safety/categories`
- **回應:** 分類列表

#### 取得報告列表
- **端點:** `GET /api/front/food-safety/items?category={slug}`
- **參數:**
  - `category` (string, optional): 分類 slug
- **回應:** 報告列表

#### 下載報告
- **端點:** `GET /api/front/food-safety/download/{id}`
- **回應:** PDF 檔案下載

---

### 加盟合作

#### 提交加盟表單
- **端點:** `POST /api/front/franchise/submit`
- **參數:**
  - `name` (string, required)
  - `email` (string, required)
  - `phone` (string, required)
  - `subject` (string, required)
  - `message` (string, required)
- **回應:** 標準成功/失敗回應

---

### 聯絡我們

#### 提交聯絡表單
- **端點:** `POST /api/front/contact/submit`
- **參數:**
  - `name` (string, required)
  - `email` (string, required)
  - `phone` (string, optional)
  - `subject` (string, required)
  - `message` (string, required)
- **回應:** 標準成功/失敗回應

---

## 錯誤代碼

| HTTP 狀態碼 | 說明 |
|-----------|------|
| 200 | 成功 |
| 400 | 請求參數錯誤 |
| 401 | 未授權 (未登入) |
| 403 | 禁止存取 (無權限) |
| 404 | 資源不存在 |
| 413 | 檔案過大 |
| 422 | 驗證失敗 |
| 500 | 伺服器錯誤 |

---

## 請求範例

### 使用 cURL

```bash
# 取得門市列表
curl -X GET "https://example.com/api/front/locations?county=台北市"

# 上傳檔案
curl -X POST "https://example.com/opanel/media-assets/upload" \
  -H "Content-Type: multipart/form-data" \
  -F "file=@photo.jpg" \
  -F "alt_text=照片說明"

# 新增門市
curl -X POST "https://example.com/opanel/locations/create" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "name=台北旗艦店&county=台北市&district=信義區&zipcode=110&address=信義路五段7號"
```

### 使用 JavaScript (Fetch API)

```javascript
// GET 請求
fetch('/api/front/locations?county=台北市')
  .then(response => response.json())
  .then(data => console.log(data));

// POST 請求 (Form Data)
const formData = new URLSearchParams();
formData.append('name', '台北旗艦店');
formData.append('county', '台北市');

fetch('/opanel/locations/create', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/x-www-form-urlencoded'
  },
  body: formData
})
  .then(response => response.json())
  .then(data => console.log(data));

// 檔案上傳
const fileFormData = new FormData();
fileFormData.append('file', fileInput.files[0]);
fileFormData.append('alt_text', '照片說明');

fetch('/opanel/media-assets/upload', {
  method: 'POST',
  body: fileFormData
})
  .then(response => response.json())
  .then(data => console.log(data));
```

---

## 注意事項

1. **Content-Type:** 後台 API 使用 `application/x-www-form-urlencoded`,檔案上傳使用 `multipart/form-data`
2. **認證:** 後台 API 需要登入後才能存取
3. **權限:** 部分 API 需要特定權限
4. **快取:** 前台 API 部分端點有快取,更新後需清除
5. **檔案大小:** 上傳檔案最大 128 MB
