<?php
declare(strict_types=1);

namespace App\Actions;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Monolog\Logger;
use Slim\Flash\Messages;
use Doctrine\DBAL\Connection;


class BaseAction
{
    protected Twig $view;
    protected Logger $logger;
    protected Messages $flash;
    protected ContainerInterface $container;
    protected Connection $conn;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->view = $container->get('view');
        $this->logger = $container->get('logger');
        $this->flash = $container->get('flash');
        $this->conn = $container->get(Connection::class);
    }
    protected function respondJson(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
