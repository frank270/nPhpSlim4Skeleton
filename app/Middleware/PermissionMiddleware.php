<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Utils\PermissionChecker;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Flash\Messages;
use Slim\Psr7\Factory\ResponseFactory;

class PermissionMiddleware implements MiddlewareInterface
{
    private PermissionChecker $permissionChecker;
    private Messages $flash;
    private ResponseFactory $responseFactory;
    
    public function __construct(
        PermissionChecker $permissionChecker,
        Messages $flash,
        ResponseFactory $responseFactory
    ) {
        $this->permissionChecker = $permissionChecker;
        $this->flash = $flash;
        $this->responseFactory = $responseFactory;
    }
    
    /**
     * 處理請求並檢查權限
     *
     * @param Request $request 請求物件
     * @param RequestHandler $handler 請求處理器
     * @return Response 回應物件
     */
    /**
     * 判斷是否為 API 請求
     *
     * @param Request $request 請求物件
     * @return bool 是否為 API 請求
     */
    private function isApiRequest(Request $request): bool
    {
        // 判斷方式可能有多種，這裡列出常見的方式
        
        // 1. 檢查路徑是否包含 /api/
        if (strpos($request->getUri()->getPath(), '/api/') === 0) {
            return true;
        }
        
        // 2. 檢查 Accept 標頭是否要求 JSON
        $accept = $request->getHeaderLine('Accept');
        if (strpos($accept, 'application/json') !== false) {
            return true;
        }
        
        // 3. 檢查是否為 AJAX 請求
        $xRequestedWith = $request->getHeaderLine('X-Requested-With');
        if ($xRequestedWith === 'XMLHttpRequest') {
            return true;
        }
        
        return false;
    }
    
    /**
     * 處理無權限訪問
     *
     * @param Request $request 請求物件
     * @param array|null $user 使用者資訊
     * @return Response 回應物件
     */
    private function handleUnauthorized(Request $request, ?array $user): Response
    {
        // 如果是 API 請求，回傳 JSON 格式的錯誤訊息
        if ($this->isApiRequest($request)) {
            $response = $this->responseFactory->createResponse(403);
            $response = $response->withHeader('Content-Type', 'application/json');
            $errorMessage = json_encode([
                'status' => 'error',
                'code' => 403,
                'message' => empty($user) ? '請先登入系統' : '您沒有權限訪問此資源'
            ], JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($errorMessage);
            return $response;
        }
        
        // 如果使用者未登入
        if (empty($user)) {
            $this->flash->addMessage('login_error', '請先登入系統');
            $response = $this->responseFactory->createResponse(302);
            return $response->withHeader('Location', '/opanel/login');
        }
        
        // 如果使用者已登入但沒有權限，導向到無權限頁面
        $this->flash->addMessage('error', '您沒有權限訪問此頁面');
        
        // 取得原始請求的 URL 路徑
        $requestedUrl = $request->getUri()->getPath();
        $encodedUrl = urlencode($requestedUrl);
        
        $response = $this->responseFactory->createResponse(302);
        return $response->withHeader('Location', "/opanel/access-denied/{$encodedUrl}");
    }
    
    public function process(Request $request, RequestHandler $handler): Response
    {
        // 透過 RouteContext 取得匹配後的 Route
        $route = RouteContext::fromRequest($request)->getRoute();
        
        // 記錄路由資訊
        error_log('PermissionMiddleware: ' . $request->getUri()->getPath() . ', route: ' . ($route ? 'found' : 'not found'));
        
        // 取得 controllerClass 和 method；若 route 未匹配到，使用 URI path + HTTP method
        if ($route && is_array($route->getCallable())) {
            [$controllerClass, $method] = $route->getCallable();
        } else {
            $controllerClass = $request->getUri()->getPath();
            $method = $request->getMethod();
        }
        
        // 如果不是字串（例如已實例化的物件），獲取類別名稱
        if (!is_string($controllerClass)) {
            $controllerClass = get_class($controllerClass);
        }
        
        // 提取控制器名稱（移除命名空間）
        $controllerParts = explode('\\', $controllerClass);
        $controller = end($controllerParts);
        
        // 檢查是否為後台控制器（Opanel 目錄下的控制器）
        if (strpos($controllerClass, 'App\\Actions\\Opanel\\') === 0) {
            // 跳過權限檢查頁面和登入頁面
            if ($controller === 'AccessDeniedAction' || 
                ($controller === 'AuthAction' && $method === 'showLogin') || 
                ($controller === 'AuthAction' && $method === 'login')) {
                return $handler->handle($request);
            }
            
            // 獲取使用者資訊
            $user = $_SESSION['opanel_user'] ?? null;
            
            // 檢查權限
            if (!$this->permissionChecker->checkPermission($user, $controller, $method)) {
                return $this->handleUnauthorized($request, $user);
            }
        }
        
        // 如果通過權限檢查或不是後台控制器，繼續處理請求
        return $handler->handle($request);
    }
}