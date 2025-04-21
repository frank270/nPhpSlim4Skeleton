<?php
declare(strict_types=1);

namespace App\Utils;

use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class PermissionChecker
{
    private ContainerInterface $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * 檢查使用者是否有權限訪問指定的控制器和方法
     *
     * @param array|null $user 使用者資訊
     * @param string $controller 控制器名稱
     * @param string $method 方法名稱
     * @return bool 是否有權限
     */
    public function checkPermission(?array $user, string $controller, string $method): bool
    {
        // 從容器中獲取 Connection 和 Logger
        try {
            $conn = $this->container->get(\Doctrine\DBAL\Connection::class);
            $logger = $this->container->get('logger');
            
            // 測試資料庫連接
            error_log('測試資料庫連接...');
            $testQuery = $conn->executeQuery('SELECT 1');
            $result = $testQuery->fetchOne();
            error_log('資料庫連接測試結果: ' . ($result === 1 ? '成功' : '失敗'));
        } catch (\Exception $e) {
            error_log('資料庫連接錯誤: ' . $e->getMessage());
            return false;
        }
        
        // 記錄執行追蹤
        $logger->info('權限檢查執行', [
            'controller' => $controller,
            'method' => $method,
            'user' => $user ? $user['username'] : 'unknown'
        ]);
        
        // 檢查使用者是否已登入
        if (empty($user) || empty($user['id']) || empty($user['group_id'])) {
            return false;
        }
        
        // 取得使用者群組 ID
        $groupId = $user['group_id'];
        
        // 檢查是否為超級管理員群組 (code = 'superadmin')
        $isSuperAdmin = $conn->fetchOne(
            'SELECT COUNT(*) FROM permissions_groups WHERE id = ? AND code = ?',
            [$groupId, 'superadmin']
        );
        
        // 檢查功能是否已存在，如果不存在則自動創建
        $funcId = $conn->fetchOne(
            'SELECT id FROM permissions_ctrl_func WHERE controller = ? AND method = ?',
            [$controller, $method]
        );
        
        if (!$funcId) {
            // 自動創建功能記錄
            try {
                $conn->insert('permissions_ctrl_func', [
                    'code' => strtolower(str_replace('Action', '', $controller) . '_' . $method),
                    'name' => str_replace('Action', '', $controller) . ' ' . $method,
                    'controller' => $controller,
                    'method' => $method,
                    'type' => 'backend'
                ]);
                
                $funcId = $conn->lastInsertId();
                
                $logger->info('自動創建功能權限成功', [
                    'controller' => $controller,
                    'method' => $method,
                    'user_id' => $user['id'],
                    'func_id' => $funcId
                ]);
            } catch (\Exception $e) {
                $logger->error('自動創建功能權限失敗', [
                    'controller' => $controller,
                    'method' => $method,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // 將錯誤記錄到 PHP 錯誤日誌
                error_log('PermissionChecker 錯誤: ' . $e->getMessage());
                
                // 如果創建失敗，返回 false
                return false;
            }
        }
        
        // 檢查是否已存在權限設定，如果不存在則創建
        $hasPermission = $conn->fetchOne(
            'SELECT enabled FROM permissions_matrix WHERE group_id = ? AND func_id = ?',
            [$groupId, $funcId]
        );
        
        if ($hasPermission === false) {
            try {
                $conn->insert('permissions_matrix', [
                    'group_id' => $groupId,
                    'func_id' => $funcId,
                    'enabled' => $isSuperAdmin > 0 ? 1 : 0 // 超級管理員預設啟用權限，其他預設關閉
                ]);
                
                $logger->info('自動創建權限設定成功', [
                    'controller' => $controller,
                    'method' => $method,
                    'group_id' => $groupId,
                    'user_id' => $user['id'],
                    'func_id' => $funcId,
                    'enabled' => $isSuperAdmin > 0 ? 1 : 0
                ]);
                
                // 更新 hasPermission 變數為新創建的權限設定值
                $hasPermission = $isSuperAdmin > 0 ? 1 : 0;
            } catch (\Exception $e) {
                $logger->error('自動創建權限設定失敗', [
                    'controller' => $controller,
                    'method' => $method,
                    'group_id' => $groupId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // 將錯誤記錄到 PHP 錯誤日誌
                error_log('PermissionChecker 錯誤 (permissions_matrix): ' . $e->getMessage());
            }
        }
        
        if ($isSuperAdmin > 0) {
            // 超級管理員擁有所有權限
            return true;
        }
        
        return (bool)$hasPermission;
    }
}