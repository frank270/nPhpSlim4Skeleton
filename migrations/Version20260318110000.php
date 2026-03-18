<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add about page content blocks (intro, counter, history)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        $blocks = [
            // Testimonials / Intro Section
            ['group_name' => 'About 品牌故事', 'short_code' => 'about_intro_image',    'type' => 'image',    'content' => '/assets/img/about_p.1_960x728.png'],
            ['group_name' => 'About 品牌故事', 'short_code' => 'about_intro_subtitle', 'type' => 'raw_text', 'content' => '1F breakfast'],
            ['group_name' => 'About 品牌故事', 'short_code' => 'about_intro_title',    'type' => 'raw_text', 'content' => '用科技打造穩定品質，用心守住每一口美味。'],
            ['group_name' => 'About 品牌故事', 'short_code' => 'about_intro_text',     'type' => 'raw_text', 'content' => "運用科技提升效率、以 SOP 確保品質，\n但始終不忘：早餐，不只是填飽肚子，\n更是一種情感的連結。\n每一份餐點都保留媽媽當年「一份一份做」的心意；\n每一家壹樓門市，都是你早晨放鬆微笑的溫暖角落。"],
            ['group_name' => 'About 品牌故事', 'short_code' => 'about_intro_author',   'type' => 'raw_text', 'content' => 'Hi there！'],
            ['group_name' => 'About 品牌故事', 'short_code' => 'about_intro_role',     'type' => 'raw_text', 'content' => '經典美味盡在壹樓！'],

            // Counter Section
            ['group_name' => 'About 數字統計', 'short_code' => 'about_counter_bg',       'type' => 'image_url', 'content' => '/assets/img/about_p.2_1920x690.png'],
            ['group_name' => 'About 數字統計', 'short_code' => 'about_counter_1_number', 'type' => 'raw_text',  'content' => '20'],
            ['group_name' => 'About 數字統計', 'short_code' => 'about_counter_1_label',  'type' => 'raw_text',  'content' => '店家數'],
            ['group_name' => 'About 數字統計', 'short_code' => 'about_counter_2_number', 'type' => 'raw_text',  'content' => '50'],
            ['group_name' => 'About 數字統計', 'short_code' => 'about_counter_2_label',  'type' => 'raw_text',  'content' => '會員人數'],
            ['group_name' => 'About 數字統計', 'short_code' => 'about_counter_3_number', 'type' => 'raw_text',  'content' => '1000'],
            ['group_name' => 'About 數字統計', 'short_code' => 'about_counter_3_label',  'type' => 'raw_text',  'content' => '服務單數'],

            // History Section Title
            ['group_name' => 'About 里程碑',   'short_code' => 'about_history_subtitle', 'type' => 'raw_text', 'content' => '1F BreakFast'],
            ['group_name' => 'About 里程碑',   'short_code' => 'about_history_title',    'type' => 'raw_text', 'content' => '壹樓早餐里程碑'],
        ];

        foreach ($blocks as $block) {
            $this->connection->insert('page_content_blocks', [
                'page_link'  => '/about',
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
            'about_intro_image', 'about_intro_subtitle', 'about_intro_title',
            'about_intro_text', 'about_intro_author', 'about_intro_role',
            'about_counter_bg', 'about_counter_1_number', 'about_counter_1_label',
            'about_counter_2_number', 'about_counter_2_label',
            'about_counter_3_number', 'about_counter_3_label',
            'about_history_subtitle', 'about_history_title',
        ];

        foreach ($codes as $code) {
            $this->connection->delete('page_content_blocks', ['short_code' => $code]);
        }
    }
}
