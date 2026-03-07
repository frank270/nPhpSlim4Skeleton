<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217060730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 1. Create cms_posts table
        $this->addSql('CREATE TABLE cms_posts (
            id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL COMMENT "文章標題",
            slug VARCHAR(255) NOT NULL COMMENT "網址代稱",
            cover_image VARCHAR(255) DEFAULT NULL COMMENT "列表封面圖",
            content LONGTEXT DEFAULT NULL COMMENT "Quill HTML 內容",
            tags TEXT DEFAULT NULL COMMENT "標籤 (逗號分隔)",
            type ENUM("news","article","statics") NOT NULL DEFAULT "news" COMMENT "文章類型",
            status ENUM("draft","published") NOT NULL DEFAULT "draft" COMMENT "狀態",
            sort_order INT NOT NULL DEFAULT 0 COMMENT "排序(置頂用)",
            published_at DATETIME DEFAULT NULL COMMENT "發布時間",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE INDEX UNIQ_SLUG (slug),
            INDEX IDX_TYPE (type),
            INDEX IDX_STATUS (status),
            INDEX IDX_PUBLISHED_AT (published_at),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // 2. Drop legacy tables if they exist
        // Note: Pivot tables must be dropped first due to Foreign Keys
        $this->addSql('DROP TABLE IF EXISTS cms_content_category');
        $this->addSql('DROP TABLE IF EXISTS cms_content_tag');
        $this->addSql('DROP TABLE IF EXISTS cms_contents');
        $this->addSql('DROP TABLE IF EXISTS cms_categories');
        $this->addSql('DROP TABLE IF EXISTS cms_tags');
        $this->addSql('DROP TABLE IF EXISTS cms_content_types');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cms_posts');
        // We do not recreate legacy tables in down() to simplify logic, 
        // assuming this is a destructive refactor.
    }
}
