<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260201095541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update cms_posts type ENUM to include "history"';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        // Add 'history' to the ENUM list
        $this->addSql("ALTER TABLE cms_posts MODIFY COLUMN type ENUM('news', 'article', 'statics', 'history') NOT NULL DEFAULT 'news' COMMENT '文章類型'");
    }

    public function down(Schema $schema): void
    {
        // Revert ENUM list (Warning: This might cause data loss for 'history' items)
        $this->addSql("ALTER TABLE cms_posts MODIFY COLUMN type ENUM('news', 'article', 'statics') NOT NULL DEFAULT 'news' COMMENT '文章類型'");
    }
}
