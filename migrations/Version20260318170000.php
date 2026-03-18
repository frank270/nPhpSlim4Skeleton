<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add faq page content blocks (hero, section title, section image)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        $blocks = [
            // Hero Section
            ['group_name' => 'FAQ Hero', 'short_code' => 'faq_hero_marquee',  'type' => 'raw_text', 'content' => '1F BREAFAST'],
            ['group_name' => 'FAQ Hero', 'short_code' => 'faq_hero_title',    'type' => 'raw_text', 'content' => '壹樓早餐'],
            ['group_name' => 'FAQ Hero', 'short_code' => 'faq_hero_subtitle', 'type' => 'raw_text', 'content' => '愛美味 吃壹樓'],
            ['group_name' => 'FAQ Hero', 'short_code' => 'faq_hero_btn_text', 'type' => 'raw_text', 'content' => '現在就點餐'],
            ['group_name' => 'FAQ Hero', 'short_code' => 'faq_hero_btn_url',  'type' => 'raw_text', 'content' => 'https://www.yovvip.com/ORD/ORDStores?appbar=f&c=MzM1'],
            ['group_name' => 'FAQ Hero', 'short_code' => 'faq_hero_badge',    'type' => 'raw_text', 'content' => 'FAQ'],
            ['group_name' => 'FAQ Hero', 'short_code' => 'faq_hero_image',    'type' => 'image',    'content' => '/assets/wellfood/assets/images/about/faq.jpg'],

            // FAQ Section
            ['group_name' => 'FAQ 區塊', 'short_code' => 'faq_section_subtitle', 'type' => 'raw_text', 'content' => 'FAQS'],
            ['group_name' => 'FAQ 區塊', 'short_code' => 'faq_section_title',    'type' => 'raw_text', 'content' => '常見問題'],
            ['group_name' => 'FAQ 區塊', 'short_code' => 'faq_section_image',    'type' => 'image',    'content' => '/assets/wellfood/assets/images/about/faq.jpg'],
        ];

        foreach ($blocks as $block) {
            $this->connection->insert('page_content_blocks', [
                'page_link'  => '/faq',
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
        $codes = [
            'faq_hero_marquee', 'faq_hero_title', 'faq_hero_subtitle',
            'faq_hero_btn_text', 'faq_hero_btn_url', 'faq_hero_badge', 'faq_hero_image',
            'faq_section_subtitle', 'faq_section_title', 'faq_section_image',
        ];

        foreach ($codes as $code) {
            $this->connection->delete('page_content_blocks', ['short_code' => $code]);
        }
    }
}
