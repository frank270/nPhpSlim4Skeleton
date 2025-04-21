<?php
declare(strict_types=1);

use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use Slim\Views\TwigMiddleware;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

return function (App $app) {
    $container = $app->getContainer();

    // 加入 Twig Middleware（讓 View 可以運作）
    $app->add(TwigMiddleware::createFromContainer($app, 'view'));

    // ✅ 新增 Flash message 中介層（注入 Twig 全域變數）
    $app->add(function (Request $request, RequestHandler $handler) use ($container): Response {
        error_log('🔔 Flash middleware executed');

        /** @var Twig $view */
        $view = $container->get('view');

        /** @var Messages $flash */
        $flash = $container->get('flash');

        $view->getEnvironment()->addGlobal('flash', $flash->getMessages());

        return $handler->handle($request);
    });

    // 加入 Error Middleware（錯誤處理）
    $settings = $container->get('settings');
    $errorMiddleware = new ErrorMiddleware(
        $app->getCallableResolver(),
        $app->getResponseFactory(),
        $settings['displayErrorDetails'] ?? false,
        $settings['logError'] ?? false,
        $settings['logErrorDetails'] ?? false
    );
    $app->add($errorMiddleware);
};
