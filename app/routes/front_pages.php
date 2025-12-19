<?php
use App\Actions\Front\LocationAction;

// 前台頁面路由 (SSR)
$app->get('/locations', [LocationAction::class, 'pageList']);

use App\Actions\Front\NewsAction;
$app->get('/privacy', [NewsAction::class, 'privacyPolicy']);
$app->get('/terms', [NewsAction::class, 'termsOfUse']);
