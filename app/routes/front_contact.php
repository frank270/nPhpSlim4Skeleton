<?php
declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use App\Actions\Front\ContactAction;

return function (RouteCollectorProxy $app) {
    $app->get('/contact', [ContactAction::class, 'index']);
    $app->post('/contact/submit', [ContactAction::class, 'submit']);
};
