<?php
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $group) {
    // 前台食安檢驗頁面
    $group->get('/food-safety', [\App\Actions\Front\FoodSafetyAction::class, 'index']);
    $group->get('/food-safety/details/{id:[0-9]+}', [\App\Actions\Front\FoodSafetyAction::class, 'detail']);
};
