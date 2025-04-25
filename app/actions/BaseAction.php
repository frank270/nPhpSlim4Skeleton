<?php
declare(strict_types=1);

namespace App\Actions;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Monolog\Logger;
use Slim\Flash\Messages;
use Doctrine\DBAL\Connection;
use App\Utils\LogUtil;


class BaseAction
{
    protected Twig $view;
    protected Logger $logger;
    protected Messages $flash;
    protected ContainerInterface $container;
    protected Connection $conn;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->view = $container->get('view');
        $this->logger = $container->get('logger');
        $this->flash = $container->get('flash');
        $this->conn = $container->get(Connection::class);
    }
    protected function respondJson(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
    
    /**
     * 記錄操作日誌
     *
     * @param string $action 操作類型
     * @param string|null $target 操作目標
     * @param array|null $oldData 修改前的資料
     * @param array|null $newData 修改後的資料
     * @param string|null $memo 備註說明
     * @return void
     */
    protected function logAction(string $action, ?string $target = null, ?array $oldData = null, ?array $newData = null, ?string $memo = null): void
    {
        // 檢查是否為後台操作
        // 從當前路徑判斷是否為後台操作
        $path = $_SERVER['REQUEST_URI'] ?? '';
        
        // 檢查是否應該記錄
        $isBackend = LogUtil::isBackendOperation($path);
        if (($isBackend && LogUtil::shouldLogBackend()) || 
            (!$isBackend && LogUtil::shouldLogFrontend())) {
            
            // 獲取當前使用者資訊
            $session = isset($_SESSION) ? $_SESSION : [];
            $currentUser = $session['user'] ?? null;
            $userId = $currentUser['id'] ?? 0;
            $username = $currentUser['username'] ?? 'system';
            
            // 記錄操作
            LogUtil::logOperation(
                $userId,
                $username,
                $action,
                $target,
                $oldData,
                $newData,
                $memo
            );
        }
    }
}
