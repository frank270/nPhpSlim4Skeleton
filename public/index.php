<?php
declare(strict_types=1);
error_reporting(E_ALL);       // 顯示所有錯誤
ini_set('display_errors', 1);  // 在頁面上直接顯示錯誤（暫時）
ini_set('log_errors', 1);      // 啟用錯誤記錄
ini_set('error_log', __DIR__ . '/../logs/php-error.log'); // 指定錯誤記錄路徑

// 必須先啟動 Session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
// 🔥 這一行不能少！
use Slim\Factory\AppFactory;
// 自動載入 Composer 套件
require __DIR__ . '/../vendor/autoload.php';
// 載入 .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// 載入設定
$settings = require __DIR__ . '/../app/settings.php';

// 建立 Slim App
AppFactory::setContainer($settings['container']);
$app = AppFactory::create();




// 🔥 **順序：先載入 dependencies，再連接資料庫，最後加入 middleware！**
(require __DIR__ . '/../app/dependencies.php')($app);

// 連接資料庫
(require __DIR__ . '/../app/database.php')($app);

// 設定 Model
foreach (glob(__DIR__ . '/../app/Models/*.php') as $file) {
    require_once $file;
}

// 加入路由
(require __DIR__ . '/../app/routes.php')($app);

// 加入中介層
(require __DIR__ . '/../app/middleware.php')($app);



// 啟用路由中介層 (RoutingMiddleware)，確保最先執行路由匹配
$app->addRoutingMiddleware();

// 啟用 Slim 官方錯誤中介層，處理 404、500 等例外
$app->addErrorMiddleware(
    $settings['displayErrorDetails'] ?? false,
    $settings['logError'] ?? false,
    $settings['logErrorDetails'] ?? false
);

// Run app
$app->run();
