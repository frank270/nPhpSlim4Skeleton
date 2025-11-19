<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\LocationAction;

return function (RouteCollectorProxy $group) {
    // 門市據點管理頁（React 容器）
    $group->get('/locations', [LocationAction::class, 'index']);

    // API 列表與詳情
    $group->get('/locations/list', [LocationAction::class, 'apiList']);
    $group->get('/locations/counties', [LocationAction::class, 'apiGetCounties']);
    $group->get('/locations/{id:[0-9]+}', [LocationAction::class, 'apiGetOne']);

    // 建立與更新
    $group->post('/locations/create', [LocationAction::class, 'apiCreate']);
    $group->post('/locations/{id:[0-9]+}/edit', [LocationAction::class, 'apiUpdate']);

    // 刪除與狀態切換
    $group->delete('/locations/{id:[0-9]+}/delete', [LocationAction::class, 'apiDelete']);
    $group->post('/locations/{id:[0-9]+}/toggle-status', [LocationAction::class, 'apiToggleStatus']);
};

