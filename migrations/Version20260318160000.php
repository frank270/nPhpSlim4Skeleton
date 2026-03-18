<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add franchise page content blocks (hero, info image, section, contact, form)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        $blocks = [
            // Hero Section
            ['group_name' => '加盟 Hero', 'short_code' => 'franchise_hero_marquee',  'type' => 'raw_text', 'content' => '1F BREAFAST'],
            ['group_name' => '加盟 Hero', 'short_code' => 'franchise_hero_title',    'type' => 'raw_text', 'content' => '壹樓早餐'],
            ['group_name' => '加盟 Hero', 'short_code' => 'franchise_hero_subtitle', 'type' => 'raw_text', 'content' => '愛美味 吃壹樓'],
            ['group_name' => '加盟 Hero', 'short_code' => 'franchise_hero_btn_text', 'type' => 'raw_text', 'content' => '現在就點餐'],
            ['group_name' => '加盟 Hero', 'short_code' => 'franchise_hero_btn_url',  'type' => 'raw_text', 'content' => 'https://www.yovvip.com/ORD/ORDStores?appbar=f&c=MzM1'],
            ['group_name' => '加盟 Hero', 'short_code' => 'franchise_hero_badge',    'type' => 'raw_text', 'content' => 'Yummy'],
            ['group_name' => '加盟 Hero', 'short_code' => 'franchise_hero_image',    'type' => 'image',    'content' => '/assets/img/franchise_p.1_773x620.png'],

            // 加盟流程圖
            ['group_name' => '加盟 流程圖', 'short_code' => 'franchise_info_image', 'type' => 'image', 'content' => '/assets/img/franchise_p.2_1290x1290_加盟.png'],

            // Section Title
            ['group_name' => '加盟 區塊', 'short_code' => 'franchise_section_subtitle', 'type' => 'raw_text', 'content' => '1F BreakFast'],
            ['group_name' => '加盟 區塊', 'short_code' => 'franchise_section_title',    'type' => 'raw_text', 'content' => '加盟合作'],

            // Contact 左側
            ['group_name' => '加盟 聯絡', 'short_code' => 'franchise_contact_subtitle',  'type' => 'raw_text', 'content' => '想加盟壹樓早餐?'],
            ['group_name' => '加盟 聯絡', 'short_code' => 'franchise_contact_title',     'type' => 'raw_text', 'content' => '現在就加入壹樓早餐'],
            ['group_name' => '加盟 聯絡', 'short_code' => 'franchise_contact_phone',     'type' => 'raw_text', 'content' => '+0800-030-909'],
            ['group_name' => '加盟 聯絡', 'short_code' => 'franchise_contact_video_url', 'type' => 'raw_text', 'content' => 'https://www.youtube.com/watch?v=9Y7ma241N8k'],

            // 表單區
            ['group_name' => '加盟 表單', 'short_code' => 'franchise_form_title',    'type' => 'raw_text', 'content' => '填寫加盟表單'],
            ['group_name' => '加盟 表單', 'short_code' => 'franchise_form_subtitle', 'type' => 'raw_text', 'content' => '專人將盡快與您聯繫'],
        ];

        foreach ($blocks as $block) {
            $this->connection->insert('page_content_blocks', [
                'page_link'  => '/franchise',
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
            'franchise_hero_marquee', 'franchise_hero_title', 'franchise_hero_subtitle',
            'franchise_hero_btn_text', 'franchise_hero_btn_url', 'franchise_hero_badge', 'franchise_hero_image',
            'franchise_info_image',
            'franchise_section_subtitle', 'franchise_section_title',
            'franchise_contact_subtitle', 'franchise_contact_title',
            'franchise_contact_phone', 'franchise_contact_video_url',
            'franchise_form_title', 'franchise_form_subtitle',
        ];

        foreach ($codes as $code) {
            $this->connection->delete('page_content_blocks', ['short_code' => $code]);
        }
    }
}
