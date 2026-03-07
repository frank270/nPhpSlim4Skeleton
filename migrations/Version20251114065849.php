<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114065849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create home content tables (home_contents, home_navigation_links, home_media_relations)';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('home_contents')) {
            $homeContents = $schema->createTable('home_contents');
            $homeContents->addColumn('id', 'bigint', [
                'unsigned' => true,
                'autoincrement' => true,
            ]);
            $homeContents->addColumn('hero_title', 'string', [
                'length' => 120,
                'notnull' => false,
            ]);
            $homeContents->addColumn('hero_subtitle', 'string', [
                'length' => 120,
                'notnull' => false,
            ]);
            $homeContents->addColumn('hero_tagline', 'string', [
                'length' => 120,
                'notnull' => false,
            ]);
            $homeContents->addColumn('hero_cta_text', 'string', [
                'length' => 80,
                'notnull' => false,
            ]);
            $homeContents->addColumn('hero_cta_link_id', 'bigint', [
                'unsigned' => true,
                'notnull' => false,
            ]);
            $homeContents->addColumn('mission_title', 'string', [
                'length' => 80,
                'notnull' => false,
            ]);
            $homeContents->addColumn('mission_content', 'text', [
                'notnull' => false,
            ]);
            $homeContents->addColumn('vision_title', 'string', [
                'length' => 80,
                'notnull' => false,
            ]);
            $homeContents->addColumn('vision_content', 'text', [
                'notnull' => false,
            ]);
            $homeContents->addColumn('narrative', 'text', [
                'notnull' => false,
                'columnDefinition' => 'LONGTEXT',
            ]);
            $homeContents->addColumn('status', 'string', [
                'length' => 20,
                'default' => 'draft',
                'columnDefinition' => "ENUM('draft','published') NOT NULL DEFAULT 'draft'",
            ]);
            $homeContents->addColumn('published_at', 'datetime', [
                'notnull' => false,
            ]);
            $homeContents->addColumn('created_at', 'datetime', [
                'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ]);
            $homeContents->addColumn('updated_at', 'datetime', [
                'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ]);
            $homeContents->addColumn('deleted_at', 'datetime', [
                'notnull' => false,
            ]);

            $homeContents->setPrimaryKey(['id']);
            $homeContents->addIndex(['status'], 'idx_home_contents_status');
            $homeContents->addIndex(['published_at'], 'idx_home_contents_published_at');

            $homeContents->addForeignKeyConstraint('external_links', ['hero_cta_link_id'], ['id'], [
                'onDelete' => 'SET NULL',
                'onUpdate' => 'CASCADE',
            ], 'fk_home_contents_cta_link');
        }

        if (!$schema->hasTable('home_navigation_links')) {
            $navLinks = $schema->createTable('home_navigation_links');
            $navLinks->addColumn('id', 'bigint', [
                'unsigned' => true,
                'autoincrement' => true,
            ]);
            $navLinks->addColumn('home_content_id', 'bigint', [
                'unsigned' => true,
            ]);
            $navLinks->addColumn('label', 'string', [
                'length' => 80,
            ]);
            $navLinks->addColumn('target_slug', 'string', [
                'length' => 80,
                'notnull' => false,
            ]);
            $navLinks->addColumn('link_id', 'bigint', [
                'unsigned' => true,
                'notnull' => false,
            ]);
            $navLinks->addColumn('sort_order', 'integer', [
                'default' => 0,
            ]);
            $navLinks->addColumn('is_active', 'boolean', [
                'default' => true,
            ]);
            $navLinks->addColumn('created_at', 'datetime', [
                'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ]);
            $navLinks->addColumn('updated_at', 'datetime', [
                'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ]);
            $navLinks->addColumn('deleted_at', 'datetime', [
                'notnull' => false,
            ]);

            $navLinks->setPrimaryKey(['id']);
            $navLinks->addIndex(['home_content_id'], 'idx_home_navigation_content');
            $navLinks->addIndex(['link_id'], 'idx_home_navigation_link');
            $navLinks->addIndex(['is_active'], 'idx_home_navigation_is_active');
            $navLinks->addIndex(['sort_order'], 'idx_home_navigation_sort');

            $navLinks->addForeignKeyConstraint('home_contents', ['home_content_id'], ['id'], [
                'onDelete' => 'CASCADE',
                'onUpdate' => 'CASCADE',
            ], 'fk_home_navigation_content');
            $navLinks->addForeignKeyConstraint('external_links', ['link_id'], ['id'], [
                'onDelete' => 'SET NULL',
                'onUpdate' => 'CASCADE',
            ], 'fk_home_navigation_link');
        }

        if (!$schema->hasTable('home_media_relations')) {
            $mediaRelations = $schema->createTable('home_media_relations');
            $mediaRelations->addColumn('id', 'bigint', [
                'unsigned' => true,
                'autoincrement' => true,
            ]);
            $mediaRelations->addColumn('home_content_id', 'bigint', [
                'unsigned' => true,
            ]);
            $mediaRelations->addColumn('media_asset_id', 'bigint', [
                'unsigned' => true,
            ]);
            $mediaRelations->addColumn('slot', 'string', [
                'length' => 60,
            ]);
            $mediaRelations->addColumn('sort_order', 'integer', [
                'default' => 0,
            ]);
            $mediaRelations->addColumn('created_at', 'datetime', [
                'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            ]);
            $mediaRelations->addColumn('updated_at', 'datetime', [
                'columnDefinition' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ]);

            $mediaRelations->setPrimaryKey(['id']);
            $mediaRelations->addIndex(['home_content_id'], 'idx_home_media_content');
            $mediaRelations->addIndex(['media_asset_id'], 'idx_home_media_asset');
            $mediaRelations->addIndex(['slot'], 'idx_home_media_slot');

            $mediaRelations->addForeignKeyConstraint('home_contents', ['home_content_id'], ['id'], [
                'onDelete' => 'CASCADE',
                'onUpdate' => 'CASCADE',
            ], 'fk_home_media_content');
            $mediaRelations->addForeignKeyConstraint('media_assets', ['media_asset_id'], ['id'], [
                'onDelete' => 'CASCADE',
                'onUpdate' => 'CASCADE',
            ], 'fk_home_media_asset');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('home_media_relations')) {
            $schema->dropTable('home_media_relations');
        }

        if ($schema->hasTable('home_navigation_links')) {
            $schema->dropTable('home_navigation_links');
        }

        if ($schema->hasTable('home_contents')) {
            $schema->dropTable('home_contents');
        }
    }
}
