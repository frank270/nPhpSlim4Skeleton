<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create location_stores table for store locations management
 */
final class Version20251119121547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create location_stores table for store locations management';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('location_stores');
        $table->addColumn('id', 'bigint', [
            'unsigned' => true,
            'autoincrement' => true,
        ]);
        $table->addColumn('name', 'string', [
            'length' => 120,
        ]);
        $table->addColumn('county', 'string', [
            'length' => 80,
            'notnull' => false,
        ]);
        $table->addColumn('district', 'string', [
            'length' => 80,
            'notnull' => false,
        ]);
        $table->addColumn('zipcode', 'string', [
            'length' => 10,
            'notnull' => false,
        ]);
        $table->addColumn('address', 'string', [
            'length' => 255,
            'notnull' => false,
        ]);
        $table->addColumn('phone', 'string', [
            'length' => 30,
            'notnull' => false,
        ]);
        $table->addColumn('latitude', 'decimal', [
            'precision' => 10,
            'scale' => 6,
            'notnull' => false,
        ]);
        $table->addColumn('longitude', 'decimal', [
            'precision' => 10,
            'scale' => 6,
            'notnull' => false,
        ]);
        $table->addColumn('map_link_id', 'bigint', [
            'unsigned' => true,
            'notnull' => false,
        ]);
        $table->addColumn('order_link_id', 'bigint', [
            'unsigned' => true,
            'notnull' => false,
        ]);
        $table->addColumn('status', 'string', [
            'length' => 20,
            'default' => 'open',
            'columnDefinition' => "ENUM('open','pause','closed') NOT NULL DEFAULT 'open'",
        ]);
        $table->addColumn('sort_order', 'integer', [
            'default' => 0,
        ]);
        $table->addColumn('notes', 'text', [
            'notnull' => false,
        ]);
        $table->addColumn('created_at', 'datetime', [
            'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
        ]);
        $table->addColumn('updated_at', 'datetime', [
            'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['county'], 'idx_location_stores_county');
        $table->addIndex(['status'], 'idx_location_stores_status');
        $table->addIndex(['sort_order'], 'idx_location_stores_sort_order');
        $table->addIndex(['created_at'], 'idx_location_stores_created_at');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('location_stores');
    }
}

