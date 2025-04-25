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

    // Twig View
    $container->set('view', function () {
        return Twig::create(__DIR__ . '/Templates', [
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
