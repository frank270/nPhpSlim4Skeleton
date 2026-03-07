<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114065420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create media_assets table for shared media library';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('media_assets');
        $table->addColumn('id', 'bigint', [
            'unsigned' => true,
            'autoincrement' => true,
        ]);
        $table->addColumn('uuid', 'string', [
            'length' => 36,
        ]);
        $table->addColumn('disk', 'string', [
            'length' => 32,
        ]);
        $table->addColumn('path', 'string', [
            'length' => 255,
        ]);
        $table->addColumn('original_name', 'string', [
            'length' => 255,
            'notnull' => false,
        ]);
        $table->addColumn('mime_type', 'string', [
            'length' => 120,
            'notnull' => false,
        ]);
        $table->addColumn('size_bytes', 'bigint', [
            'unsigned' => true,
        ]);
        $table->addColumn('width', 'integer', [
            'unsigned' => true,
            'notnull' => false,
        ]);
        $table->addColumn('height', 'integer', [
            'unsigned' => true,
            'notnull' => false,
        ]);
        $table->addColumn('alt_text', 'string', [
            'length' => 255,
            'notnull' => false,
        ]);
        $table->addColumn('caption', 'text', [
            'notnull' => false,
        ]);
        $table->addColumn('meta', 'json', [
            'notnull' => false,
        ]);
        $table->addColumn('status', 'string', [
            'length' => 20,
            'default' => 'draft',
            'columnDefinition' => "ENUM('draft','active','archived') NOT NULL DEFAULT 'draft'",
        ]);
        $table->addColumn('created_at', 'datetime', [
            'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
        $table->addColumn('updated_at', 'datetime', [
            'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);
        $table->addColumn('deleted_at', 'datetime', [
            'notnull' => false,
        ]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['uuid'], 'uniq_media_assets_uuid');
        $table->addIndex(['status'], 'idx_media_assets_status');
        $table->addIndex(['created_at'], 'idx_media_assets_created_at');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('media_assets');
    }
}
