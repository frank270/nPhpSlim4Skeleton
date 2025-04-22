<?php
namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class AccessRefineAction extends BaseAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            return $this->handlePost($request, $response);
        }

        return $this->handleGet($response);
    }

    private function handleGet(Response $response): Response
    {
        $permissions = $this->conn->createQueryBuilder()
            ->select('id', 'code', 'name', 'controller', 'method', 'type', 'created_at')
            ->from('permissions_ctrl_func')
            ->where("name LIKE '%__%'")
            ->orderBy('id', 'ASC')
            ->fetchAllAssociative();

        return $this->view->render($response, 'opanel/access/refine-names.twig', [
            'permissions' => $permissions
        ]);
    }

    private function handlePost(Request $request, Response $response): Response
    {
        $payload = $request->getParsedBody()['json'] ?? '';
        $items = json_decode($payload, true);

        $success = 0;
        $error = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                if (!isset($item['code'], $item['name'])) {
                    $error++;
                    continue;
                }

                try {
                    $this->conn->update('permissions_ctrl_func', [
                        'name' => $item['name'],
                    ], [
                        'code' => $item['code'],
                    ]);
                    $success++;
                } catch (\Throwable $e) {
                    $this->logger->error('Permission name update failed', [
                        'code' => $item['code'],
                        'error' => $e->getMessage()
                    ]);
                    $error++;
                }
            }
        }

        $this->flash->addMessage('info', "更新完成：成功 $success 筆，失敗 $error 筆");

        return $response
            ->withHeader('Location', '/opanel/access/refine-names')
            ->withStatus(302);
    }
}
