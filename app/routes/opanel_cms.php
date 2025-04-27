<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\CmsCategoryAction;

return function (RouteCollectorProxy $group) {
    // 分類管理頁面
    $group->get('/cms/categories', [CmsCategoryAction::class, 'index']);
    
    // 獲取所有分類
    $group->get('/cms/categories/list', [CmsCategoryAction::class, 'apiGetAll']);
    
    // 獲取單個分類
    $group->get('/cms/categories/{id:[0-9]+}', [CmsCategoryAction::class, 'apiGetOne']);
    
    // 創建新分類
    $group->post('/cms/categories/create', [CmsCategoryAction::class, 'apiCreate']);
    
    // 更新分類
    $group->post('/cms/categories/{id:[0-9]+}/edit', [CmsCategoryAction::class, 'apiUpdate']);
    
    // 刪除分類
    $group->delete('/cms/categories/{id:[0-9]+}/delete', [CmsCategoryAction::class, 'apiDelete']);
    
    // 切換分類啟用狀態
    $group->post('/cms/categories/{id:[0-9]+}/toggle-status', [CmsCategoryAction::class, 'apiToggleActive']);
    
    // 批量更新分類排序
    $group->post('/cms/categories/update-order', [CmsCategoryAction::class, 'apiUpdateOrder']);
};