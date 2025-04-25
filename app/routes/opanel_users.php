<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\UserAction;

return function (RouteCollectorProxy $group) {
    // 使用者列表
    $group->get('/users', [UserAction::class, 'index']);
    $group->get('/users/list', [UserAction::class, 'list']);
    
    // 新增使用者
    $group->get('/users/create', [UserAction::class, 'showCreateForm']);
    $group->post('/users/create', [UserAction::class, 'create']);
    
    // 編輯使用者
    $group->get('/users/{id}/edit', [UserAction::class, 'showEditForm']);
    $group->post('/users/{id}/edit', [UserAction::class, 'update']);
    
    // 重設密碼
    $group->post('/users/{id}/reset-password', [UserAction::class, 'resetPassword']);
    
    // 停用/啟用使用者
    $group->post('/users/{id}/toggle-status', [UserAction::class, 'toggleStatus']);
    
    // 刪除使用者
    $group->post('/users/{id}/delete', [UserAction::class, 'delete']);
};
