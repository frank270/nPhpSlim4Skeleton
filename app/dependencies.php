<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Views\Twig;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Slim\Flash\Messages;

return function (App $app) {
    /** @var ContainerInterface $container */
    $container = $app->getContainer();

    // Twig View
    $container->set('view', function () {
        return Twig::create(__DIR__ . '/templates', [
            'cache' => __DIR__ . '/../cache/twig', // 建議開發時設 false，正式上線用快取
            'auto_reload' => true,
        ]);
    });

    // Monolog Logger
    $container->set('logger', function () {
        $logger = new Logger('slim-app');
        $logFile = __DIR__ . '/../log/app.log';
        $logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
        return $logger;
    });

    // Flash Messages
    $container->set('flash', function () {
        return new Messages();
    });
};
