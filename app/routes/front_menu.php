<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Front\MenuAction;

return function (RouteCollectorProxy $group) {
    // 前台菜單頁面
    $group->get('/menu', [\App\Actions\Front\MenuAction::class, 'index']);
    $group->get('/menu/details/{id:[0-9]+}', [\App\Actions\Front\MenuAction::class, 'detail']);
};
