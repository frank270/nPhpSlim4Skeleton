<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AccessUpdatePermissionAction extends BaseAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = json_decode((string) $request->getBody(), true);
        $groupId = $data['groupId'] ?? null;
        $funcId = $data['funcId'] ?? null;
        $enabled = isset($data['enabled']) ? (int) $data['enabled'] : null;

        if (!$groupId || !$funcId || !is_numeric($enabled)) {
            return $this->respondJson($response, [
                'success' => false,
                'error' => 'Invalid input'
            ], 400);
        }

        $conn = $this->conn;

        // 檢查是否已存在該筆 group/func 對應
        $exists = $conn->fetchOne(
            'SELECT id FROM permissions_matrix WHERE group_id = ? AND func_id = ?',
            [$groupId, $funcId]
        );

        if ($exists) {
            // 已存在 → 更新
            $conn->update('permissions_matrix', ['enabled' => $enabled], [
                'group_id' => $groupId,
                'func_id' => $funcId
            ]);
        } else {
            // 不存在 → 新增
            $conn->insert('permissions_matrix', [
                'group_id' => $groupId,
                'func_id' => $funcId,
                'enabled' => $enabled,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->respondJson($response, ['success' => true]);
    }

    protected function respondJson(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
