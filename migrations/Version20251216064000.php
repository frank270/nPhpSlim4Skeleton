<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251216064000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create food_safety_categories and food_safety_items tables';
    }

    public function up(Schema $schema): void
    {
        // food_safety_categories
        $table = $schema->createTable('food_safety_categories');
        $table->addColumn('id', 'bigint', ['autoincrement' => true, 'unsigned' => true]);
        $table->addColumn('name', 'string', ['length' => 120]);
        $table->addColumn('slug', 'string', ['length' => 120]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('sort_order', 'integer', ['default' => 0]);
        $table->addColumn('is_active', 'boolean', ['default' => true]);
        $table->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'notnull' => false]);
        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['slug']);

        // food_safety_items
        $tableItems = $schema->createTable('food_safety_items');
        $tableItems->addColumn('id', 'bigint', ['autoincrement' => true, 'unsigned' => true]);
        $tableItems->addColumn('category_id', 'bigint', ['unsigned' => true]);
        $tableItems->addColumn('name', 'string', ['length' => 120]); // Title/Report Name
        $tableItems->addColumn('description', 'text', ['notnull' => false]);
        $tableItems->addColumn('image_path', 'string', ['length' => 255, 'notnull' => false]); // Thumbnail
        $tableItems->addColumn('file_path', 'string', ['length' => 255, 'notnull' => false]); // PDF File
        $tableItems->addColumn('report_date', 'date', ['notnull' => false]);
        $tableItems->addColumn('expired_at', 'date', ['notnull' => false]);
        $tableItems->addColumn('tags', 'text', ['notnull' => false]);
        $tableItems->addColumn('is_highlight', 'boolean', ['default' => false]); // Sticky/Top
        $tableItems->addColumn('status', 'string', ['length' => 20, 'default' => 'draft']); // draft, published, archived
        $tableItems->addColumn('published_at', 'datetime', ['notnull' => false]);
        $tableItems->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP']);
        $tableItems->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'notnull' => false]);

        $tableItems->setPrimaryKey(['id']);
        $tableItems->addForeignKeyConstraint('food_safety_categories', ['category_id'], ['id'], ['onDelete' => 'CASCADE']);
        $tableItems->addIndex(['category_id']);
        $tableItems->addIndex(['status']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('food_safety_items');
        $schema->dropTable('food_safety_categories');
    }
}
