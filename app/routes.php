<?php
declare(strict_types=1);

use Slim\App;
use App\HomeAction;

return function (App $app) {
     // 首頁 landing page route
     $app->get('/', HomeAction::class . ':landingPage');
    // 定義 /hello/{name} 路由，交給 HomeAction
    $app->get('/hello[/{name}]', HomeAction::class);
    /*
    $app->group('/member', function ($group) {
        $ctrl = MemberAction::class;
        $group->get('/profile', "{$ctrl}:profile");
        $group->get('/profile/edit', "{$ctrl}:profileEdit");
        $group->get('/fbLogin', "{$ctrl}:fbLogin");
        $group->get('/login', "{$ctrl}:login");
        $group->post('/fbProc', "{$ctrl}:fbProc");
        $group->get('/fbProc', "{$ctrl}:fbProc");
        $group->post('/fbLogin/revoke', "{$ctrl}:revokeFacebookLogin");
        $group->get('/logout', "{$ctrl}:logout");
    });
    */
};
