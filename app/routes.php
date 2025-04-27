<?php
declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Middleware\AdminLogMiddleware;

return function (App $app) {
    foreach (glob(__DIR__ . '/Routes/*.php') as $routeFile) {
        require $routeFile;
    }
    // ✅ 正確做法：自己 group，然後手動呼叫每個回傳的 Closure
    $app->group('/opanel', function (RouteCollectorProxy $group) {
        (require __DIR__ . '/Routes/opanel_auth.php')($group);
        (require __DIR__ . '/Routes/opanel_dashboard.php')($group);
        (require __DIR__ . '/Routes/opanel_access.php')($group);
        (require __DIR__ . '/Routes/opanel_users.php')($group);
        (require __DIR__ . '/Routes/opanel_cms.php')($group); // 添加 CMS 路由
        // 其他 ...
    })->add($app->getContainer()->get(AdminLogMiddleware::class)); // 為整個路由群組添加日誌中間件
};
