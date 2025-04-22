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
use App\Middleware\PermissionMiddleware;

return function (App $app) {
    $container = $app->getContainer();

    // ✅ 正確注入 Flash 實例給 Twig，不提前清空訊息
    $app->add(function (Request $request, RequestHandler $handler) use ($container): Response {
        error_log('🔔 Flash middleware executed');

        /** @var Twig $view */
        $view = $container->get('view');

        /** @var Messages $flash */
        $flash = $container->get('flash');

        // ✅ 正確做法：直接傳遞 flash 物件，而非 getMessages()
        $view->getEnvironment()->addGlobal('flash', $flash);

        return $handler->handle($request);
    });

    // ✅ 權限檢查中介層（放在業務邏輯之上）
    $app->add($container->get(PermissionMiddleware::class));

    // ✅ Twig middleware 放在最底層
    $app->add(TwigMiddleware::createFromContainer($app, 'view'));
};
