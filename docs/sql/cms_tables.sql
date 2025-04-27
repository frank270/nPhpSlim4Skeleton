-- CMS內容類型表
CREATE TABLE cms_content_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT '內容類型名稱',
    slug VARCHAR(100) NOT NULL COMMENT '內容類型標識',
    description VARCHAR(255) DEFAULT NULL COMMENT '內容類型描述',
    is_active TINYINT(1) DEFAULT 1 COMMENT '是否啟用',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- CMS內容表
CREATE TABLE cms_contents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_type_id INT UNSIGNED NOT NULL COMMENT '內容類型ID',
    title VARCHAR(255) NOT NULL COMMENT '標題',
    slug VARCHAR(255) NOT NULL COMMENT '網址標識',
    content LONGTEXT DEFAULT NULL COMMENT '內容',
    excerpt TEXT DEFAULT NULL COMMENT '摘要',
    thumbnail VARCHAR(255) DEFAULT NULL COMMENT '縮圖路徑',
    meta_title VARCHAR(255) DEFAULT NULL COMMENT 'SEO標題',
    meta_description TEXT DEFAULT NULL COMMENT 'SEO描述',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft' COMMENT '狀態',
    is_featured TINYINT(1) DEFAULT 0 COMMENT '是否精選',
    sort_order INT DEFAULT 0 COMMENT '排序順序',
    author_id INT UNSIGNED DEFAULT NULL COMMENT '作者ID (後台使用者)',
    published_at DATETIME DEFAULT NULL COMMENT '發布時間',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_content_type` (`content_type_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_author` (`author_id`),
    UNIQUE KEY `unique_slug_per_type` (`content_type_id`, `slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- CMS分類表
CREATE TABLE cms_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED DEFAULT NULL COMMENT '父分類ID',
    name VARCHAR(100) NOT NULL COMMENT '分類名稱',
    slug VARCHAR(100) NOT NULL COMMENT '分類標識',
    description TEXT DEFAULT NULL COMMENT '分類描述',
    image VARCHAR(255) DEFAULT NULL COMMENT '分類圖片',
    is_active TINYINT(1) DEFAULT 1 COMMENT '是否啟用',
    sort_order INT DEFAULT 0 COMMENT '排序順序',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_parent` (`parent_id`),
    UNIQUE KEY `unique_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- CMS內容與分類的關聯表
