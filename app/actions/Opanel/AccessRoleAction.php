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

    // 之後還可以擴充 list() / save() / toggle() / export() 等 method
}
