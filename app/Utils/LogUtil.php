<?php
declare(strict_types=1);

namespace App\Utils;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use App\Utils\CustomJsonFormatter;

class LogUtil
{
    /**
     * 取得日誌記錄器
     * 
     * @param string $channel 日誌通道名稱
     * @return Logger
     */
    public static function getLogger(string $channel = 'admin'): Logger
    {
        $logger = new Logger($channel);
        
        // 設定每日輪換的日誌檔案
        // 格式將會是 admin_operation-YYYY-MM-DD.log
        $logPath = $_ENV['LOG_PATH'] ?? __DIR__ . '/../../logs';
        $handler = new RotatingFileHandler(
            $logPath . '/admin_' . $channel . '.log',
            0, // 0 表示無限制保留天數，實際上會由檔案系統的日期後綴來區分
            Logger::INFO,
            true, // 檔案權限
            0644  // 檔案權限模式
        );
        
        // 使用自定義 JSON 格式
        $handler->setFormatter(new CustomJsonFormatter());
        
        // 添加自定義處理器，記錄使用者資訊
        $logger->pushProcessor(function ($record) {
            // 嘗試從不同來源獲取使用者資訊
            $userId = 0;
            $username = 'system';
            
            // 從 SESSION 獲取（後台用戶）
            if (isset($_SESSION['opanel_user'])) {
                $userId = $_SESSION['opanel_user']['id'] ?? 0;
                $username = $_SESSION['opanel_user']['username'] ?? 'system';
            }
            // 從前台用戶資訊獲取（如果有的話）
            else if (isset($_SESSION['user'])) {
                $userId = $_SESSION['user']['id'] ?? 0;
                $username = $_SESSION['user']['username'] ?? 'system';
            }
            // 從全局變數獲取（如果有的話）
            else if (isset($GLOBALS['current_user'])) {
                $userId = $GLOBALS['current_user']['id'] ?? 0;
                $username = $GLOBALS['current_user']['username'] ?? 'system';
            }
            
            // 添加到日誌記錄中
            $record['extra'] = array_merge($record['extra'] ?? [], [
                'user_id' => $userId,
                'username' => $username,
                'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? uniqid(),
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'host' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            ]);
            
            return $record;
        });
        
        $logger->pushHandler($handler);
        
        return $logger;
    }
    
    /**
     * 記錄操作日誌
     *
     * @param int $userId 操作者 ID
     * @param string $username 操作者帳號
     * @param string $action 操作類型
     * @param string|null $target 操作目標
     * @param array|null $oldData 修改前的資料
     * @param array|null $newData 修改後的資料
     * @param string|null $memo 備註說明
     * @return void
     */
    public static function logOperation(
        int $userId,
        string $username,
        string $action,
        ?string $target = null,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $memo = null
    ): void {
        $logger = self::getLogger('operation');
        
        // 清理資料，確保不包含敏感資訊
        if (is_array($oldData)) {
            $oldData = self::filterSensitiveData($oldData);
        }
        
        if (is_array($newData)) {
            $newData = self::filterSensitiveData($newData);
        }
        
        // 建立日誌內容
        $context = [
            'action' => $action,
            'target' => $target,
            'old_data' => $oldData,
            'new_data' => $newData,
            'memo' => $memo,
            'user' => [
                'id' => $userId,
                'username' => $username
            ]
        ];
        
        // 記錄日誌
        $logger->info("後台操作: $action" . ($target ? " [$target]" : ''), $context);
    }
    
    /**
     * 過濾敏感資料
     *
     * @param array $data 原始資料
     * @return array 過濾後的資料
     */
    private static function filterSensitiveData(array $data): array
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
    
    /**
     * 檢查是否為後台操作
     *
     * @param string $path 請求路徑
     * @return bool
     */
    public static function isBackendOperation(string $path): bool
    {
        return strpos($path, '/opanel') === 0;
    }
    
    /**
     * 檢查是否應該記錄前台操作
     *
     * @return bool
     */
    public static function shouldLogFrontend(): bool
    {
        return filter_var($_ENV['LOG_FRONTEND'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * 檢查是否應該記錄後台操作
     *
     * @return bool
     */
    public static function shouldLogBackend(): bool
    {
        return filter_var($_ENV['LOG_BACKEND'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }
}
