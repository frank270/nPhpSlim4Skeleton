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
    $app->group('/opanel', function (RouteCollectorProxy $group) use ($app) {
        $group->get('', function ($request, $response) {
            return $response->withHeader('Location', '/opanel/login')->withStatus(302);
        });
        $group->get('/', function ($request, $response) {
            return $response->withHeader('Location', '/opanel/login')->withStatus(302);
        });

        (require __DIR__ . '/Routes/opanel_auth.php')($group);
        (require __DIR__ . '/Routes/opanel_dashboard.php')($group);
        (require __DIR__ . '/Routes/opanel_users.php')($group);
        (require __DIR__ . '/Routes/opanel_access.php')($group);
        (require __DIR__ . '/Routes/opanel_cms.php')($group);
        (require __DIR__ . '/Routes/opanel_external_links.php')($group);
        (require __DIR__ . '/Routes/opanel_media_assets.php')($group);
        (require __DIR__ . '/Routes/opanel_locations.php')($group);
        (require __DIR__ . '/Routes/opanel_menu.php')($group);
        (require __DIR__ . '/Routes/opanel_food_safety.php')($group);
        (require __DIR__ . '/Routes/opanel_faqs.php')($group);
        (require __DIR__ . '/Routes/opanel_franchise.php')($group);
        (require __DIR__ . '/Routes/opanel_content_blocks.php')($group);
    })->add(AdminLogMiddleware::class); // 為整個路由群組添加日誌中間件

    // 前台 API 群組
    $app->group('/api/front', function (RouteCollectorProxy $group) {
        (require __DIR__ . '/Routes/front_locations.php')($group);
    });

    // 前台路由
    (require __DIR__ . '/Routes/front_menu.php')($app);
    (require __DIR__ . '/Routes/front_food_safety.php')($app);
    (require __DIR__ . '/Routes/front_news.php')($app);
    (require __DIR__ . '/Routes/front_faq.php')($app);
    (require __DIR__ . '/Routes/front_about.php')($app); // 新增 About/History 路由
    (require __DIR__ . '/Routes/front_franchise.php')($app); // 新增 Franchise 路由
    (require __DIR__ . '/Routes/front_contact.php')($app);
};
