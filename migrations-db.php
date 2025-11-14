<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

if (class_exists(Dotenv\Dotenv::class)) {
    Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
}

return [
    'driver' => $_ENV['DB_CONNECTION'] ?? 'pdo_mysql',
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => (int)($_ENV['DB_PORT'] ?? 3306),
    'dbname' => $_ENV['DB_DATABASE'] ?? '',
    'user' => $_ENV['DB_USERNAME'] ?? '',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
];
