<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\MenuAction;

return function (RouteCollectorProxy $group) {
    $group->group('/menu', function (RouteCollectorProxy $group) {
        // Categories
        $group->get('/categories', [MenuAction::class, 'pageCategories']); // Page
        $group->get('/categories/list', [MenuAction::class, 'listCategories']);
        $group->get('/categories/all', [MenuAction::class, 'getAllCategories']); // For dropdowns
        $group->get('/categories/{id:[0-9]+}', [MenuAction::class, 'getCategory']);
        $group->post('/categories/create', [MenuAction::class, 'createCategory']);
        $group->post('/categories/{id:[0-9]+}/edit', [MenuAction::class, 'updateCategory']);
        $group->delete('/categories/{id:[0-9]+}/delete', [MenuAction::class, 'deleteCategory']);

        // Items
        $group->get('/items', [MenuAction::class, 'pageItems']); // Page
        $group->get('/items/list', [MenuAction::class, 'listItems']);
        $group->get('/items/{id:[0-9]+}', [MenuAction::class, 'getItem']);
        $group->post('/items/create', [MenuAction::class, 'createItem']);
        $group->post('/items/{id:[0-9]+}/edit', [MenuAction::class, 'updateItem']);
        $group->delete('/items/{id:[0-9]+}/delete', [MenuAction::class, 'deleteItem']);
    });
};
