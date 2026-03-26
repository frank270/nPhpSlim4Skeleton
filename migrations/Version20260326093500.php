<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326093500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add item_code to menu_items table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('menu_items');
        $table->addColumn('item_code', 'string', ['length' => 50, 'notnull' => false]);
        $table->addUniqueIndex(['item_code'], 'UNIQ_ITEM_CODE');
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('menu_items');
        $table->dropIndex('UNIQ_ITEM_CODE');
        $table->dropColumn('item_code');
    }
}
