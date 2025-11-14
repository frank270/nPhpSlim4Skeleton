<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\MediaAssetAction;

return function (RouteCollectorProxy $group) {
    $group->get('/media-assets', [MediaAssetAction::class, 'index']);
    $group->get('/media-assets/list', [MediaAssetAction::class, 'apiList']);
    $group->get('/media-assets/{id:[0-9]+}', [MediaAssetAction::class, 'apiGetOne']);

    $group->post('/media-assets/upload', [MediaAssetAction::class, 'apiUpload']);
    $group->post('/media-assets/{id:[0-9]+}/edit', [MediaAssetAction::class, 'apiUpdate']);
    $group->post('/media-assets/{id:[0-9]+}/toggle-status', [MediaAssetAction::class, 'apiToggleStatus']);
    $group->delete('/media-assets/{id:[0-9]+}/delete', [MediaAssetAction::class, 'apiDelete']);
};
