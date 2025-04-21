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
        'dbname'   => getenv('DB_DATABASE') ?: 'your_database',
        'user'     => getenv('DB_USERNAME') ?: 'your_user',
        'password' => getenv('DB_PASSWORD') ?: 'your_password',
        'host'     => getenv('DB_HOST') ?: 'localhost',
        'driver'   => getenv('DB_CONNECTION') ?: 'pdo_mysql',
        'charset'  => 'utf8mb4',
    ]);

    $container->set(Connection::class, $connection);
};