CREATE TABLE cms_content_category (
    content_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (`content_id`, `category_id`),
    INDEX `idx_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- CMS標籤表
CREATE TABLE cms_tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT '標籤名稱',
    slug VARCHAR(100) NOT NULL COMMENT '標籤標識',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- CMS內容與標籤的關聯表
CREATE TABLE cms_content_tag (
    content_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (`content_id`, `tag_id`),
    INDEX `idx_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 初始資料：內容類型
INSERT INTO cms_content_types (name, slug, description) 
VALUES 
('頁面', 'page', '網站靜態頁面'),
('文章', 'article', '部落格文章'),
('區塊', 'block', '可複用的內容區塊');

-- 初始資料：分類
INSERT INTO cms_categories (name, slug, description, is_active, sort_order)
VALUES
('公司新聞', 'company-news', '公司相關新聞與公告', 1, 10),
('產品資訊', 'product-info', '產品相關資訊與更新', 1, 20),
('技術文章', 'tech-articles', '技術相關文章與教學', 1, 30),
('活動報導', 'events', '各種活動的報導與回顧', 1, 40);

-- 添加子分類
INSERT INTO cms_categories (parent_id, name, slug, description, is_active, sort_order)
VALUES
(3, 'Web開發', 'web-development', 'Web開發相關技術文章', 1, 10),
(3, '行動應用', 'mobile-app', '行動應用開發相關文章', 1, 20),
(2, '新產品', 'new-products', '新產品發布資訊', 1, 10),
(2, '產品更新', 'product-updates', '產品更新與改進資訊', 1, 20);

-- 初始資料：標籤
INSERT INTO cms_tags (name, slug)
VALUES
('PHP', 'php'),
('JavaScript', 'javascript'),
('CSS', 'css'),
('React', 'react'),
('Vue', 'vue'),
('MySQL', 'mysql'),
('Docker', 'docker'),
('雲端服務', 'cloud-services'),
('資安', 'security'),
('API', 'api');

-- 初始資料：內容 (頁面)
INSERT INTO cms_contents (content_type_id, title, slug, content, excerpt, meta_title, meta_description, status, is_featured, published_at)
VALUES
(1, '關於我們', 'about-us', '<h1>關於我們</h1><p>我們是一家專注於提供高品質數位解決方案的公司。憑藉多年的行業經驗和專業知識，我們致力於為客戶創造價值。</p><p>我們的團隊由一群充滿熱情的專業人士組成，他們擁有豐富的經驗和創新思維，能夠應對各種挑戰並提供量身定制的解決方案。</p>', '專注於提供高品質數位解決方案的公司', '關於我們 | 公司名稱', '了解更多關於我們公司的資訊，包括我們的使命、願景和價值觀。', 'published', 0, NOW()),
(1, '服務項目', 'services', '<h1>服務項目</h1><p>我們提供多元化的服務，滿足不同客戶的需求。</p><ul><li>網站開發</li><li>行動應用開發</li><li>UI/UX設計</li><li>數位行銷</li><li>系統整合</li></ul>', '我們提供多元化的服務，滿足不同客戶的需求', '服務項目 | 公司名稱', '探索我們提供的各種服務，包括網站開發、行動應用開發、UI/UX設計等。', 'published', 0, NOW()),
(1, '聯絡我們', 'contact-us', '<h1>聯絡我們</h1><p>有任何問題或需求，歡迎與我們聯繫。</p><p>電話：(02) 2345-6789</p><p>Email：info@example.com</p><p>地址：台北市信義區信義路五段7號</p>', '有任何問題或需求，歡迎與我們聯繫', '聯絡我們 | 公司名稱', '聯繫我們以獲取更多資訊或討論您的項目需求。', 'published', 0, NOW());

-- 初始資料：內容 (文章)
INSERT INTO cms_contents (content_type_id, title, slug, content, excerpt, thumbnail, meta_title, meta_description, status, is_featured, author_id, published_at)
VALUES
(2, '如何提升網站性能', 'how-to-improve-website-performance', '<h1>如何提升網站性能</h1><p>網站性能對用戶體驗和SEO有著重要影響。本文將介紹幾種提升網站性能的方法。</p><h2>1. 優化圖片</h2><p>圖片通常是網頁中最大的元素，優化圖片可以顯著提升加載速度。使用適當的圖片格式，如JPEG用於照片，PNG用於需要透明背景的圖像，WebP為現代瀏覽器提供更好的壓縮。</p><h2>2. 使用CDN</h2><p>內容分發網絡(CDN)可以將您的靜態資源分發到全球各地的伺服器，使用戶可以從最近的伺服器加載資源，從而減少延遲。</p><h2>3. 實現瀏覽器快取</h2><p>通過設置適當的HTTP頭，告訴瀏覽器哪些資源可以快取以及快取多長時間，可以減少重複請求。</p>', '本文將介紹幾種提升網站性能的方法，包括圖片優化、使用CDN和實現瀏覽器快取等', '/uploads/images/website-performance.jpg', '如何提升網站性能 | 技術文章', '了解如何通過優化圖片、使用CDN和實現瀏覽器快取等方法提升您的網站性能', 'published', 1, 1, NOW()),
(2, 'PHP 8新特性介紹', 'php-8-new-features', '<h1>PHP 8新特性介紹</h1><p>PHP 8帶來了許多令人興奮的新特性和性能改進。本文將介紹其中幾個重要的特性。</p><h2>1. JIT編譯器</h2><p>PHP 8引入了即時(JIT)編譯器，可以顯著提升某些場景下的性能。JIT編譯器將PHP操作碼轉換為機器碼，減少解釋時間。</p><h2>2. 聯合類型</h2><p>PHP 8允許開發者使用聯合類型聲明，例如 `function foo(int|string $bar)`，使類型系統更加強大和靈活。</p><h2>3. 命名參數</h2><p>命名參數允許您在調用函數時按名稱而不是位置傳遞參數，使代碼更易讀且可以跳過默認參數。</p>', 'PHP 8帶來了許多令人興奮的新特性和性能改進，包括JIT編譯器、聯合類型和命名參數等', '/uploads/images/php8.jpg', 'PHP 8新特性介紹 | 技術文章', '了解PHP 8的新特性，包括JIT編譯器、聯合類型和命名參數等', 'published', 1, 1, NOW()),
(2, '響應式設計最佳實踐', 'responsive-design-best-practices', '<h1>響應式設計最佳實踐</h1><p>隨著移動設備的普及，響應式設計變得尤為重要。本文將分享一些響應式設計的最佳實踐。</p><h2>1. 移動優先設計</h2><p>從移動視圖開始設計，然後逐步擴展到更大的屏幕。這種方法可以確保您的網站在所有設備上都能提供良好的用戶體驗。</p><h2>2. 使用彈性網格</h2><p>使用基於百分比的網格而不是固定像素寬度，可以使布局更加靈活，適應不同的屏幕尺寸。</p><h2>3. 媒體查詢的有效使用</h2><p>媒體查詢允許您基於各種條件（如屏幕寬度、設備類型等）應用不同的樣式。合理使用媒體查詢是實現響應式設計的關鍵。</p>', '本文將分享一些響應式設計的最佳實踐，包括移動優先設計、使用彈性網格和有效使用媒體查詢等', '/uploads/images/responsive-design.jpg', '響應式設計最佳實踐 | 技術文章', '了解響應式設計的最佳實踐，包括移動優先設計、使用彈性網格和有效使用媒體查詢等', 'published', 0, 1, NOW());

-- 初始資料：內容 (區塊)
INSERT INTO cms_contents (content_type_id, title, slug, content, status, sort_order)
VALUES
(3, '頁尾資訊', 'footer-info', '<div class="footer-info"><h3>公司名稱</h3><p>地址：台北市信義區信義路五段7號</p><p>電話：(02) 2345-6789</p><p>Email：info@example.com</p><p>© 2023 版權所有</p></div>', 'published', 10),
(3, '首頁橫幅', 'home-banner', '<div class="banner"><h1>歡迎訪問我們的網站</h1><p>我們提供專業的數位解決方案，助您業務蓬勃發展</p><a href="/contact-us" class="btn">聯絡我們</a></div>', 'published', 20),
(3, '關於我們簡介', 'about-us-brief', '<div class="about-brief"><h2>關於我們</h2><p>我們是一家專注於提供高品質數位解決方案的公司。憑藉多年的行業經驗和專業知識，我們致力於為客戶創造價值。</p><a href="/about-us" class="btn-sm">了解更多</a></div>', 'published', 30);

-- 內容與分類關聯
INSERT INTO cms_content_category (content_id, category_id)
VALUES
(4, 3), -- 如何提升網站性能 -> 技術文章
(4, 5), -- 如何提升網站性能 -> Web開發
(5, 3), -- PHP 8新特性介紹 -> 技術文章
(5, 5), -- PHP 8新特性介紹 -> Web開發
(6, 3), -- 響應式設計最佳實踐 -> 技術文章
(6, 5); -- 響應式設計最佳實踐 -> Web開發

-- 內容與標籤關聯
INSERT INTO cms_content_tag (content_id, tag_id)
VALUES
(4, 2), -- 如何提升網站性能 -> JavaScript
(4, 3), -- 如何提升網站性能 -> CSS
(5, 1), -- PHP 8新特性介紹 -> PHP
(5, 6), -- PHP 8新特性介紹 -> MySQL
(6, 2), -- 響應式設計最佳實踐 -> JavaScript
(6, 3); -- 響應式設計最佳實踐 -> CSS