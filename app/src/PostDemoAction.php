<?php
declare(strict_types=1);

namespace App;

use App\BaseAction;
use App\Models\PostModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class PostDemoAction extends BaseAction
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $postModel = $this->container->get(PostModel::class);
        $posts = $postModel->all();

        return $this->view->render($response, 'posts.twig', [
            'posts' => $posts,
        ]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        $postModel = $this->container->get(PostModel::class);
        $post = $postModel->findById($id);

        return $this->view->render($response, 'post.twig', [
            'post' => $post,
        ]);
    }
}
