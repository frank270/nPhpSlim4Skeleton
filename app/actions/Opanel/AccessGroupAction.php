<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AccessGroupAction extends BaseAction
{
    public function matrix(Request $request, Response $response, array $args): Response
    {
        $groupId = (int) ($args['groupId'] ?? 0);

        if ($groupId <= 0) {
            return $this->respondJson($response, [
                'success' => false,
                'error' => 'Invalid groupId'
            ], 400);
        }

        $funcIds = $this->conn->fetchFirstColumn(
            'SELECT func_id FROM permissions_matrix WHERE group_id = ? AND enabled = 1',
            [$groupId]
        );

        return $this->respondJson($response, [
            'funcIds' => $funcIds
        ]);
    }


    public function permissions(Request $request, Response $response, array $args): Response
    {
        $groupId = (int)($args['groupId'] ?? 0);

        if ($groupId <= 0) {
            return $this->respondJson($response, [
                'success' => false,
                'error' => 'Invalid groupId'
            ], 400);
        }

        $permissions = $this->conn->fetchAllAssociative(
            'SELECT
                f.id,
                f.name,
                f.code,
                f.controller,
                f.method,
                f.type,
                COALESCE(m.enabled, 0) AS enabled
            FROM permissions_ctrl_func f
            LEFT JOIN permissions_matrix m
            ON m.func_id = f.id AND m.group_id = ?
            ORDER BY f.id ASC',
            [$groupId]
        );

        return $this->respondJson($response, [
            'permissions' => $permissions
        ]);
    }

}
