<?php
declare(strict_types=1);

namespace App\Actions;

use App\Actions\BaseAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeAction extends BaseAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $name = $args['name'] ?? 'Guest';

        return $this->view->render($response, 'hello.twig', [
            'name' => $name,
        ]);
    }
    public function landingPage(Request $request, Response $response, array $args): Response
    {
        return $this->view->render($response, 'landing.twig', [
            'title' => 'Coming Soon - 1fBreakfast'
        ]);
    }
}
