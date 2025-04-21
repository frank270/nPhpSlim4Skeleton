<?php
declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
return function (App $app) {
    foreach (glob(__DIR__ . '/Routes/*.php') as $routeFile) {
        require $routeFile;
    }
    // ✅ 正確做法：自己 group，然後手動呼叫每個回傳的 Closure
    $app->group('/opanel', function (RouteCollectorProxy $group) {
        (require __DIR__ . '/Routes/opanel_auth.php')($group);
        // 其他 ...
    });
};
// return function (App $app) {
//     /*
//     $app->group('/member', function ($group) {
//         $ctrl = MemberAction::class;
//         $group->get('/profile', "{$ctrl}:profile");
//         $group->get('/profile/edit', "{$ctrl}:profileEdit");
//         $group->get('/fbLogin', "{$ctrl}:fbLogin");
//         $group->get('/login', "{$ctrl}:login");
//         $group->post('/fbProc', "{$ctrl}:fbProc");
//         $group->get('/fbProc', "{$ctrl}:fbProc");
//         $group->post('/fbLogin/revoke', "{$ctrl}:revokeFacebookLogin");
//         $group->get('/logout', "{$ctrl}:logout");
//     });
//     */
// };
