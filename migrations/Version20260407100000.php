<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260407100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add contact page icon image shortcodes';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        $blocks = [
            ['group_name' => '聯絡頁 圖示', 'short_code' => 'contact_icon_location', 'type' => 'image', 'content' => ''],
            ['group_name' => '聯絡頁 圖示', 'short_code' => 'contact_icon_email',    'type' => 'image', 'content' => ''],
            ['group_name' => '聯絡頁 圖示', 'short_code' => 'contact_icon_phone',    'type' => 'image', 'content' => ''],
        ];

        foreach ($blocks as $block) {
            $this->connection->insert('page_content_blocks', [
                'page_link'  => '/contact',
                'group_name' => $block['group_name'],
                'short_code' => $block['short_code'],
                'type'       => $block['type'],
                'content'    => $block['content'],
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        foreach (['contact_icon_location', 'contact_icon_email', 'contact_icon_phone'] as $code) {
            $this->connection->delete('page_content_blocks', ['short_code' => $code]);
        }
    }
}
