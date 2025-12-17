<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\CmsCategoryAction;

return function (RouteCollectorProxy $group) {
    // CMS Post Management
    $group->get('/cms', [App\Actions\Opanel\CmsPostAction::class, 'pageIndex']);

    $group->get('/cms/posts/list', [App\Actions\Opanel\CmsPostAction::class, 'list']);
    $group->get('/cms/posts/{id:[0-9]+}', [App\Actions\Opanel\CmsPostAction::class, 'get']);
    $group->post('/cms/posts/create', [App\Actions\Opanel\CmsPostAction::class, 'create']);
    $group->post('/cms/posts/{id:[0-9]+}/edit', [App\Actions\Opanel\CmsPostAction::class, 'update']);
    $group->delete('/cms/posts/{id:[0-9]+}/delete', [App\Actions\Opanel\CmsPostAction::class, 'delete']);
};