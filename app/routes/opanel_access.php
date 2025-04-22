<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\AccessDeniedAction;
use App\Actions\Opanel\AccessRefineAction;

return function (RouteCollectorProxy $group) {
    $group->get('/access-denied[/{requested_url}]', [AccessDeniedAction::class, '__invoke']);
    $group->map(['GET', 'POST'], '/access/refine-names', [AccessRefineAction::class, '__invoke'])
          ->setName('access.refine');
};