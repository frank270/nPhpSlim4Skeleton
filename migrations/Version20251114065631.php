<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114065631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create external_links table for shared link settings';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('external_links');
        $table->addColumn('id', 'bigint', [
            'unsigned' => true,
            'autoincrement' => true,
        ]);
        $table->addColumn('uuid', 'string', [
            'length' => 36,
        ]);
        $table->addColumn('name', 'string', [
            'length' => 120,
        ]);
        $table->addColumn('description', 'text', [
            'notnull' => false,
        ]);
        $table->addColumn('url', 'string', [
            'length' => 255,
        ]);
        $table->addColumn('type', 'string', [
            'length' => 60,
            'default' => 'default',
        ]);
        $table->addColumn('locale', 'string', [
            'length' => 10,
            'notnull' => false,
        ]);
        $table->addColumn('is_active', 'boolean', [
            'default' => true,
        ]);
        $table->addColumn('opened_in_new_tab', 'boolean', [
            'default' => true,
        ]);
        $table->addColumn('metadata', 'json', [
            'notnull' => false,
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
        $table->addUniqueIndex(['uuid'], 'uniq_external_links_uuid');
        $table->addIndex(['type'], 'idx_external_links_type');
        $table->addIndex(['is_active'], 'idx_external_links_is_active');
        $table->addIndex(['created_at'], 'idx_external_links_created_at');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('external_links');
    }
}
