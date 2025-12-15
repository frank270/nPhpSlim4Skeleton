<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use App\Models\MenuCategoryModel;
use App\Models\MenuItemModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class MenuAction extends BaseAction
{
    private MenuCategoryModel $categoryModel;
    private MenuItemModel $itemModel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->categoryModel = new MenuCategoryModel($this->conn);
        $this->itemModel = new MenuItemModel($this->conn);
    }

    // --- Page Rendering ---

    public function pageCategories(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/menu/categories.twig', [
            'title' => '菜單分類管理',
            'apiBase' => '/opanel/menu/categories'
        ]);
    }

    public function pageItems(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/menu/items.twig', [
            'title' => '菜單商品管理',
            'apiBase' => '/opanel/menu/items',
            'apiCategoryBase' => '/opanel/menu/categories', // For dropdowns
        ]);
    }

    // --- Category Actions ---

    public function listCategories(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'keyword' => $params['keyword'] ?? null,
        ];

        $items = $this->categoryModel->paginate($filters, $limit, $offset);
        $total = $this->categoryModel->count($filters);

        return $this->respondJson($response, [
            'success' => true,
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit),
            ],
        ]);
    }

    public function getCategory(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryModel->findById((int)$args['id']);
        if (!$category) {
            return $this->respondJson($response, ['success' => false, 'message' => 'Category not found'], 404);
        }
        return $this->respondJson($response, ['success' => true, 'data' => $category]);
    }

    public function createCategory(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        // DEBUG: Check what's received
        error_log('DEBUG MenuAction createCategory: ParsedBody: ' . print_r($data, true));
        error_log('DEBUG MenuAction createCategory: $_POST: ' . print_r($_POST, true));
        error_log('DEBUG MenuAction createCategory: Content-Type: ' . $request->getHeaderLine('Content-Type'));
        // Validation (Basic)
        if (empty($data['name']) || empty($data['slug'])) {
            return $this->respondJson($response, ['success' => false, 'message' => 'Name and Slug are required'], 400);
        }

        try {
            $id = $this->categoryModel->create($data);
            return $this->respondJson($response, ['success' => true, 'data' => ['id' => $id]]);
        } catch (\Exception $e) {
            return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateCategory(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        try {
            $updated = $this->categoryModel->update((int)$args['id'], $data);
            if (!$updated) {
                 return $this->respondJson($response, ['success' => false, 'message' => 'No changes made or record not found']);
            }
            return $this->respondJson($response, ['success' => true]);
        } catch (\Exception $e) {
             return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteCategory(Request $request, Response $response, array $args): Response
    {
        try {
            $deleted = $this->categoryModel->delete((int)$args['id']);
             if (!$deleted) {
                 return $this->respondJson($response, ['success' => false, 'message' => 'Record not found']);
            }
            return $this->respondJson($response, ['success' => true]);
        } catch (\Exception $e) {
             return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- Item Actions ---

    public function listItems(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'keyword' => $params['keyword'] ?? null,
            'category_id' => $params['category_id'] ?? null,
            'status' => $params['status'] ?? null,
        ];

        $items = $this->itemModel->paginate($filters, $limit, $offset);
        $total = $this->itemModel->count($filters);

        return $this->respondJson($response, [
            'success' => true,
            'data' => $items,
             'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit),
            ],
        ]);
    }

    public function getItem(Request $request, Response $response, array $args): Response
    {
        $item = $this->itemModel->findById((int)$args['id']);
        if (!$item) {
             return $this->respondJson($response, ['success' => false, 'message' => 'Item not found'], 404);
        }
        return $this->respondJson($response, ['success' => true, 'data' => $item]);
    }

    public function createItem(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if (empty($data['name']) || empty($data['category_id'])) {
             return $this->respondJson($response, ['success' => false, 'message' => 'Name and Category are required'], 400);
        }

        // Sanitize price_sale
        if (isset($data['price_sale']) && $data['price_sale'] === '') {
            $data['price_sale'] = null;
        }

        try {
            $id = $this->itemModel->create($data);
            return $this->respondJson($response, ['success' => true, 'data' => ['id' => $id]]);
        } catch (\Exception $e) {
            return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateItem(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        
        // Sanitize price_sale
        if (isset($data['price_sale']) && $data['price_sale'] === '') {
            $data['price_sale'] = null;
        }

        try {
            $updated = $this->itemModel->update((int)$args['id'], $data);
            if (!$updated) {
                 return $this->respondJson($response, ['success' => false, 'message' => 'No changes made or record not found']);
            }
            return $this->respondJson($response, ['success' => true]);
        } catch (\Exception $e) {
             return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteItem(Request $request, Response $response, array $args): Response
    {
        try {
            $deleted = $this->itemModel->delete((int)$args['id']);
            if (!$deleted) {
                 return $this->respondJson($response, ['success' => false, 'message' => 'Record not found']);
            }
            return $this->respondJson($response, ['success' => true]);
        } catch (\Exception $e) {
             return $this->respondJson($response, ['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAllCategories(Request $request, Response $response): Response
    {
        $categories = $this->categoryModel->getAll();
        return $this->respondJson($response, ['success' => true, 'data' => $categories]);
    }
}
