<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Front\NewsAction;

return function (RouteCollectorProxy $group) {
    $group->group('/news', function (RouteCollectorProxy $group) {
        $group->get('', [NewsAction::class, 'index']);
        $group->get('/{slug}', [NewsAction::class, 'detail']);
    });
};
