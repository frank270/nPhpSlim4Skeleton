<?php
namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use App\Models\ExternalLinksModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Utils\LogUtil;

class ExternalLinkAction extends BaseAction
{
    private ExternalLinksModel $linkModel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->linkModel = new ExternalLinksModel($this->conn);
    }

    /**
     * 後台 React 容器頁
     */
    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/external-links/index.twig', [
            'title' => '外部連結管理',
            'apiBase' => '/opanel/external-links'
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
            'type' => $params['type'] ?? null,
            'is_active' => isset($params['is_active']) ? (int)$params['is_active'] : null,
            'locale' => $params['locale'] ?? null,
            'keyword' => $params['keyword'] ?? null,
        ];

        $items = $this->linkModel->paginate($filters, $perPage, $offset);
        $total = $this->linkModel->count($filters);

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
        $link = $this->linkModel->findById((int)$args['id']);
        if (!$link) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的外部連結',
            ], 404);
        }

        return $this->respondJson($response, [
            'success' => true,
            'data' => $link,
        ]);
    }

    /**
     * 建立
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

        $id = $this->linkModel->create([
            'uuid' => $data['uuid'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'url' => $data['url'],
            'type' => $data['type'] ?? 'default',
            'locale' => $data['locale'] ?? null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'opened_in_new_tab' => isset($data['opened_in_new_tab']) ? (int)$data['opened_in_new_tab'] : 1,
            'metadata' => $data['metadata'] ?? null,
        ]);

        $link = $this->linkModel->findById($id);
        $this->logAction('新增外部連結', '外部連結', null, $link);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '外部連結已建立',
            'data' => $link,
        ], 201);
    }

    /**
     * 更新
     */
    public function apiUpdate(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $original = $this->linkModel->findById($id);
        if (!$original) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的外部連結',
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

        $this->linkModel->update($id, [
            'name' => $data['name'] ?? $original['name'],
            'description' => $data['description'] ?? $original['description'],
            'url' => $data['url'] ?? $original['url'],
            'type' => $data['type'] ?? $original['type'],
            'locale' => array_key_exists('locale', $data) ? $data['locale'] : $original['locale'],
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : $original['is_active'],
            'opened_in_new_tab' => isset($data['opened_in_new_tab']) ? (int)$data['opened_in_new_tab'] : $original['opened_in_new_tab'],
            'metadata' => $data['metadata'] ?? $original['metadata'],
        ]);

        $updated = $this->linkModel->findById($id);
        $this->logAction('更新外部連結', '外部連結', $original, $updated);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '外部連結已更新',
            'data' => $updated,
        ]);
    }

    /**
     * 刪除（軟刪除）
     */
    public function apiDelete(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $link = $this->linkModel->findById($id);
        if (!$link) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的外部連結',
            ], 404);
        }

        $this->linkModel->softDelete($id);
        $this->logAction('刪除外部連結', '外部連結', $link, null);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '外部連結已刪除',
        ]);
    }

    /**
     * 切換啟用狀態
     */
    public function apiToggleActive(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $link = $this->linkModel->findById($id);
        if (!$link) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的外部連結',
            ], 404);
        }

        $data = $this->parseRequestData($request);
        $newState = isset($data['is_active']) ? (int)$data['is_active'] : ($link['is_active'] ? 0 : 1);

        $this->linkModel->update($id, ['is_active' => $newState]);
        $this->logAction('切換外部連結狀態', '外部連結', $link, [
            'id' => $id,
            'name' => $link['name'],
            'is_active' => $newState,
        ]);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '外部連結狀態已更新',
            'data' => [
                'id' => $id,
                'is_active' => $newState,
            ],
        ]);
    }

    private function validate(array $data, bool $isCreate): array
    {
        $errors = [];

        if ($isCreate && empty($data['uuid'])) {
            $errors['uuid'] = '缺少 uuid';
        }

        if (empty($data['name'])) {
            $errors['name'] = '名稱必填';
        }

        if (empty($data['url'])) {
            $errors['url'] = 'URL 必填';
        } elseif (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
            $errors['url'] = 'URL 格式不正確';
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
