<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\AccessDeniedAction;
use App\Actions\Opanel\AccessRefineAction;
use App\Actions\Opanel\AccessRoleAction;

return function (RouteCollectorProxy $group) {
    $group->get('/access-denied[/{requested_url}]', [AccessDeniedAction::class, '__invoke']);
    $group->get('/access/refine-names', [AccessRefineAction::class, 'handleGet'])
          ->setName('access.refine.get');
    $group->post('/access/refine-names', [AccessRefineAction::class, 'handlePost'])
          ->setName('access.refine.post');
    $group->get('/access/roles', [AccessRoleAction::class, 'lists'])
          ->setName('access.roles');
};