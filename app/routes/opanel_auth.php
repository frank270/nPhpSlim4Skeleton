<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\AuthAction;

return function (RouteCollectorProxy $group) {
    $group->get('/login', [AuthAction::class, 'showLogin']);
    $group->post('/login', [AuthAction::class, 'login']);
    // $group->get('/logout', [AuthAction::class, 'logout']);
};