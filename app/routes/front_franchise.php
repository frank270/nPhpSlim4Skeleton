<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Front\FranchiseAction;

/**
 * 前台 Franchise (加盟合作) 路由
 */
return function (Slim\App $app) {
    // 註冊 /franchise
    $app->get('/franchise', [FranchiseAction::class, 'index']);
    $app->post('/franchise/submit', [FranchiseAction::class, 'submit']);
};
