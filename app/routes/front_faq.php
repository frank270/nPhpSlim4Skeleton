<?php
declare(strict_types=1);

use App\Actions\Front\FaqAction;
use Slim\App;

return function (App $app) {
    $app->get('/faq', [FaqAction::class, 'index']);
};
