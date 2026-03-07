<?php
declare(strict_types=1);

namespace App\Actions\Front;

use App\Actions\BaseAction;
use App\Models\CmsPostModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class NewsAction extends BaseAction
{
    private CmsPostModel $postModel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->postModel = new CmsPostModel($this->conn);
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = 12; // Grid layout usually works well with 12
        $offset = ($page - 1) * $limit;

        $tagMap = [
            'event' => '活動新品',
            'store' => '展店消息',
            'member' => '會員活動',
        ];

        $filterParam = $params['filter'] ?? null;
        $tagSearch = isset($tagMap[$filterParam]) ? $tagMap[$filterParam] : null;

        $filters = [
            'status' => 'published',
            'type' => 'news',
            'keyword' => $params['keyword'] ?? null,
            'tag_like' => $tagSearch,
        ];

        $total = $this->postModel->count($filters);
        $posts = $this->postModel->paginate($filters, $limit, $offset);
        
        // Calculate total pages
        $totalPages = ceil($total / $limit);

        return $this->view->render($response, 'frontend/news/index.twig', [
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function detail(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'];
        
        $post = $this->postModel->findBySlug($slug);

        if (!$post || $post['status'] !== 'published' || $post['type'] !== 'news') {
             // Try to find by ID if slug not found (backward compatibility if needed, or just 404)
             // strict slug usage is better for SEO
             $response->getBody()->write("文章不存在或已下架");
             return $response->withStatus(404);
        }

        return $this->view->render($response, 'frontend/news/detail.twig', [
            'post' => $post,
        ]);
    }

    public function privacyPolicy(Request $request, Response $response, array $args): Response
    {
        return $this->renderStaticPage($response, 'privacy-policy');
    }

    public function termsOfUse(Request $request, Response $response, array $args): Response
    {
        return $this->renderStaticPage($response, 'terms-of-use');
    }

    private function renderStaticPage(Response $response, string $slug): Response
    {
        $post = $this->postModel->findBySlug($slug);

        // Allow 'statics' type or 'news' type, as long asslug matches
        if (!$post || $post['status'] !== 'published') {
             $response->getBody()->write("頁面不存在或已下架");
             return $response->withStatus(404);
        }

        return $this->view->render($response, 'frontend/news/detail.twig', [
            'post' => $post,
            'hideShare' => true,
        ]);
    }
}
