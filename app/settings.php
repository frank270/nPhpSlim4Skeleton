<?php
declare(strict_types=1);

use DI\Container;

return [
    'container' => (function () {
        $container = new Container();

        // App settings
        $container->set('settings', function () {
            return [
                'displayErrorDetails' => true, // 開發時建議開，正式環境記得關
                'logError'            => true,
                'logErrorDetails'     => true,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => __DIR__ . '/../log/app.log',
                    'level' => \Monolog\Logger::DEBUG,
                ],
                'twig' => [
                    'template_path' => __DIR__ . '/Templates',
                    'cache_path'    => __DIR__ . '/../cache/twig',
                ],
            ];
        });

        return $container;
    })()
];
