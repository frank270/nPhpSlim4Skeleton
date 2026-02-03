<?php
namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use App\Models\FranchiseInquiriesModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FranchiseInquiryAction extends BaseAction
{
    private FranchiseInquiriesModel $model;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->model = new FranchiseInquiriesModel($this->conn);
    }

    /**
     * 渲染列表頁面容器
     */
    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/franchise-inquiries/index.twig');
    }

    /**
     * API: 取得列表資料
     */
    public function list(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'keyword' => $params['keyword'] ?? null,
            'status' => $params['status'] ?? null,
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

    /**
     * API: 切換狀態
     */
    public function toggleStatus(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        
        // Use standard form data parsing
        $data = (array)$request->getParsedBody();
        $newStatus = (int)($data['status'] ?? 0);

        if ($this->model->updateStatus($id, $newStatus)) {
             // 記錄操作日誌 (可選)
             // LogUtil::logOperation(...) 

            return $this->respondJson($response, ['success' => true, 'message' => 'Status updated']);
        }

        return $this->respondJson($response, ['success' => false, 'message' => 'Update failed'], 500);
    }
}
