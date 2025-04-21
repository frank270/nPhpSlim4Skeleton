<?php
declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use Slim\App;

return function (App $app) {
    /** @var ContainerInterface $container */
    $container = $app->getContainer();

    $connection = DriverManager::getConnection([
        'dbname'   => $_ENV['DB_DATABASE'] ?? 'your_database',
        'user'     => $_ENV['DB_USERNAME'] ?? 'your_user',
        'password' => $_ENV['DB_PASSWORD'] ?? 'your_password',
        'host'     => $_ENV['DB_HOST'] ?? 'localhost',
        'driver'   => $_ENV['DB_CONNECTION'] ?? 'pdo_mysql',
        'charset'  => 'utf8mb4',
        'collate'  => 'utf8mb4_unicode_ci'
    ]);

    $container->set(Connection::class, $connection);
};
