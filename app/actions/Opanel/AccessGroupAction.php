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

    protected function respondJson(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
