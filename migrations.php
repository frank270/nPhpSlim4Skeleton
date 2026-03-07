<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

if (class_exists(Dotenv\Dotenv::class)) {
    Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
}

return [
    'table_storage' => [
        'table_name' => $_ENV['DB_MIGRATIONS_TABLE'] ?? 'doctrine_migration_versions',
    ],
    'migrations_paths' => [
        'App\\Migrations' => __DIR__ . '/migrations',
    ],
    'all_or_nothing' => true,
    'transactional' => true,
];
