<?php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Container\ContainerInterface;
use Slim\App;

return function (App $app) {
    /** @var ContainerInterface $container */
    $container = $app->getContainer();

    $capsule = new Capsule;

    $capsule->addConnection([
        'driver'    => getenv('DB_CONNECTION') ?: 'mysql',
        'host'      => getenv('DB_HOST') ?: 'localhost',
        'database'  => getenv('DB_DATABASE') ?: 'your_database',
        'username'  => getenv('DB_USERNAME') ?: 'your_user',
        'password'  => getenv('DB_PASSWORD') ?: 'your_password',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
    ]);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();
};
