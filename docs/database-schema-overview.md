# 資料庫結構概覽

**最後更新日期：2026-02-03**

## Migrations 總覽

本專案使用 Doctrine Migrations 管理資料庫結構,共有 **12 個 Migration 檔案**。

---

## 1. 外部連結模組

### Migration: Version20251114065420
### 資料表: `external_links`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `name` | VARCHAR(255) | 連結名稱 | NOT NULL |
| `url` | VARCHAR(512) | 連結網址 | NOT NULL |
| `description` | TEXT | 連結描述 | NULLABLE |
| `status` | ENUM('active','inactive') | 狀態 | DEFAULT 'active' |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)

---

## 2. 媒體資源庫

### Migration: Version20251114065631
### 資料表: `media_assets`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `filename` | VARCHAR(255) | 檔案名稱 | NOT NULL |
| `original_name` | VARCHAR(255) | 原始檔名 | NOT NULL |
| `file_path` | VARCHAR(512) | 檔案路徑 | NOT NULL |
| `file_size` | BIGINT | 檔案大小 (bytes) | NOT NULL |
| `mime_type` | VARCHAR(100) | MIME 類型 | NOT NULL |
| `alt_text` | VARCHAR(255) | 替代文字 | NULLABLE |
| `caption` | TEXT | 圖片說明 | NULLABLE |
| `status` | ENUM('active','inactive') | 狀態 | DEFAULT 'active' |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- INDEX `idx_status` (`status`)
- INDEX `idx_mime_type` (`mime_type`)

**儲存路徑規則:** `public/upload/{Y}/{m}/uuid.ext`

---

## 3. CMS 內容區塊

### Migration: Version20251114065849
### 資料表: `cms_content_blocks`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `route` | VARCHAR(100) | 路由名稱 | NOT NULL |
| `section` | VARCHAR(100) | 區塊名稱 | NOT NULL |
| `type` | ENUM('text','html','image','media') | 內容類型 | NOT NULL |
| `locale` | VARCHAR(10) | 語系 | DEFAULT 'zh_TW' |
| `content` | LONGTEXT | 內容 | NULLABLE |
| `meta` | JSON | 額外資料 | NULLABLE |
| `status` | ENUM('draft','published') | 狀態 | DEFAULT 'draft' |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `unique_block` (`route`, `section`, `locale`)
- INDEX `idx_status` (`status`)

**使用情境:** 基於 route/section/type/locale 管理頁面內容區塊

---

## 4. 門市據點

### Migration: Version20251119121547
### 資料表: `location_stores`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `name` | VARCHAR(255) | 門市名稱 | NOT NULL |
| `phone` | VARCHAR(50) | 電話 | NULLABLE |
| `county` | VARCHAR(50) | 縣市 | NOT NULL |
| `district` | VARCHAR(50) | 行政區 | NOT NULL |
| `zipcode` | VARCHAR(10) | 郵遞區號 | NOT NULL |
| `address` | VARCHAR(512) | 完整地址 | NOT NULL |
| `latitude` | DECIMAL(10,7) | 緯度 | NULLABLE |
| `longitude` | DECIMAL(10,7) | 經度 | NULLABLE |
| `map_link_id` | BIGINT | 地圖連結 ID | NULLABLE, FK |
| `order_link_id` | BIGINT | 點餐連結 ID | NULLABLE, FK |
| `notes` | TEXT | 備註 | NULLABLE |
| `status` | ENUM('open','pause','closed') | 營業狀態 | DEFAULT 'open' |
| `sort_order` | INT | 排序 | DEFAULT 0 |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- INDEX `idx_county` (`county`)
- INDEX `idx_status` (`status`)
- INDEX `idx_sort_order` (`sort_order`)
- FOREIGN KEY `fk_map_link` (`map_link_id`) REFERENCES `external_links`(`id`)
- FOREIGN KEY `fk_order_link` (`order_link_id`) REFERENCES `external_links`(`id`)

---

## 5. FAQ 模組

### Migration: Version20251119142345

