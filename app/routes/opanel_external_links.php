<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\ExternalLinkAction;

return function (RouteCollectorProxy $group) {
    // 外部連結管理頁（React 容器）
    $group->get('/external-links', [ExternalLinkAction::class, 'index']);

    // API 列表與詳情
    $group->get('/external-links/list', [ExternalLinkAction::class, 'apiList']);
    $group->get('/external-links/{id:[0-9]+}', [ExternalLinkAction::class, 'apiGetOne']);

    // 建立與更新
    $group->post('/external-links/create', [ExternalLinkAction::class, 'apiCreate']);
    $group->post('/external-links/{id:[0-9]+}/edit', [ExternalLinkAction::class, 'apiUpdate']);

    // 刪除與狀態切換
    $group->delete('/external-links/{id:[0-9]+}/delete', [ExternalLinkAction::class, 'apiDelete']);
    $group->post('/external-links/{id:[0-9]+}/toggle-status', [ExternalLinkAction::class, 'apiToggleActive']);
};
