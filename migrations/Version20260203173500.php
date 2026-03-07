<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260203173500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create page_content_blocks table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('page_content_blocks');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('page_link', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('group_name', 'string', ['length' => 100, 'notnull' => false]);
        $table->addColumn('short_code', 'string', ['length' => 100]);
        $table->addColumn('type', 'string', ['length' => 20, 'default' => 'raw_text', 'comment' => 'image, html, raw_text']);
        $table->addColumn('content', 'text', ['length' => 4294967295, 'notnull' => false]); // LONGTEXT
        $table->addColumn('status', 'smallint', ['default' => 1]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['short_code']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('page_content_blocks');
    }
}
