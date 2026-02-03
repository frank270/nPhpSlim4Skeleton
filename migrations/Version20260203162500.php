<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203162500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create franchise_inquiries table';
    }

    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE franchise_inquiries (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL DEFAULT '', email VARCHAR(255) NOT NULL DEFAULT '', phone VARCHAR(50) NOT NULL DEFAULT '', subject VARCHAR(255) NOT NULL DEFAULT '', message TEXT DEFAULT NULL, status TINYINT(4) DEFAULT 0 NOT NULL COMMENT '0:Pending, 1:Processed', ip_address VARCHAR(45) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE franchise_inquiries');
    }
}
