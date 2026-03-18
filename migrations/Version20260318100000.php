<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add home page content blocks (hero, brand, banner, testimonials)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        $blocks = [
            // Hero Section
            ['group_name' => '首頁 Hero', 'short_code' => 'home_hero_marquee',    'type' => 'raw_text',  'content' => '1F BREAFAST'],
            ['group_name' => '首頁 Hero', 'short_code' => 'home_hero_title',      'type' => 'raw_text',  'content' => '壹樓早餐'],
            ['group_name' => '首頁 Hero', 'short_code' => 'home_hero_subtitle',   'type' => 'raw_text',  'content' => '愛美味 吃壹樓'],
            ['group_name' => '首頁 Hero', 'short_code' => 'home_hero_btn_text',   'type' => 'raw_text',  'content' => '現在就點餐'],
            ['group_name' => '首頁 Hero', 'short_code' => 'home_hero_btn_url',    'type' => 'raw_text',  'content' => 'https://www.yovvip.com/ORD/ORDStores?appbar=f&c=MzM1'],
            ['group_name' => '首頁 Hero', 'short_code' => 'home_hero_badge',      'type' => 'raw_text',  'content' => 'Yummy'],
            ['group_name' => '首頁 Hero', 'short_code' => 'home_hero_image',      'type' => 'image',     'content' => '/upload/website/home_p.1_773x620.png'],

            // 品牌使命願景 Section
            ['group_name' => '首頁 品牌', 'short_code' => 'home_brand_subtitle',           'type' => 'raw_text', 'content' => '壹樓早餐'],
            ['group_name' => '首頁 品牌', 'short_code' => 'home_brand_title',              'type' => 'raw_text', 'content' => '品牌使命與願景'],
            ['group_name' => '首頁 品牌', 'short_code' => 'home_brand_mission_title',      'type' => 'raw_text', 'content' => '使命 Mission'],
            ['group_name' => '首頁 品牌', 'short_code' => 'home_brand_mission_text',       'type' => 'raw_text', 'content' => '打造良好的加盟環境，讓創業更安心，共創長遠發展'],
            ['group_name' => '首頁 品牌', 'short_code' => 'home_brand_persistence_title',  'type' => 'raw_text', 'content' => '堅持 Persistence'],
            ['group_name' => '首頁 品牌', 'short_code' => 'home_brand_persistence_text',   'type' => 'raw_text', 'content' => '堅持品質與創新科技，帶來穩定美味與高效體驗。'],
            ['group_name' => '首頁 品牌', 'short_code' => 'home_brand_vision_title',       'type' => 'raw_text', 'content' => '願景 Vision'],
            ['group_name' => '首頁 品牌', 'short_code' => 'home_brand_vision_text',        'type' => 'raw_text', 'content' => '建立穩定成長的連鎖餐飲平台，成為台灣科技早餐的領導品牌'],

            // Category Banner
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_about_label',      'type' => 'raw_text',  'content' => 'About Us'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_about_title',      'type' => 'raw_text',  'content' => '關於我們'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_about_image',      'type' => 'image_url', 'content' => '/upload/website/home_p.2_230x230.png'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_menu_label',       'type' => 'raw_text',  'content' => 'Menu'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_menu_title',       'type' => 'raw_text',  'content' => '美味菜單'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_menu_image',       'type' => 'image_url', 'content' => '/upload/website/home_p.3_230x230.png'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_news_label',       'type' => 'raw_text',  'content' => 'News'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_news_title',       'type' => 'raw_text',  'content' => '最新消息'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_news_image',       'type' => 'image_url', 'content' => '/upload/website/home_p.4_230x230.png'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_locations_label',  'type' => 'raw_text',  'content' => 'Locations'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_locations_title',  'type' => 'raw_text',  'content' => '門市據點'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_locations_image',  'type' => 'image_url', 'content' => '/upload/website/home_p.5_230x230.png'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_franchise_label',  'type' => 'raw_text',  'content' => 'Franchise'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_franchise_title',  'type' => 'raw_text',  'content' => '加盟合作'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_franchise_image',  'type' => 'image_url', 'content' => '/upload/website/home_p.6_230x230.png'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_contact_label',    'type' => 'raw_text',  'content' => 'Contact Us'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_contact_title',    'type' => 'raw_text',  'content' => '聯絡我們'],
            ['group_name' => '首頁 Banner', 'short_code' => 'home_banner_contact_image',    'type' => 'image_url', 'content' => '/upload/website/home_p.7_230x230.png'],

            // Testimonials Section
            ['group_name' => '首頁 Testimonials', 'short_code' => 'home_testimonial_subtitle',    'type' => 'raw_text', 'content' => '1F breakfast'],
            ['group_name' => '首頁 Testimonials', 'short_code' => 'home_testimonial_title',       'type' => 'raw_text', 'content' => '用科技打造穩定品質，用心守住每一口美味。'],
            ['group_name' => '首頁 Testimonials', 'short_code' => 'home_testimonial_left_image',  'type' => 'image',    'content' => '/upload/website/home_p.8_960x728.png'],
            ['group_name' => '首頁 Testimonials', 'short_code' => 'home_testimonial_1_text',      'type' => 'raw_text', 'content' => "運用科技提升效率、以 SOP 確保品質，\n但始終不忘：早餐，不只是填飽肚子，\n更是一種情感的連結。\n每一份餐點都保留媽媽當年「一份一份做」的心意；\n每一家壹樓門市，都是你早晨放鬆微笑的溫暖角落。"],
            ['group_name' => '首頁 Testimonials', 'short_code' => 'home_testimonial_1_author',    'type' => 'raw_text', 'content' => 'Hi there！'],
            ['group_name' => '首頁 Testimonials', 'short_code' => 'home_testimonial_1_role',      'type' => 'raw_text', 'content' => '經典美味盡在壹樓！'],
        ];

        foreach ($blocks as $block) {
            $this->connection->insert('page_content_blocks', [
                'page_link'  => '/',
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
            'home_hero_marquee', 'home_hero_title', 'home_hero_subtitle',
            'home_hero_btn_text', 'home_hero_btn_url', 'home_hero_badge', 'home_hero_image',
            'home_brand_subtitle', 'home_brand_title',
            'home_brand_mission_title', 'home_brand_mission_text',
            'home_brand_persistence_title', 'home_brand_persistence_text',
            'home_brand_vision_title', 'home_brand_vision_text',
            'home_banner_about_label', 'home_banner_about_title', 'home_banner_about_image',
            'home_banner_menu_label', 'home_banner_menu_title', 'home_banner_menu_image',
            'home_banner_news_label', 'home_banner_news_title', 'home_banner_news_image',
            'home_banner_locations_label', 'home_banner_locations_title', 'home_banner_locations_image',
            'home_banner_franchise_label', 'home_banner_franchise_title', 'home_banner_franchise_image',
            'home_banner_contact_label', 'home_banner_contact_title', 'home_banner_contact_image',
            'home_testimonial_subtitle', 'home_testimonial_title', 'home_testimonial_left_image',
            'home_testimonial_1_text', 'home_testimonial_1_author', 'home_testimonial_1_role',
        ];

        foreach ($codes as $code) {
            $this->connection->delete('page_content_blocks', ['short_code' => $code]);
        }
    }
}
