<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Front\AboutUsAction;

/**
 * 前台 About / History 路由
 * 
 * 兩個網址指向同一個 Action，顯示相同的內容
 */
return function (Slim\App $app) {
    // 註冊 /about
    $app->get('/about', [AboutUsAction::class, 'index']);

    // 註冊 /history
    $app->get('/history', [AboutUsAction::class, 'index']);
};
