<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class AuthAction extends BaseAction
{
    public function showLogin(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'Opanel/login.twig');
    }
}