#### 資料表: `faq_categories`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `name` | VARCHAR(255) | 分類名稱 | NOT NULL |
| `slug` | VARCHAR(255) | 網址代稱 | NOT NULL, UNIQUE |
| `description` | TEXT | 分類描述 | NULLABLE |
| `sort_order` | INT | 排序 | DEFAULT 0 |
| `status` | ENUM('active','inactive') | 狀態 | DEFAULT 'active' |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`slug`)
- INDEX `idx_sort_order` (`sort_order`)

#### 資料表: `faqs`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `category_id` | BIGINT | 分類 ID | NULLABLE, FK |
| `question` | VARCHAR(512) | 問題 | NOT NULL |
| `answer` | TEXT | 答案 | NOT NULL |
| `is_featured` | TINYINT(1) | 是否精選 | DEFAULT 0 |
| `sort_order` | INT | 排序 | DEFAULT 0 |
| `status` | ENUM('draft','published') | 狀態 | DEFAULT 'draft' |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- INDEX `idx_category` (`category_id`)
- INDEX `idx_featured` (`is_featured`)
- INDEX `idx_status` (`status`)
- FOREIGN KEY `fk_category` (`category_id`) REFERENCES `faq_categories`(`id`)

---

## 6. 菜單模組

### Migration: Version20251215075528
#### 資料表: `menu_categories`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `name` | VARCHAR(255) | 分類名稱 | NOT NULL |
| `slug` | VARCHAR(255) | 網址代稱 | NOT NULL, UNIQUE |
| `description` | TEXT | 分類描述 | NULLABLE |
| `sort_order` | INT | 排序 | DEFAULT 0 |
| `status` | ENUM('active','inactive') | 狀態 | DEFAULT 'active' |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`slug`)

### Migration: Version20251215143000
#### 資料表: `menu_items`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `category_id` | BIGINT | 分類 ID | NOT NULL, FK |
| `name` | VARCHAR(255) | 商品名稱 | NOT NULL |
| `slug` | VARCHAR(255) | 網址代稱 | NOT NULL, UNIQUE |
| `description` | TEXT | 商品描述 | NULLABLE |
| `image_id` | BIGINT | 圖片 ID | NULLABLE, FK |
| `price` | DECIMAL(10,2) | 原價 | NOT NULL |
| `sale_price` | DECIMAL(10,2) | 優惠價 | NULLABLE |
| `tags` | VARCHAR(512) | 標籤 (逗號分隔) | NULLABLE |
| `is_best_seller` | TINYINT(1) | 是否熱銷 | DEFAULT 0 |
| `status` | ENUM('draft','published','archived') | 狀態 | DEFAULT 'draft' |
| `sort_order` | INT | 排序 | DEFAULT 0 |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`slug`)
- INDEX `idx_category` (`category_id`)
- INDEX `idx_status` (`status`)
- INDEX `idx_best_seller` (`is_best_seller`)
- FOREIGN KEY `fk_category` (`category_id`) REFERENCES `menu_categories`(`id`)
- FOREIGN KEY `fk_image` (`image_id`) REFERENCES `media_assets`(`id`)

---

## 7. 食品安全檢驗

### Migration: Version20251216064000

#### 資料表: `food_safety_categories`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `name` | VARCHAR(255) | 分類名稱 | NOT NULL |
| `slug` | VARCHAR(255) | 網址代稱 | NOT NULL, UNIQUE |
| `sort_order` | INT | 排序 | DEFAULT 0 |
| `status` | ENUM('active','inactive') | 狀態 | DEFAULT 'active' |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

#### 資料表: `food_safety_items`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `category_id` | BIGINT | 分類 ID | NOT NULL, FK |
| `title` | VARCHAR(255) | 報告標題 | NOT NULL |
| `inspection_date` | DATE | 檢驗日期 | NOT NULL |
| `batch_number` | VARCHAR(100) | 批號 | NULLABLE |
| `file_path` | VARCHAR(512) | 檔案路徑 | NOT NULL |
| `is_featured` | TINYINT(1) | 是否精選 | DEFAULT 0 |
| `status` | ENUM('active','inactive') | 狀態 | DEFAULT 'active' |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- INDEX `idx_category` (`category_id`)
- INDEX `idx_featured` (`is_featured`)
- FOREIGN KEY `fk_category` (`category_id`) REFERENCES `food_safety_categories`(`id`)

