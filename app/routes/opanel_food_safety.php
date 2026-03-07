<?php

use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\FoodSafetyAction;

return function (RouteCollectorProxy $group) {
    // Categories
    $group->get('/food-safety/categories', [FoodSafetyAction::class, 'pageCategories']);
    $group->get('/food-safety/categories/list', [FoodSafetyAction::class, 'listCategories']);
    $group->get('/food-safety/categories/all', [FoodSafetyAction::class, 'getAllCategories']);
    $group->get('/food-safety/categories/{id:[0-9]+}', [FoodSafetyAction::class, 'getCategory']);
    $group->post('/food-safety/categories/create', [FoodSafetyAction::class, 'createCategory']);
    $group->post('/food-safety/categories/{id:[0-9]+}/edit', [FoodSafetyAction::class, 'updateCategory']);
    $group->delete('/food-safety/categories/{id:[0-9]+}/delete', [FoodSafetyAction::class, 'deleteCategory']);

    // Items (Reports)
    $group->get('/food-safety', [FoodSafetyAction::class, 'pageItems']);
    $group->get('/food-safety/items/list', [FoodSafetyAction::class, 'listItems']);
    $group->get('/food-safety/items/{id:[0-9]+}', [FoodSafetyAction::class, 'getItem']);
    $group->post('/food-safety/items/create', [FoodSafetyAction::class, 'createItem']);
    $group->post('/food-safety/items/{id:[0-9]+}/edit', [FoodSafetyAction::class, 'updateItem']);
    $group->delete('/food-safety/items/{id:[0-9]+}/delete', [FoodSafetyAction::class, 'deleteItem']);
};
