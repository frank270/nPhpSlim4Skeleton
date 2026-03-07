<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251215143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create menu_categories and menu_items tables';
    }

    public function up(Schema $schema): void
    {
        // menu_categories table
        $table = $schema->createTable('menu_categories');
        $table->addColumn('id', 'bigint', ['autoincrement' => true, 'unsigned' => true]);
        $table->addColumn('name', 'string', ['length' => 120]);
        $table->addColumn('slug', 'string', ['length' => 120]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('sort_order', 'integer', ['default' => 0]);
        $table->addColumn('is_active', 'boolean', ['default' => true]); // TinyInt(1) for boolean
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'notnull' => false]);
        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['slug']);

        // menu_items table
        $tableItems = $schema->createTable('menu_items');
        $tableItems->addColumn('id', 'bigint', ['autoincrement' => true, 'unsigned' => true]);
        $tableItems->addColumn('category_id', 'bigint', ['unsigned' => true]);
        $tableItems->addColumn('name', 'string', ['length' => 120]);
        $tableItems->addColumn('description', 'text', ['notnull' => false]);
        $tableItems->addColumn('price_original', 'decimal', ['precision' => 10, 'scale' => 2]);
        $tableItems->addColumn('price_sale', 'decimal', ['precision' => 10, 'scale' => 2, 'notnull' => false]);
        $tableItems->addColumn('media_id', 'bigint', ['unsigned' => true, 'notnull' => false]);
        $tableItems->addColumn('tags', 'text', ['notnull' => false, 'comment' => 'Tags separated by comma']);
        $tableItems->addColumn('is_best_seller', 'boolean', ['default' => false]);
        $tableItems->addColumn('status', 'string', ['length' => 20, 'default' => 'draft']); // draft, published, archived
        $tableItems->addColumn('published_at', 'datetime', ['notnull' => false]);
        $tableItems->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $tableItems->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'notnull' => false]);

        $tableItems->setPrimaryKey(['id']);
        $tableItems->addForeignKeyConstraint('menu_categories', ['category_id'], ['id'], ['onDelete' => 'CASCADE']);
        // Assuming media_assets table exists or similar for media_id, usually loose coupling or FK. 
        // Based on other schemas, usually no strict FK to media assets or it depends on the project. 
        // For now, I'll add an index but strictly adding FK might fail if media table name differs.
        // Let's assume 'media_assets' or just keep it as index.
        // Checking doc/schema.md usually helps but let's stick to simple index for media_id now to be safe.
        $tableItems->addIndex(['category_id']);
        $tableItems->addIndex(['media_id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('menu_items');
        $schema->dropTable('menu_categories');
    }
}
