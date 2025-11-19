<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\FaqAction;

return function (RouteCollectorProxy $group) {
    // FAQ 管理頁（React 容器）
    $group->get('/faqs', [FaqAction::class, 'index']);

    // 分類 API
    $group->get('/faqs/categories', [FaqAction::class, 'apiListCategories']);
    $group->get('/faqs/categories/{id:[0-9]+}', [FaqAction::class, 'apiGetCategory']);
    $group->post('/faqs/categories/create', [FaqAction::class, 'apiCreateCategory']);
    $group->post('/faqs/categories/{id:[0-9]+}/edit', [FaqAction::class, 'apiUpdateCategory']);
    $group->delete('/faqs/categories/{id:[0-9]+}/delete', [FaqAction::class, 'apiDeleteCategory']);

    // FAQ API
    $group->get('/faqs/list', [FaqAction::class, 'apiList']);
    $group->get('/faqs/{id:[0-9]+}', [FaqAction::class, 'apiGetOne']);
    $group->post('/faqs/create', [FaqAction::class, 'apiCreate']);
    $group->post('/faqs/{id:[0-9]+}/edit', [FaqAction::class, 'apiUpdate']);
    $group->delete('/faqs/{id:[0-9]+}/delete', [FaqAction::class, 'apiDelete']);
    $group->post('/faqs/{id:[0-9]+}/toggle-status', [FaqAction::class, 'apiToggleStatus']);
};

