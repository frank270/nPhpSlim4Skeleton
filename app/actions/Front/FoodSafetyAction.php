<?php
declare(strict_types=1);

namespace App\Actions\Front;

use App\Actions\BaseAction;
use App\Models\FoodSafetyCategoryModel;
use App\Models\FoodSafetyItemModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class FoodSafetyAction extends BaseAction
{
    private FoodSafetyCategoryModel $categoryModel;
    private FoodSafetyItemModel $itemModel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->categoryModel = new FoodSafetyCategoryModel($this->conn);
        $this->itemModel = new FoodSafetyItemModel($this->conn);
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        // Get Query Params
        $params = $request->getQueryParams();
        
        $filters = [
            'status' => 'published',
            'keyword' => $params['keyword'] ?? null,
            'category_id' => $params['category_id'] ?? null,
        ];

        // Fetch published categories
        $categories = $this->categoryModel->getAll();

        // Fetch items using filters
        $items = $this->itemModel->paginate($filters, 1000, 0);

        // Group items by category_id
        $itemsByCat = [];
        foreach ($items as $item) {
            $catId = $item['category_id'];
            if (!isset($itemsByCat[$catId])) {
                $itemsByCat[$catId] = [];
            }
            $itemsByCat[$catId][] = $item;
        }

        return $this->view->render($response, 'frontend/food-safety/index.twig', [
            'categories' => $categories,
            'itemsByCat' => $itemsByCat,
            'queryParams' => $params,
        ]);
    }

    public function detail(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        
        $item = $this->itemModel->findById($id);

        if (!$item || $item['status'] !== 'published') {
             $response->getBody()->write("報告不存在或已下架");
             return $response->withStatus(404);
        }

        return $this->view->render($response, 'frontend/food-safety/detail.twig', [
            'item' => $item,
            'shareUrl' => (string) $request->getUri(),
        ]);
    }
}
