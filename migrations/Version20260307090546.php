<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * 將 location_stores 的 map_link_id / order_link_id (bigint FK) 改為 map_url / order_url (varchar 255)
 */
final class Version20260307090546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '將 location_stores 的 map_link_id 與 order_link_id 欄位改為 map_url 與 order_url (varchar 255)';
    }

    public function up(Schema $schema): void
    {
        // 移除舊的 bigint 外鍵欄位
        $this->addSql("ALTER TABLE location_stores DROP COLUMN map_link_id");
        $this->addSql("ALTER TABLE location_stores DROP COLUMN order_link_id");

        // 新增 varchar(255) 網址欄位
        $this->addSql("ALTER TABLE location_stores ADD COLUMN map_url VARCHAR(255) DEFAULT NULL COMMENT '交通地圖網址'");
        $this->addSql("ALTER TABLE location_stores ADD COLUMN order_url VARCHAR(255) DEFAULT NULL COMMENT '線上點餐網址'");
    }

    public function down(Schema $schema): void
    {
        // 移除新的 varchar 欄位
        $this->addSql("ALTER TABLE location_stores DROP COLUMN map_url");
        $this->addSql("ALTER TABLE location_stores DROP COLUMN order_url");

        // 恢復舊的 bigint 欄位
        $this->addSql("ALTER TABLE location_stores ADD COLUMN map_link_id BIGINT UNSIGNED DEFAULT NULL COMMENT '地圖外部連結 ID'");
        $this->addSql("ALTER TABLE location_stores ADD COLUMN order_link_id BIGINT UNSIGNED DEFAULT NULL COMMENT '點餐外部連結 ID'");
    }
}
