<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shared Instagram image content blocks (footer gallery)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        $blocks = [
            ['short_code' => 'shared_instagram_1', 'content' => '/upload/website/footer_0.p.1_384x400.png'],
            ['short_code' => 'shared_instagram_2', 'content' => '/upload/website/footer_0.p.2_384x400.png'],
            ['short_code' => 'shared_instagram_3', 'content' => '/upload/website/footer_0.p.3_384x400.png'],
            ['short_code' => 'shared_instagram_4', 'content' => '/upload/website/footer_0.p.4_384x400.png'],
            ['short_code' => 'shared_instagram_5', 'content' => '/upload/website/footer_0.p.5_384x400.png'],
        ];

        foreach ($blocks as $block) {
            $this->connection->insert('page_content_blocks', [
                'page_link'  => null,
                'group_name' => '共用 Instagram',
                'short_code' => $block['short_code'],
                'type'       => 'image_url',
                'content'    => $block['content'],
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        foreach (['shared_instagram_1', 'shared_instagram_2', 'shared_instagram_3', 'shared_instagram_4', 'shared_instagram_5'] as $code) {
            $this->connection->delete('page_content_blocks', ['short_code' => $code]);
        }
    }
}
