<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create FAQ tables (faq_categories, faqs) for FAQ management
 */
final class Version20251119142345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create FAQ tables (faq_categories, faqs) for FAQ management';
    }

    public function up(Schema $schema): void
    {
        // 建立 faq_categories 表
        $categoriesTable = $schema->createTable('faq_categories');
        $categoriesTable->addColumn('id', 'bigint', [
            'unsigned' => true,
            'autoincrement' => true,
        ]);
        $categoriesTable->addColumn('name', 'string', [
            'length' => 120,
        ]);
        $categoriesTable->addColumn('slug', 'string', [
            'length' => 120,
        ]);
        $categoriesTable->addColumn('sort_order', 'integer', [
            'default' => 0,
        ]);
        $categoriesTable->addColumn('is_active', 'boolean', [
            'default' => true,
        ]);
        $categoriesTable->addColumn('created_at', 'datetime', [
            'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
        $categoriesTable->addColumn('updated_at', 'datetime', [
            'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        $categoriesTable->setPrimaryKey(['id']);
        $categoriesTable->addUniqueIndex(['slug'], 'uniq_faq_categories_slug');
        $categoriesTable->addIndex(['is_active'], 'idx_faq_categories_is_active');
        $categoriesTable->addIndex(['sort_order'], 'idx_faq_categories_sort_order');

        // 建立 faqs 表
        $faqsTable = $schema->createTable('faqs');
        $faqsTable->addColumn('id', 'bigint', [
            'unsigned' => true,
            'autoincrement' => true,
        ]);
        $faqsTable->addColumn('category_id', 'bigint', [
            'unsigned' => true,
            'notnull' => false,
        ]);
        $faqsTable->addColumn('question', 'string', [
            'length' => 255,
        ]);
        $faqsTable->addColumn('answer', 'text', [
            'columnDefinition' => 'LONGTEXT',
        ]);
        $faqsTable->addColumn('is_highlight', 'boolean', [
            'default' => false,
        ]);
        $faqsTable->addColumn('status', 'string', [
            'length' => 20,
            'default' => 'draft',
            'columnDefinition' => "ENUM('draft','published') NOT NULL DEFAULT 'draft'",
        ]);
        $faqsTable->addColumn('sort_order', 'integer', [
            'default' => 0,
        ]);
        $faqsTable->addColumn('created_at', 'datetime', [
            'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
        $faqsTable->addColumn('updated_at', 'datetime', [
            'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        $faqsTable->setPrimaryKey(['id']);
        $faqsTable->addIndex(['category_id'], 'idx_faqs_category_id');
        $faqsTable->addIndex(['status'], 'idx_faqs_status');
        $faqsTable->addIndex(['is_highlight'], 'idx_faqs_is_highlight');
        $faqsTable->addIndex(['sort_order'], 'idx_faqs_sort_order');
        $faqsTable->addForeignKeyConstraint(
            'faq_categories',
            ['category_id'],
            ['id'],
            ['onDelete' => 'SET NULL'],
            'fk_faqs_category_id'
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('faqs');
        $schema->dropTable('faq_categories');
    }
}

