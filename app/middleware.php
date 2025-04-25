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
use App\Middleware\AdminLogMiddleware;

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
    
    // 注意：後台日誌中間件已在 routes.php 中註冊，這裡不需要重複註冊

    // 加入一個過濾靜態資源 404 日誌的中介層
    $app->add(function (Request $request, RequestHandler $handler) use ($container) {
        try {
            return $handler->handle($request);
        } catch (\Slim\Exception\HttpNotFoundException $e) {
            // 只有在靜態資源請求時才忽略 404 錯誤
            $uri = $request->getUri()->getPath();
            if (preg_match('/\.(css|js|html|scss|png|jpe?g|webp|gif|bmp|svg|ico|woff2?|ttf|otf|map)$/i', $uri)) {
                // 靜態資源的 404 錯誤，直接回傳 404 而不記錄
                return new \Slim\Psr7\Response(404);
            }
            // 非靜態資源的 404 錯誤，繼續拋出
            throw $e;
        }
    });
    
    // ✅ Twig middleware 放在最底層
    $app->add(TwigMiddleware::createFromContainer($app, 'view'));
};
