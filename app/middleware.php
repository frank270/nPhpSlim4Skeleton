<?php
declare(strict_types=1);

use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Slim\Views\TwigMiddleware;

return function (App $app) {
    $container = $app->getContainer();

    // 加入 Twig Middleware（讓 View 可以運作）
    $app->add(TwigMiddleware::createFromContainer($app, 'view'));

    // 加入 Error Middleware（錯誤處理）
    $settings = $container->get('settings');
    $errorMiddleware = new ErrorMiddleware(
        $app->getCallableResolver(),
        $app->getResponseFactory(),
        $settings['displayErrorDetails'] ?? false, // 從 settings.php 控制是否顯示錯誤細節
        $settings['logError'] ?? false,
        $settings['logErrorDetails'] ?? false
    );
    $app->add($errorMiddleware);
};
