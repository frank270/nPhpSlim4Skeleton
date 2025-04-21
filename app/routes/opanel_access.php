<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\AccessDeniedAction;

return function (RouteCollectorProxy $group) {
    $group->get('/access-denied[/{requested_url}]', [AccessDeniedAction::class, '__invoke']);
};