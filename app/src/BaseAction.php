<?php
declare(strict_types=1);

namespace App;

use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Monolog\Logger;
use Slim\Flash\Messages;

class BaseAction
{
    protected Twig $view;
    protected Logger $logger;
    protected Messages $flash;

    public function __construct(ContainerInterface $container)
    {
        $this->view = $container->get('view');
        $this->logger = $container->get('logger');
        $this->flash = $container->get('flash');
    }
}
