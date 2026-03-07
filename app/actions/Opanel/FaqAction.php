<?php
declare(strict_types=1);

namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use App\Models\FaqCategoriesModel;
use App\Models\FaqsModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class FaqAction extends BaseAction
{
    private FaqCategoriesModel $categoryModel;
    private FaqsModel $faqModel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->categoryModel = new FaqCategoriesModel($this->conn);
        $this->faqModel = new FaqsModel($this->conn);
    }

    /**
     * 後台 React 容器頁
     */
    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/faqs/index.twig', [
            'title' => '常見問題管理',
            'apiBase' => '/opanel/faqs'
        ]);
    }

    // ==================== 分類相關 API ====================

    /**
     * 取得分類列表
     */
    public function apiListCategories(Request $request, Response $response): Response
    {
        $categories = $this->categoryModel->getAll();

        return $this->respondJson($response, [
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * 取得單一分類
     */
    public function apiGetCategory(Request $request, Response $response, array $args): Response
    {
        $category = $this->categoryModel->findById((int)$args['id']);
        if (!$category) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的分類',
            ], 404);
        }

        return $this->respondJson($response, [
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * 建立分類
     */
    public function apiCreateCategory(Request $request, Response $response): Response
    {
        $data = $this->parseRequestData($request);

        $errors = $this->validateCategory($data, true);
        if (!empty($errors)) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '資料驗證失敗',
                'errors' => $errors,
            ], 400);
        }

        // 檢查 slug 是否重複
        $existing = $this->categoryModel->findBySlug($data['slug']);
        if ($existing) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '該 slug 已被使用',
            ], 400);
        }

        $id = $this->categoryModel->create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
        ]);

        $category = $this->categoryModel->findById($id);
        $this->logAction('新增 FAQ 分類', '常見問題', null, $category);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '分類已建立',
            'data' => $category,
        ], 201);
    }

    /**
     * 更新分類
     */
    public function apiUpdateCategory(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $original = $this->categoryModel->findById($id);
        if (!$original) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的分類',
            ], 404);
        }

        $data = $this->parseRequestData($request);
        $errors = $this->validateCategory($data, false);
        if (!empty($errors)) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '資料驗證失敗',
                'errors' => $errors,
            ], 400);
        }

        // 檢查 slug 是否重複（排除自己）
        if (isset($data['slug']) && $data['slug'] !== $original['slug']) {
            $existing = $this->categoryModel->findBySlug($data['slug']);
            if ($existing) {
                return $this->respondJson($response, [
                    'success' => false,
                    'message' => '該 slug 已被使用',
                ], 400);
            }
        }

        $updateData = [];
        foreach (['name', 'slug', 'sort_order', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        $this->categoryModel->update($id, $updateData);

        $updated = $this->categoryModel->findById($id);
        $this->logAction('更新 FAQ 分類', '常見問題', $original, $updated);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '分類已更新',
            'data' => $updated,
        ]);
    }

    /**
     * 刪除分類
     */
    public function apiDeleteCategory(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $category = $this->categoryModel->findById($id);
        if (!$category) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的分類',
            ], 404);
        }

        $this->categoryModel->delete($id);
        $this->logAction('刪除 FAQ 分類', '常見問題', $category, null);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '分類已刪除',
        ]);
    }

    // ==================== FAQ 相關 API ====================

    /**
     * 取得 FAQ 列表（支援篩選、分頁）
     */
    public function apiList(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = max(1, min(100, (int)($params['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $filters = [
            'category_id' => isset($params['category_id']) ? (int)$params['category_id'] : null,
            'status' => $params['status'] ?? null,
            'is_highlight' => isset($params['is_highlight']) ? (bool)$params['is_highlight'] : null,
            'keyword' => $params['keyword'] ?? null,
        ];

        $items = $this->faqModel->paginate($filters, $perPage, $offset);
        $total = $this->faqModel->count($filters);

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
     * 取得單筆 FAQ
     */
    public function apiGetOne(Request $request, Response $response, array $args): Response
    {
        $faq = $this->faqModel->findById((int)$args['id']);
        if (!$faq) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的問題',
            ], 404);
        }

        return $this->respondJson($response, [
            'success' => true,
            'data' => $faq,
        ]);
    }

    /**
     * 建立 FAQ
     */
    public function apiCreate(Request $request, Response $response): Response
    {
        $data = $this->parseRequestData($request);

        $errors = $this->validateFaq($data, true);
        if (!empty($errors)) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '資料驗證失敗',
                'errors' => $errors,
            ], 400);
        }

        $id = $this->faqModel->create([
            'category_id' => isset($data['category_id']) && $data['category_id'] ? (int)$data['category_id'] : null,
            'question' => $data['question'],
            'answer' => $data['answer'],
            'is_highlight' => isset($data['is_highlight']) ? (bool)$data['is_highlight'] : false,
            'status' => $data['status'] ?? 'draft',
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
        ]);

        $faq = $this->faqModel->findById($id);
        $this->logAction('新增 FAQ', '常見問題', null, $faq);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '問題已建立',
            'data' => $faq,
        ], 201);
    }

    /**
     * 更新 FAQ
     */
    public function apiUpdate(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $original = $this->faqModel->findById($id);
        if (!$original) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的問題',
            ], 404);
        }

        $data = $this->parseRequestData($request);
        $errors = $this->validateFaq($data, false);
        if (!empty($errors)) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '資料驗證失敗',
                'errors' => $errors,
            ], 400);
        }

        $updateData = [];
        foreach (['category_id', 'question', 'answer', 'is_highlight', 'status', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        $this->faqModel->update($id, $updateData);

        $updated = $this->faqModel->findById($id);
        $this->logAction('更新 FAQ', '常見問題', $original, $updated);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '問題已更新',
            'data' => $updated,
        ]);
    }

    /**
     * 刪除 FAQ
     */
    public function apiDelete(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $faq = $this->faqModel->findById($id);
        if (!$faq) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的問題',
            ], 404);
        }

        $this->faqModel->delete($id);
        $this->logAction('刪除 FAQ', '常見問題', $faq, null);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '問題已刪除',
        ]);
    }

    /**
     * 切換狀態
     */
    public function apiToggleStatus(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $faq = $this->faqModel->findById($id);
        if (!$faq) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的問題',
            ], 404);
        }

        $newStatus = $this->faqModel->toggleStatus($id);
        $updated = $this->faqModel->findById($id);
        $this->logAction('切換 FAQ 狀態', '常見問題', $faq, $updated);

        return $this->respondJson($response, [
            'success' => true,
            'message' => '狀態已更新',
            'data' => $updated,
        ]);
    }

    // ==================== 驗證方法 ====================

    protected function validateCategory(array $data, bool $isCreate): array
    {
        $errors = [];

        if ($isCreate || isset($data['name'])) {
            if (empty($data['name'])) {
                $errors['name'] = '分類名稱不能為空';
            } elseif (mb_strlen($data['name']) > 120) {
                $errors['name'] = '分類名稱不能超過 120 個字元';
            }
        }

        if ($isCreate || isset($data['slug'])) {
            if (empty($data['slug'])) {
                $errors['slug'] = 'Slug 不能為空';
            } elseif (mb_strlen($data['slug']) > 120) {
                $errors['slug'] = 'Slug 不能超過 120 個字元';
            } elseif (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
                $errors['slug'] = 'Slug 只能包含小寫字母、數字和連字號';
            }
        }

        return $errors;
    }

    protected function validateFaq(array $data, bool $isCreate): array
    {
        $errors = [];

        if ($isCreate || isset($data['question'])) {
            if (empty($data['question'])) {
                $errors['question'] = '問題不能為空';
            } elseif (mb_strlen($data['question']) > 255) {
                $errors['question'] = '問題不能超過 255 個字元';
            }
        }

        if ($isCreate || isset($data['answer'])) {
            if (empty($data['answer'])) {
                $errors['answer'] = '答案不能為空';
            }
        }

        if (isset($data['category_id']) && $data['category_id']) {
            $category = $this->categoryModel->findById((int)$data['category_id']);
            if (!$category) {
                $errors['category_id'] = '指定的分類不存在';
            }
        }

        if (isset($data['status']) && !in_array($data['status'], ['draft', 'published'], true)) {
            $errors['status'] = '狀態值不正確';
        }

        return $errors;
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

