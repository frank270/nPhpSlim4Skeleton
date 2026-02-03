<?php

use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\ContentBlockAction;

return function (RouteCollectorProxy $group) {
    $group->get('/content-blocks', ContentBlockAction::class . ':index');
    $group->get('/content-blocks/list', ContentBlockAction::class . ':apiList');
    $group->post('/content-blocks', ContentBlockAction::class . ':apiCreate');
    $group->post('/content-blocks/upload', ContentBlockAction::class . ':apiUpload'); // Image upload
    $group->post('/content-blocks/{id}', ContentBlockAction::class . ':apiUpdate');
    $group->post('/content-blocks/{id}/delete', ContentBlockAction::class . ':apiDelete');
    $group->post('/content-blocks/{id}/status', ContentBlockAction::class . ':apiToggleStatus');
};
