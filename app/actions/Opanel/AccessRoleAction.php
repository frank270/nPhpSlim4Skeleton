<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AccessRoleAction extends BaseAction
{
    public function lists(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/access/roles.twig');
    }
    
    public function fetchList(Request $request, Response $response): Response
    {
        $roles = $this->conn->fetchAllAssociative(
            'SELECT id, name FROM permissions_groups ORDER BY id ASC'
        );

        //$this->logAction('list', 'roles', null, null, '取得角色列表');
        return $this->respondJson($response, ['roles' => $roles]);
    }
    
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // 驗證輸入
        if (empty($data['code']) || empty($data['name'])) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '請填寫角色代碼和名稱'
            ], 400);
        }
        
        // 檢查代碼是否已存在
        $exists = $this->conn->fetchOne(
            'SELECT COUNT(*) FROM permissions_groups WHERE code = ?',
            [$data['code']]
        );
        
        if ($exists > 0) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '角色代碼已存在'
            ], 400);
        }
        
        // 新增角色
        $this->conn->executeStatement(
            'INSERT INTO permissions_groups (code, name, memo) VALUES (?, ?, ?)',
            [$data['code'], $data['name'], $data['memo'] ?? null]
        );
        
        $newId = $this->conn->lastInsertId();
        
        return $this->respondJson($response, [
            'success' => true,
            'message' => '角色新增成功',
            'role' => [
                'id' => $newId,
                'code' => $data['code'],
                'name' => $data['name']
            ]
        ]);
    }
    
    public function delete(Request $request, Response $response, array $args): Response
    {
        $roleId = (int)$args['id'] ?? 0;
        
        if ($roleId <= 0) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '無效的角色 ID'
            ], 400);
        }
        
        // 檢查角色是否存在
        $role = $this->conn->fetchAssociative(
            'SELECT id, name FROM permissions_groups WHERE id = ?',
            [$roleId]
        );
        
        if (!$role) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的角色'
            ], 404);
        }
        
        // 檢查是否有使用者正在使用此角色
        $usersCount = $this->conn->fetchOne(
            'SELECT COUNT(*) FROM admin_users WHERE group_id = ?',
            [$roleId]
        );
        
        if ($usersCount > 0) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '無法刪除：此角色正被 ' . $usersCount . ' 個使用者使用中'
            ], 400);
        }
        
        // 刪除角色（資料庫會自動刪除相關的權限設定，因為有設定外鍵約束）
        $this->conn->executeStatement(
            'DELETE FROM permissions_groups WHERE id = ?',
            [$roleId]
        );
        
        return $this->respondJson($response, [
            'success' => true,
            'message' => '角色已成功刪除',
            'roleId' => $roleId
        ]);
    }

    // 之後還可以擴充 list() / save() / toggle() / export() 等 method
}
