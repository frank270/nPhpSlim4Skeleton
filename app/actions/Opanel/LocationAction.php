<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use App\Models\LocationStoresModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class LocationAction extends BaseAction
{
    private LocationStoresModel $storeModel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->storeModel = new LocationStoresModel($this->conn);
    }

    /**
     * 後台 React 容器頁
     */
    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/locations/index.twig', [
            'title' => '門市據點管理',
            'apiBase' => '/opanel/locations'
        ]);
    }

    /**
     * 取得列表（支援篩選、分頁）
     */
    public function apiList(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = max(1, min(100, (int)($params['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $filters = [
            'county' => $params['county'] ?? null,
            'status' => $params['status'] ?? null,
            'keyword' => $params['keyword'] ?? null,
        ];

        $items = $this->storeModel->paginate($filters, $perPage, $offset);
        $total = $this->storeModel->count($filters);

        return $this->respondJson($response, [
            'success' => true,
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * 取得單筆資料
     */
    public function apiGetOne(Request $request, Response $response, array $args): Response
    {
        $store = $this->storeModel->findById((int)$args['id']);
        if (!$store) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的門市',
            ], 404);
        }

        return $this->respondJson($response, [
            'success' => true,
            'data' => $store,
        ]);
    }

    /**
     * 取得縣市列表（從門市資料中提取不重複縣市）
     */
    public function apiGetCounties(Request $request, Response $response): Response
    {
        $counties = $this->storeModel->getCounties();

        return $this->respondJson($response, [
            'success' => true,
            'data' => $counties,
        ]);
    }

    /**
     * 建立門市
     */
    public function apiCreate(Request $request, Response $response): Response
    {
        $data = $this->parseRequestData($request);

        $errors = $this->validate($data, true);
        if (!empty($errors)) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '資料驗證失敗',
                'errors' => $errors,
            ], 400);
        }

        $id = $this->storeModel->create([
            'name' => $data['name'],
            'county' => $data['county'] ?? null,
            'district' => $data['district'] ?? null,
            'zipcode' => $data['zipcode'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'map_link_id' => $data['map_link_id'] ?? null,
            'order_link_id' => $data['order_link_id'] ?? null,
            'status' => $data['status'] ?? 'open',
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
            'notes' => $data['notes'] ?? null,
        ]);

        $store = $this->storeModel->findById($id);
        $this->logAction('新增門市', '門市據點', null, $store);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '門市已建立',
            'data' => $store,
        ], 201);
    }

    /**
     * 更新門市
     */
    public function apiUpdate(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $original = $this->storeModel->findById($id);
        if (!$original) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的門市',
            ], 404);
        }

        $data = $this->parseRequestData($request);
        $errors = $this->validate($data, false);
        if (!empty($errors)) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '資料驗證失敗',
                'errors' => $errors,
            ], 400);
        }

        $updateData = [];
        foreach ([
            'name', 'county', 'district', 'zipcode', 'address', 'phone',
            'latitude', 'longitude', 'map_link_id', 'order_link_id',
            'status', 'sort_order', 'notes'
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        $this->storeModel->update($id, $updateData);

        $updated = $this->storeModel->findById($id);
        $this->logAction('更新門市', '門市據點', $original, $updated);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '門市已更新',
            'data' => $updated,
        ]);
    }

    /**
     * 刪除門市
     */
    public function apiDelete(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $store = $this->storeModel->findById($id);
        if (!$store) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的門市',
            ], 404);
        }

        $this->storeModel->delete($id);
        $this->logAction('刪除門市', '門市據點', $store, null);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '門市已刪除',
        ]);
    }

    /**
     * 切換營業狀態
     */
    public function apiToggleStatus(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $store = $this->storeModel->findById($id);
        if (!$store) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的門市',
            ], 404);
        }

        $data = $this->parseRequestData($request);
        $validStatuses = ['open', 'pause', 'closed'];
        $newStatus = $data['status'] ?? null;

        if ($newStatus === null || !in_array($newStatus, $validStatuses, true)) {
            // 如果沒有提供狀態，則循環切換
            $statusMap = ['open' => 'pause', 'pause' => 'closed', 'closed' => 'open'];
            $newStatus = $statusMap[$store['status']] ?? 'open';
        }

        $this->storeModel->update($id, ['status' => $newStatus]);
        $this->logAction('切換門市狀態', '門市據點', $store, [
            'id' => $id,
            'name' => $store['name'],
            'status' => $newStatus,
        ]);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '門市狀態已更新',
            'data' => [
                'id' => $id,
                'status' => $newStatus,
            ],
        ]);
    }

    private function validate(array $data, bool $isCreate): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = '門市名稱必填';
        }

        if (!empty($data['status']) && !in_array($data['status'], ['open', 'pause', 'closed'], true)) {
            $errors['status'] = '營業狀態不正確';
        }

        if (isset($data['latitude']) && $data['latitude'] !== null) {
            if (!is_numeric($data['latitude']) || (float)$data['latitude'] < -90 || (float)$data['latitude'] > 90) {
                $errors['latitude'] = '緯度格式不正確';
            }
        }

        if (isset($data['longitude']) && $data['longitude'] !== null) {
            if (!is_numeric($data['longitude']) || (float)$data['longitude'] < -180 || (float)$data['longitude'] > 180) {
                $errors['longitude'] = '經度格式不正確';
            }
        }

        return array_filter($errors);
    }

    private function parseRequestData(Request $request): array
    {
        $data = $request->getParsedBody();

        if (is_array($data) && !empty($data)) {
            return $data;
        }

        $contentType = $request->getHeaderLine('Content-Type');
        if (stripos($contentType, 'application/json') !== false) {
            $body = (string)$request->getBody();
            if ($body !== '') {
                $decoded = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return is_array($data) ? $data : [];
    }
}

