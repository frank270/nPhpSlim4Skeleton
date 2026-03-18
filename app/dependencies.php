<?php
declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Views\Twig;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Slim\Flash\Messages;
use App\Actions\Opanel\AuthAction;
use App\Utils\PermissionChecker;
use App\Middleware\PermissionMiddleware;
use App\Middleware\AdminLogMiddleware;
use Slim\Psr7\Factory\ResponseFactory;

return function (App $app) {
    /** @var ContainerInterface $container */
    $container = $app->getContainer();

    // I18n Service
    $container->set(\App\Services\I18nService::class, function () {
        $service = new \App\Services\I18nService(__DIR__ . '/../resources/lang');
        
        // 從 Cookie 讀取語系設定，預設為 zh-TW
        $locale = $_COOKIE['i18next'] ?? 'zh-TW';
        
        // 簡單的安全性檢查，避免路徑遍歷
        if (!in_array($locale, ['zh-TW', 'zh-CN', 'en', 'ko'])) {
            $locale = 'zh-TW';
        }
        
        $service->loadTranslations($locale);
        return $service;
    });

    // Twig View
    $container->set('view', function (ContainerInterface $c) {
        $twig = Twig::create(__DIR__ . '/Templates', [
            'cache' => __DIR__ . '/../cache/twig', // 建議開發時設 false，正式上線用快取
            'auto_reload' => true,
        ]);

        // Add t() function to Twig
        $i18n = $c->get(\App\Services\I18nService::class);
        $twig->getEnvironment()->addFunction(new \Twig\TwigFunction('t', function ($key, $replace = []) use ($i18n) {
            return $i18n->t($key, $replace);
        }));

        // Add short_code() function
        $db = $c->get(\Doctrine\DBAL\Connection::class);
        // Avoid re-instantiating if possible, but here it's fine
        $contentBlockModel = new \App\Models\ContentBlockModel($db);
        
        $twig->getEnvironment()->addFunction(new \Twig\TwigFunction('short_code', function ($code) use ($contentBlockModel) {
            $block = $contentBlockModel->findByShortCode($code);
            if (!$block) {
                return '';
            }

            switch ($block['type']) {
                case 'image':
                    return '<img src="' . htmlspecialchars($block['content']) . '" alt="' . htmlspecialchars($code) . '">';
                case 'image_url':
                    return $block['content']; // Raw URL for use in CSS background-image or <img src>
                case 'html':
                    return $block['content']; // Raw HTML
                case 'raw_text':
                default:
                    return nl2br(htmlspecialchars($block['content']));
            }
        }, ['is_safe' => ['html']]));

        return $twig;
    });

    // Monolog Logger
    $container->set('logger', function () {
        $logger = new Logger('slim-app');
        $logFile = __DIR__ . '/../logs/app.log';
        $logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
        return $logger;
    });

    // Flash Messages
    $container->set('flash', function () {
        return new Messages();
    });
    
    // Database
    $container->set(\Doctrine\DBAL\Connection::class, function () {
        return require __DIR__ . '/database.php';
    });
    
    // Response Factory
    $container->set(ResponseFactory::class, function () {
        return new ResponseFactory();
    });
    
    // Permission Checker
    $container->set(PermissionChecker::class, function (ContainerInterface $c) {
        return new PermissionChecker($c);
    });
    
    // Permission Middleware
    $container->set(PermissionMiddleware::class, function (ContainerInterface $c) {
        return new PermissionMiddleware(
            $c->get(PermissionChecker::class),
            $c->get('flash'),
            $c->get(ResponseFactory::class)
        );
    });
    
    // Admin Log Middleware
    $container->set(AdminLogMiddleware::class, function (ContainerInterface $c) {
        return new AdminLogMiddleware($c);
    });
    
    // Actions
    $container->set(AuthAction::class, function ($c) {
        return new AuthAction($c);
    });
    
};
