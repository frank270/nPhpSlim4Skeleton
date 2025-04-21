<?php
declare(strict_types=1);

use Slim\App;
return function (App $app) {
    foreach (glob(__DIR__ . '/routes/*.php') as $routeFile) {
        require $routeFile;
    }
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
