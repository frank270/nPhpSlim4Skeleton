<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Utils\LogUtil;

class UserAction extends BaseAction
{
    /**
     * 顯示使用者列表頁面
     */
    public function index(Request $request, Response $response): Response
    {
        // 獲取角色群組列表，供篩選使用
        $groups = $this->conn->fetchAllAssociative(
            'SELECT id, name FROM permissions_groups ORDER BY id ASC'
        );
        
        return $this->view->render($response, 'Opanel/users/index.twig', [
            'groups' => $groups
        ]);
    }
    
    /**
     * 獲取使用者列表資料（API）
     */
    public function fetchList(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $groupId = isset($params['group_id']) && $params['group_id'] !== '' ? (int)$params['group_id'] : null;
        $keyword = $params['keyword'] ?? '';
        
        // 建立基本查詢
        $sql = 'SELECT u.id, u.username, u.display_name, u.last_login_at, u.status, 
                       g.name as group_name, g.id as group_id
                FROM admin_users u
                LEFT JOIN permissions_groups g ON u.group_id = g.id
                WHERE 1=1';
        $params = [];
        
        // 加入過濾條件
        if ($groupId !== null) {
            $sql .= ' AND u.group_id = ?';
            $params[] = $groupId;
        }
        
        if ($keyword !== '') {
            $sql .= ' AND (u.username LIKE ? OR u.display_name LIKE ?)';
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        $sql .= ' ORDER BY u.id ASC';
        
        $users = $this->conn->fetchAllAssociative($sql, $params);
        
        return $this->respondJson($response, ['users' => $users]);
    }
    
    /**
     * 顯示新增使用者表單
     */
    public function showCreateForm(Request $request, Response $response): Response
    {
        // 檢查是否為 API 請求
        $isApiRequest = $request->getHeaderLine('Accept') === 'application/json';
        
        // 獲取角色群組列表
        $groups = $this->conn->fetchAllAssociative(
            'SELECT id, name FROM permissions_groups ORDER BY id ASC'
        );
        
        if ($isApiRequest) {
            return $this->respondJson($response, [
                'groups' => $groups
            ]);
        }
        
        return $this->view->render($response, 'Opanel/users/create.twig', [
            'groups' => $groups
        ]);
    }
    
    /**
     * 新增使用者
     */
    public function create(Request $request, Response $response): Response
    {
        // 檢查是否為 API 請求
        $isApiRequest = $request->getHeaderLine('Content-Type') === 'application/json';
        
        // 獲取請求參數
        if ($isApiRequest) {
            $params = json_decode($request->getBody()->getContents(), true) ?? [];
        } else {
            $params = (array) $request->getParsedBody();
        }
        
        $username = trim($params['username'] ?? '');
        $password = trim($params['password'] ?? '');
        $displayName = trim($params['display_name'] ?? '');
        $groupId = isset($params['group_id']) && $params['group_id'] !== '' ? (int)$params['group_id'] : null;
        
        // 驗證欄位
        if ($username === '' || $password === '') {
            if ($isApiRequest) {
                return $this->respondJson($response, [
                    'success' => false,
                    'message' => '帳號和密碼不能為空'
                ], 400);
            } else {
                $this->flash->addMessage('danger', '帳號和密碼不能為空');
                return $response->withHeader('Location', '/opanel/users/create')->withStatus(302);
            }
        }
        
        // 檢查帳號是否已存在
        $existingUser = $this->conn->fetchAssociative(
            'SELECT id FROM admin_users WHERE username = ?',
            [$username]
        );
        
        if ($existingUser) {
            if ($isApiRequest) {
                return $this->respondJson($response, [
                    'success' => false,
                    'message' => '帳號已存在'
                ], 400);
            } else {
                $this->flash->addMessage('danger', '帳號已存在');
                return $response->withHeader('Location', '/opanel/users/create')->withStatus(302);
            }
        }
        
        // 建立新使用者
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->conn->insert('admin_users', [
            'username' => $username,
            'password_hash' => $passwordHash,
            'display_name' => $displayName,
            'group_id' => $groupId,
            'status' => 1, // 預設啟用
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $userId = (int)$this->conn->lastInsertId();
        
        // 記錄操作日誌
        LogUtil::logOperation(
            $_SESSION['opanel_user']['id'],
            $_SESSION['opanel_user']['username'],
            'create',
            'admin_user',
            null,
            ['id' => $userId, 'username' => $username, 'display_name' => $displayName, 'group_id' => $groupId],
            "新增後台使用者: $username"
        );
        
        if ($isApiRequest) {
            return $this->respondJson($response, [
                'success' => true,
                'message' => '使用者新增成功',
                'user_id' => $userId
            ]);
        } else {
            $this->flash->addMessage('success', '使用者新增成功');
            return $response->withHeader('Location', '/opanel/users')->withStatus(302);
        }
    }
    
    /**
     * 顯示編輯使用者表單
     */
    public function showEditForm(Request $request, Response $response, array $args): Response
    {
        // 檢查是否為 API 請求
        $isApiRequest = $request->getHeaderLine('Accept') === 'application/json';
        
        $userId = (int)$args['id'];
        
        // 獲取使用者資料
        $user = $this->conn->fetchAssociative(
            'SELECT id, username, display_name, group_id, status FROM admin_users WHERE id = ?',
            [$userId]
        );
        
        if (!$user) {
            if ($isApiRequest) {
                return $this->respondJson($response, [
                    'success' => false,
                    'message' => '使用者不存在'
                ], 404);
            } else {
                $this->flash->addMessage('danger', '使用者不存在');
                return $response->withHeader('Location', '/opanel/users')->withStatus(302);
            }
        }
        
        // 獲取角色群組列表
        $groups = $this->conn->fetchAllAssociative(
            'SELECT id, name FROM permissions_groups ORDER BY id ASC'
        );
        
        if ($isApiRequest) {
            return $this->respondJson($response, [
                'user' => $user,
                'groups' => $groups
            ]);
        }
        
        return $this->view->render($response, 'Opanel/users/edit.twig', [
            'user' => $user,
            'groups' => $groups
        ]);
    }
    
    /**
     * 更新使用者資料
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        // 檢查是否為 API 請求
        $isApiRequest = $request->getHeaderLine('Content-Type') === 'application/json';
        
        $userId = (int)$args['id'];
        
        // 獲取請求參數
        if ($isApiRequest) {
            $params = json_decode($request->getBody()->getContents(), true) ?? [];
        } else {
            $params = (array) $request->getParsedBody();
        }
        
        $displayName = trim($params['display_name'] ?? '');
        $groupId = isset($params['group_id']) && $params['group_id'] !== '' ? (int)$params['group_id'] : null;
        $status = isset($params['status']) ? (int)$params['status'] : 1;
        
        // 獲取原始使用者資料
        $oldUser = $this->conn->fetchAssociative(
            'SELECT username, display_name, group_id, status FROM admin_users WHERE id = ?',
            [$userId]
        );
        
        if (!$oldUser) {
            if ($isApiRequest) {
                return $this->respondJson($response, [
                    'success' => false,
                    'message' => '使用者不存在'
                ], 404);
            } else {
                $this->flash->addMessage('danger', '使用者不存在');
                return $response->withHeader('Location', '/opanel/users')->withStatus(302);
            }
        }
        
        // 更新使用者資料
        $this->conn->update('admin_users', [
            'display_name' => $displayName,
            'group_id' => $groupId,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $userId]);
        
        // 記錄操作日誌
        LogUtil::logOperation(
            $_SESSION['opanel_user']['id'],
            $_SESSION['opanel_user']['username'],
            'update',
            'admin_user',
            $oldUser,
            ['id' => $userId, 'display_name' => $displayName, 'group_id' => $groupId, 'status' => $status],
            "更新後台使用者: {$oldUser['username']}"
        );
        
        if ($isApiRequest) {
            return $this->respondJson($response, [
                'success' => true,
                'message' => '使用者資料更新成功'
            ]);
        } else {
            $this->flash->addMessage('success', '使用者資料更新成功');
            return $response->withHeader('Location', '/opanel/users')->withStatus(302);
        }
    }
    
    /**
     * 重設使用者密碼
     */
    public function resetPassword(Request $request, Response $response, array $args): Response
    {
        $userId = (int)$args['id'];
        $params = (array) $request->getParsedBody();
        $password = trim($params['password'] ?? '');
        
        if ($password === '') {
            return $this->respondJson($response, ['success' => false, 'message' => '密碼不能為空'], 400);
        }
        
        // 獲取使用者資料
        $user = $this->conn->fetchAssociative(
            'SELECT username FROM admin_users WHERE id = ?',
            [$userId]
        );
        
        if (!$user) {
            return $this->respondJson($response, ['success' => false, 'message' => '使用者不存在'], 404);
        }
        
        // 更新密碼
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->conn->update('admin_users', [
            'password_hash' => $passwordHash,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $userId]);
        
        // 記錄操作日誌
        LogUtil::logOperation(
            $_SESSION['opanel_user']['id'],
            $_SESSION['opanel_user']['username'],
            'reset_password',
            'admin_user',
            null,
            ['id' => $userId],
            "重設後台使用者密碼: {$user['username']}"
        );
        
        return $this->respondJson($response, ['success' => true, 'message' => '密碼重設成功']);
    }
    
    /**
     * 切換使用者狀態（啟用/停用）
     */
    public function toggleStatus(Request $request, Response $response, array $args): Response
    {
        $userId = (int)$args['id'];
        
        // 獲取使用者資料
        $user = $this->conn->fetchAssociative(
            'SELECT username, status FROM admin_users WHERE id = ?',
            [$userId]
        );
        
        if (!$user) {
            return $this->respondJson($response, ['success' => false, 'message' => '使用者不存在'], 404);
        }
        
        // 切換狀態
        $newStatus = $user['status'] == 1 ? 0 : 1;
        $statusText = $newStatus == 1 ? '啟用' : '停用';
        
        $this->conn->update('admin_users', [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $userId]);
        
        // 記錄操作日誌
        LogUtil::logOperation(
            $_SESSION['opanel_user']['id'],
            $_SESSION['opanel_user']['username'],
            'toggle_status',
            'admin_user',
            ['status' => $user['status']],
            ['id' => $userId, 'status' => $newStatus],
            "$statusText 後台使用者: {$user['username']}"
        );
        
        return $this->respondJson($response, [
            'success' => true, 
            'message' => "使用者已{$statusText}",
            'new_status' => $newStatus
        ]);
    }
    
    /**
     * 刪除使用者
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $userId = (int)$args['id'];
        
        // 獲取使用者資料
        $user = $this->conn->fetchAssociative(
            'SELECT username FROM admin_users WHERE id = ?',
            [$userId]
        );
        
        if (!$user) {
            return $this->respondJson($response, ['success' => false, 'message' => '使用者不存在'], 404);
        }
        
        // 檢查是否為當前登入使用者
        if ($userId === (int)$_SESSION['opanel_user']['id']) {
            return $this->respondJson($response, ['success' => false, 'message' => '無法刪除當前登入的使用者'], 400);
        }
        
        // 刪除使用者
        $this->conn->delete('admin_users', ['id' => $userId]);
        
        // 記錄操作日誌
        LogUtil::logOperation(
            $_SESSION['opanel_user']['id'],
            $_SESSION['opanel_user']['username'],
            'delete',
            'admin_user',
            ['id' => $userId, 'username' => $user['username']],
            null,
            "刪除後台使用者: {$user['username']}"
        );
        
        return $this->respondJson($response, ['success' => true, 'message' => '使用者已刪除']);
    }
}