---

## 8. 加盟洽詢

### Migration: Version20251217060730
### 資料表: `franchise_inquiries`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `name` | VARCHAR(255) | 姓名 | NOT NULL |
| `email` | VARCHAR(255) | Email | NOT NULL |
| `phone` | VARCHAR(50) | 電話 | NOT NULL |
| `subject` | VARCHAR(255) | 主題 | NOT NULL |
| `message` | TEXT | 訊息內容 | NOT NULL |
| `status` | ENUM('pending','processing','completed','rejected') | 狀態 | DEFAULT 'pending' |
| `notes` | TEXT | 備註 | NULLABLE |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- INDEX `idx_status` (`status`)
- INDEX `idx_email` (`email`)

---

## 9. CMS 文章/品牌歷程

### Migration: Version20260201095541
### 資料表: `cms_posts`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `type` | ENUM('article','history') | 文章類型 | DEFAULT 'article' |
| `title` | VARCHAR(255) | 標題 | NOT NULL |
| `slug` | VARCHAR(255) | 網址代稱 | NOT NULL, UNIQUE |
| `cover_image` | VARCHAR(512) | 封面圖路徑 | NULLABLE |
| `content` | LONGTEXT | 內文 (HTML) | NULLABLE |
| `status` | ENUM('draft','published') | 狀態 | DEFAULT 'draft' |
| `published_at` | DATETIME | 發布時間 | NULLABLE |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`slug`)
- INDEX `idx_type` (`type`)
- INDEX `idx_status` (`status`)

**使用情境:**
- `type='article'`: 一般文章
- `type='history'`: 品牌歷程時間軸事件

---

## 10. 聯絡我們

### Migration: Version20260203162500
#### 資料表: `contact_messages`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `name` | VARCHAR(255) | 姓名 | NOT NULL |
| `email` | VARCHAR(255) | Email | NOT NULL |
| `phone` | VARCHAR(50) | 電話 | NULLABLE |
| `subject` | VARCHAR(255) | 主題 | NOT NULL |
| `message` | TEXT | 訊息內容 | NOT NULL |
| `status` | ENUM('new','read','replied') | 狀態 | DEFAULT 'new' |
| `created_at` | DATETIME | 建立時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- INDEX `idx_status` (`status`)

### Migration: Version20260203173500
#### 資料表: `contact_settings`

| 欄位名稱 | 型別 | 說明 | 備註 |
|---------|------|------|------|
| `id` | BIGINT | 主鍵 | AUTO_INCREMENT |
| `key` | VARCHAR(100) | 設定鍵 | NOT NULL, UNIQUE |
| `value` | TEXT | 設定值 | NULLABLE |
| `created_at` | DATETIME | 建立時間 | NOT NULL |
| `updated_at` | DATETIME | 更新時間 | NOT NULL |

**索引:**
- PRIMARY KEY (`id`)
- UNIQUE KEY (`key`)

---

## 資料表關聯圖

```
external_links (外部連結)
    ↑
    |
location_stores (門市據點)
    - map_link_id → external_links.id
    - order_link_id → external_links.id

media_assets (媒體資源庫)
    ↑
    |
menu_items (菜單商品)
    - image_id → media_assets.id

faq_categories (FAQ 分類)
    ↑
    |
faqs (FAQ 問題)
    - category_id → faq_categories.id

menu_categories (菜單分類)
    ↑
    |
menu_items (菜單商品)
    - category_id → menu_categories.id

food_safety_categories (食品安全分類)
    ↑
    |
food_safety_items (食品安全報告)
    - category_id → food_safety_categories.id
```

---

## 總計

- **資料表總數:** 17 個
- **Migration 檔案:** 12 個
- **外鍵關聯:** 7 個
- **索引總數:** 40+ 個

---

## 字元編碼設定

所有資料表統一使用:
- **字元集:** utf8mb4
- **排序規則:** utf8mb4_general_ci

確保正確處理中文與 Emoji 等多字節字元。
