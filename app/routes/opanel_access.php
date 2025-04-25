<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\AccessDeniedAction;
use App\Actions\Opanel\AccessRefineAction;
use App\Actions\Opanel\AccessRoleAction;
use App\Actions\Opanel\AccessUpdatePermissionAction;
use App\Actions\Opanel\AccessGroupAction;

return function (RouteCollectorProxy $group) {
      $group->get('/access-denied[/{requested_url}]', [AccessDeniedAction::class, '__invoke']);
      $group->get('/access/refine-names', [AccessRefineAction::class, 'handleGet'])
            ->setName('access.refine.get');
      $group->post('/access/refine-names', [AccessRefineAction::class, 'handlePost'])
            ->setName('access.refine.post');
      $group->get('/access/roles', [AccessRoleAction::class, 'lists'])
            ->setName('access.roles');
      $group->post('/access/update-permission', [AccessUpdatePermissionAction::class, '__invoke'])
            ->setName('access.update-permission');
      $group->get('/access/group/{groupId}/matrix', [AccessGroupAction::class, 'matrix'])
          ->setName('access.group.matrix');
      $group->get('/access/roles/list', [AccessRoleAction::class, 'fetchList'])
            ->setName('access.roles.list');
      $group->post('/access/roles/create', [AccessRoleAction::class, 'create'])
            ->setName('access.roles.create');
      $group->delete('/access/roles/{id}', [AccessRoleAction::class, 'delete'])
            ->setName('access.roles.delete');
      $group->get('/access/group/{groupId}/permissions', [AccessGroupAction::class, 'permissions'])
      ->setName('access.group.permissions');
};