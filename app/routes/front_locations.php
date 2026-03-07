<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Front\LocationAction;

return function (RouteCollectorProxy $group) {
    // 前台門市 API
    $group->get('/locations', [LocationAction::class, 'apiList']);
    $group->get('/locations/cities', [LocationAction::class, 'apiGetCounties']);
    $group->get('/locations/{id:[0-9]+}', [LocationAction::class, 'apiGetOne']);
};
