<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260407120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add franchise form background shortcode';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        $this->connection->insert('page_content_blocks', [
            'page_link'  => '/franchise',
            'group_name' => '加盟頁 加盟主心聲',
            'short_code' => 'franchise_form_bg',
            'type'       => 'image_url',
            'content'    => '/assets/wellfood/assets/images/background/booking-table.jpg',
            'status'     => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->connection->delete('page_content_blocks', ['short_code' => 'franchise_form_bg']);
    }
}
