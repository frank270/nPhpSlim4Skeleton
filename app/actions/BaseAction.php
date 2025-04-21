<?php
declare(strict_types=1);

namespace App\Actions;

use Psr\Container\ContainerInterface;
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
}
