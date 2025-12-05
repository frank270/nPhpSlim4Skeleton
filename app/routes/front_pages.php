<?php
use App\Actions\Front\LocationAction;

// 前台頁面路由 (SSR)
$app->get('/locations', [LocationAction::class, 'pageList']);
