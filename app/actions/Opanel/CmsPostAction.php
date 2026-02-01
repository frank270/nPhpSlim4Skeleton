<?php
namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use App\Models\CmsPostModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CmsPostAction extends BaseAction
{
    private CmsPostModel $model;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->model = new CmsPostModel($this->conn);
    }

    public function pageIndex(Request $request, Response $response): Response
    {
        // Render the React container
        return $this->view->render($response, 'opanel/cms/index.twig', [
            'title' => '文章管理',
            'apiBase' => '/opanel/cms/posts'
        ]);
    }

    public function historyIndex(Request $request, Response $response): Response
    {
        // Render the React container with forcedType = history
        return $this->view->render($response, 'opanel/cms/index.twig', [
            'title' => '品牌歷程',
            'apiBase' => '/opanel/cms/posts',
            'forcedType' => 'history'
        ]);
    }

    public function list(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'keyword' => $params['keyword'] ?? null,
            'status' => $params['status'] ?? null,
            'type' => $params['type'] ?? null, // Fix: Capture type filter
        ];

        $items = $this->model->paginate($filters, $limit, $offset);
        $total = $this->model->count($filters);

        return $this->respondJson($response, [
            'success' => true,
            'data' => $items,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
            ]
        ]);
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $item = $this->model->findById((int) $args['id']);
        
        if (!$item) {
            return $this->respondJson($response, ['success' => false, 'message' => 'Post not found'], 404);
        }

        return $this->respondJson($response, ['success' => true, 'data' => $item]);
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // Basic validation
        if (empty($data['title']) || empty($data['slug'])) {
            return $this->respondJson($response, ['success' => false, 'message' => 'Title and Slug are required'], 400);
        }

        // Check unique slug
        if ($this->model->findBySlug($data['slug'])) {
            return $this->respondJson($response, ['success' => false, 'message' => 'Slug already exists'], 400);
        }

        try {
            $id = $this->model->create($data);
            return $this->respondJson($response, ['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $request->getParsedBody();

        // Check unique slug (exclude self)
        if (!empty($data['slug'])) {
            $exist = $this->model->findBySlug($data['slug']);
            if ($exist && $exist['id'] !== $id) {
                return $this->respondJson($response, ['success' => false, 'message' => 'Slug already exists'], 400);
            }
        }

        if ($this->model->update($id, $data)) {
            return $this->respondJson($response, ['success' => true]);
        }

        return $this->respondJson($response, ['success' => false, 'message' => 'Update failed'], 500);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        if ($this->model->delete((int) $args['id'])) {
            return $this->respondJson($response, ['success' => true]);
        }
        return $this->respondJson($response, ['success' => false, 'message' => 'Delete failed'], 500);
    }
}
