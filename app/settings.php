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
                    'path' => __DIR__ . '/../logss/app.log',
                    'level' => \Monolog\Logger::DEBUG,
                ],
                'twig' => [
                    'template_path' => __DIR__ . '/Templates',
                    'cache_path'    => __DIR__ . '/../cache/twig',
                ],
                'smtp' => [
                    'host'       => $_ENV['SMTP_HOST'] ?? 'smtp.dreamhost.com',
                    'port'       => $_ENV['SMTP_PORT'] ?? 587,
                    'username'   => $_ENV['SMTP_USER'] ?? '',
                    'password'   => $_ENV['SMTP_PASS'] ?? '',
                    'secure'     => $_ENV['SMTP_SECURE'] ?? 'tls',
                    'from_email' => $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@example.com',
                    'from_name'  => $_ENV['SMTP_FROM_NAME'] ?? 'Contact Form',
                ],
            ];
        });

        return $container;
    })()
];
