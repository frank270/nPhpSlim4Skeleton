<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Doctrine\DBAL\Connection;

class AccessUpdatePermissionAction extends BaseAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();

        $groupId = isset($data['groupId']) ? (int)$data['groupId'] : null;
        $funcId  = isset($data['funcId'])  ? (int)$data['funcId']  : null;
        $enabled = isset($data['enabled']) ? (bool)$data['enabled'] : false;

        if (!$groupId || !$funcId) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '缺少必要欄位'
            ]);
        }

        /** @var Connection $db */
        $db = $this->container->get(Connection::class);

        try {
            $exists = $db->fetchOne(
                'SELECT id FROM permissions_matrix WHERE group_id = ? AND func_id = ?',
                [$groupId, $funcId]
            );

            if ($exists) {
                $db->update('permissions_matrix', ['enabled' => $enabled ? 1 : 0], [
                    'group_id' => $groupId,
                    'func_id'  => $funcId
                ]);
            } else {
                $db->insert('permissions_matrix', [
                    'group_id' => $groupId,
                    'func_id'  => $funcId,
                    'enabled'  => $enabled ? 1 : 0
                ]);
            }

            return $this->respondJson($response, [
                'success' => true,
                'message' => '權限已更新'
            ]);
        } catch (\Throwable $e) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '更新失敗：' . $e->getMessage()
            ]);
        }
    }
}
