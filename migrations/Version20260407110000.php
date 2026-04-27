<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260407110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add franchise testimonial shortcodes';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        $blocks = [
            ['short_code' => 'franchise_testi_subtitle',    'type' => 'raw_text',  'content' => '加盟主心聲'],
            ['short_code' => 'franchise_testi_title',       'type' => 'raw_text',  'content' => '看看加盟主的心聲'],
            ['short_code' => 'franchise_testi_1_avatar',    'type' => 'image',     'content' => ''],
            ['short_code' => 'franchise_testi_1_text',      'type' => 'raw_text',  'content' => ''],
            ['short_code' => 'franchise_testi_1_author',    'type' => 'raw_text',  'content' => ''],
            ['short_code' => 'franchise_testi_1_designation', 'type' => 'raw_text', 'content' => ''],
            ['short_code' => 'franchise_testi_badge_image', 'type' => 'image',     'content' => ''],
            ['short_code' => 'franchise_testi_bg',          'type' => 'image_url', 'content' => '/assets/wellfood/assets/images/background/testimonials.jpg'],
        ];

        foreach ($blocks as $block) {
            $this->connection->insert('page_content_blocks', [
                'page_link'  => '/franchise',
                'group_name' => '加盟頁 加盟主心聲',
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
        $codes = [
            'franchise_testi_subtitle',
            'franchise_testi_title',
            'franchise_testi_1_avatar',
            'franchise_testi_1_text',
            'franchise_testi_1_author',
            'franchise_testi_1_designation',
            'franchise_testi_badge_image',
            'franchise_testi_bg',
        ];

        foreach ($codes as $code) {
            $this->connection->delete('page_content_blocks', ['short_code' => $code]);
        }
    }
}
