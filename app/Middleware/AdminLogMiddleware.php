<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Utils\LogUtil;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;

class AdminLogMiddleware
{
    protected ContainerInterface $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // 獲取請求資訊
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        
        // 檢查是否為後台操作
        $isBackend = LogUtil::isBackendOperation($path);
        
        // 根據設定決定是否記錄
        if (($isBackend && LogUtil::shouldLogBackend()) || 
            (!$isBackend && LogUtil::shouldLogFrontend())) {
            
            // 獲取當前使用者資訊
            $session = isset($_SESSION) ? $_SESSION : [];
            $currentUser = $session['user'] ?? null;
            $userId = $currentUser['id'] ?? 0;
            $username = $currentUser['username'] ?? 'anonymous';
            
            // 獲取請求參數
            $params = $request->getQueryParams();
            
            // 獲取請求內容（如果有）
            $body = $request->getParsedBody();
            $filteredBody = is_array($body) ? $this->filterSensitiveData($body) : null;
            
            // 記錄所有請求
            $context = [
                'method' => $method,
                'path' => $path,
                'query' => $params
            ];
            
            // 如果有 body 資料，添加到日誌中
            if ($filteredBody !== null) {
                $context['body'] = $filteredBody;
            }
            
            // 記錄請求
            LogUtil::logOperation(
                $userId,
                $username,
                "request_$method",
                $path,
                null,
                $context,
                "後台請求: $method $path"
            );
        }
        
        // 處理請求
        $response = $handler->handle($request);
        
        return $response;
    }
    
    /**
     * 過濾敏感資料
     *
     * @param array $data 原始資料
     * @return array 過濾後的資料
     */
    private function filterSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_confirm', 'password_hash', 
            'token', 'secret', 'api_key', 'credit_card'
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '******';
            }
        }
        
        return $data;
    }
}
