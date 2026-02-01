# CMS Database Schema (Simplified)

## `cms_posts`
用於儲存所有最新消息與文章內容。

```sql
CREATE TABLE `cms_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章標題',
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '網址代稱',
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '列表封面圖',
  `content` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Quill HTML 內容',
  `tags` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '標籤 (逗號分隔)',
  `type` enum('news','article','statics','history') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'news' COMMENT '文章類型',
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT '狀態',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序(置頂用)',
  `published_at` datetime DEFAULT NULL COMMENT '發布時間',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_posts_slug_unique` (`slug`),
  KEY `cms_posts_status_index` (`status`),
  KEY `cms_posts_published_at_index` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 圖片處理策略
1. **封面圖 (`cover_image`)**: 
   - 存儲路徑範例: `/upload/cms/covers/2025/12/abc.jpg`
   - 使用現有的 `MediaAssetAction` 或專屬 Upload API。

2. **內文圖片 (Quill Embedded)**:
   - 存儲路徑範例: `/upload/cms/content/2025/12/xyz.jpg`
   - 前端 Quill 編輯器透過 `imageHandler` 上傳圖片，後端回傳 URL，Quill 儲存 `<img src="/upload/..." />` HTML。
