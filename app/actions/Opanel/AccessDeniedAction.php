<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class AccessDeniedAction extends BaseAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $opanelUser = $_SESSION['opanel_user'] ?? null;

        return $this->view->render($response, 'opanel/access_denied.twig', [
            'title' => '無權限訪問',
            'display_name' => $opanelUser['display_name'] ?? '未知使用者',
            'requested_url' => $args['requested_url'] ?? '',
        ])->withStatus(403);
    }
}