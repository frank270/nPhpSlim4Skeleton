<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add menu page content blocks (hero, section title)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        $blocks = [
            // Hero Section
            ['group_name' => '菜單 Hero', 'short_code' => 'menu_hero_marquee',  'type' => 'raw_text', 'content' => '1F BREAFAST'],
            ['group_name' => '菜單 Hero', 'short_code' => 'menu_hero_title',    'type' => 'raw_text', 'content' => '壹樓早餐'],
            ['group_name' => '菜單 Hero', 'short_code' => 'menu_hero_subtitle', 'type' => 'raw_text', 'content' => '愛美味 吃壹樓'],
            ['group_name' => '菜單 Hero', 'short_code' => 'menu_hero_btn_text', 'type' => 'raw_text', 'content' => '現在就點餐'],
            ['group_name' => '菜單 Hero', 'short_code' => 'menu_hero_btn_url',  'type' => 'raw_text', 'content' => 'https://www.yovvip.com/ORD/ORDStores?appbar=f&c=MzM1'],
            ['group_name' => '菜單 Hero', 'short_code' => 'menu_hero_badge',    'type' => 'raw_text', 'content' => '50% off'],
            ['group_name' => '菜單 Hero', 'short_code' => 'menu_hero_image',    'type' => 'image',    'content' => '/upload/website/p.1_773x620.png'],

            // Section Title
            ['group_name' => '菜單 區塊', 'short_code' => 'menu_section_subtitle', 'type' => 'raw_text', 'content' => 'Menu'],
            ['group_name' => '菜單 區塊', 'short_code' => 'menu_section_title',    'type' => 'raw_text', 'content' => '美味餐點'],
        ];

        foreach ($blocks as $block) {
            $this->connection->insert('page_content_blocks', [
                'page_link'  => '/menu',
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
            'menu_hero_marquee', 'menu_hero_title', 'menu_hero_subtitle',
            'menu_hero_btn_text', 'menu_hero_btn_url', 'menu_hero_badge', 'menu_hero_image',
            'menu_section_subtitle', 'menu_section_title',
        ];

        foreach ($codes as $code) {
            $this->connection->delete('page_content_blocks', ['short_code' => $code]);
        }
    }
}
