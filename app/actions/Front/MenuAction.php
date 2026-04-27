<?php
declare(strict_types=1);

namespace App\Actions\Front;

use App\Actions\BaseAction;
use App\Models\MenuCategoryModel;
use App\Models\MenuItemModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MenuAction extends BaseAction
{
    public function __construct(\Psr\Container\ContainerInterface $container)
    {
        parent::__construct($container);
        error_log('DEBUG MenuAction: __construct called. Conn is ' . (isset($this->conn) ? 'SET' : 'UNSET'));
        if (isset($this->conn)) {
             error_log('DEBUG MenuAction: Conn type: ' . get_class($this->conn));
        }
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        $categoryModel = new MenuCategoryModel($this->conn);
        $itemModel = new MenuItemModel($this->conn);

        // Get Query Params
        $params = $request->getQueryParams();
        
        $filters = [
            'status' => 'published',
            'keyword' => $params['keyword'] ?? null,
            'category_id' => $params['category_id'] ?? null,
        ];

        // Fetch published categories
        $categories = $categoryModel->getAll();

        // Fetch items using filters
        $items = $itemModel->paginate($filters, 1000, 0);

        // Group items by category_id
        $itemsByCat = [];
        foreach ($items as $item) {
            $catId = $item['category_id'];
            if (!isset($itemsByCat[$catId])) {
                $itemsByCat[$catId] = [];
            }
            $itemsByCat[$catId][] = $item;
        }

        return $this->view->render($response, 'frontend/menu/index.twig', [
            'categories' => $categories,
            'itemsByCat' => $itemsByCat,
            'filters' => $items, // WRONG: should be $filters array for UI state, or $params
            'queryParams' => $params, // Use this for form values
        ]);
    }

    public function detail(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $itemModel = new MenuItemModel($this->conn);
        $categoryModel = new MenuCategoryModel($this->conn);

        $item = $itemModel->findById($id);

        if (!$item || $item['status'] !== 'published') {
            // Handle not found or not published
             $response->getBody()->write("商品不存在或已下架");
             return $response->withStatus(404);
             // Or redirect: return $response->withHeader('Location', '/menu')->withStatus(302);
        }

        // Get all categories for sidebar or breadcrumb if needed
        $categories = $categoryModel->getAll();

        return $this->view->render($response, 'frontend/menu/detail.twig', [
            'item' => $item,
            'categories' => $categories, // Passed if we want to show sidebar in detail page too, or just for nav
            'shareUrl' => (string) $request->getUri(),
        ]);
    }
}
