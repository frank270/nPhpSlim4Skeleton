<?php
use App\HomeAction;
// 首頁 landing page route
$app->get('/', HomeAction::class . ':landingPage');
// 定義 /hello/{name} 路由，交給 HomeAction
$app->get('/hello[/{name}]', HomeAction::class);