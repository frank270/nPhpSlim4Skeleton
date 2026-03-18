<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add news page content blocks (hero, section title, gallery tabs)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        $blocks = [
            // Hero Section
            ['group_name' => '最新消息 Hero', 'short_code' => 'news_hero_marquee',  'type' => 'raw_text', 'content' => '1F BREAFAST'],
            ['group_name' => '最新消息 Hero', 'short_code' => 'news_hero_title',    'type' => 'raw_text', 'content' => '壹樓早餐'],
            ['group_name' => '最新消息 Hero', 'short_code' => 'news_hero_subtitle', 'type' => 'raw_text', 'content' => '愛美味 吃壹樓'],
            ['group_name' => '最新消息 Hero', 'short_code' => 'news_hero_btn_text', 'type' => 'raw_text', 'content' => '現在就點餐'],
            ['group_name' => '最新消息 Hero', 'short_code' => 'news_hero_btn_url',  'type' => 'raw_text', 'content' => 'https://www.yovvip.com/ORD/ORDStores?appbar=f&c=MzM1'],
            ['group_name' => '最新消息 Hero', 'short_code' => 'news_hero_badge',    'type' => 'raw_text', 'content' => '50% off'],
            ['group_name' => '最新消息 Hero', 'short_code' => 'news_hero_image',    'type' => 'image',    'content' => '/upload/website/p.1_773x620.png'],

            // Section Title
            ['group_name' => '最新消息 區塊', 'short_code' => 'news_section_subtitle', 'type' => 'raw_text', 'content' => 'New'],
            ['group_name' => '最新消息 區塊', 'short_code' => 'news_section_title',    'type' => 'raw_text', 'content' => '最新消息'],

            // Gallery Tabs
            ['group_name' => '最新消息 分類', 'short_code' => 'news_tab_event',  'type' => 'raw_text', 'content' => '活動新品'],
            ['group_name' => '最新消息 分類', 'short_code' => 'news_tab_store',  'type' => 'raw_text', 'content' => '展店消息'],
            ['group_name' => '最新消息 分類', 'short_code' => 'news_tab_member', 'type' => 'raw_text', 'content' => '會員活動'],
        ];

        foreach ($blocks as $block) {
            $this->connection->insert('page_content_blocks', [
                'page_link'  => '/news',
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
            'news_hero_marquee', 'news_hero_title', 'news_hero_subtitle',
            'news_hero_btn_text', 'news_hero_btn_url', 'news_hero_badge', 'news_hero_image',
            'news_section_subtitle', 'news_section_title',
            'news_tab_event', 'news_tab_store', 'news_tab_member',
        ];

        foreach ($codes as $code) {
            $this->connection->delete('page_content_blocks', ['short_code' => $code]);
        }
    }
}
