<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\DashBoardAction;

return function (RouteCollectorProxy $group) {
    $group->get('/dashboard', [DashBoardAction::class, '__invoke']);
   
};