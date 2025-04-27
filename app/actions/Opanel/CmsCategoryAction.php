<?php
namespace App\Actions\Opanel;

use App\Actions\BaseAction;
use App\Models\CmsCategoryModel;
use App\Utils\LogUtil;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class CmsCategoryAction extends BaseAction
{
    private CmsCategoryModel $categoryModel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->categoryModel = new CmsCategoryModel($this->conn);
    }

    /**
     * 顯示分類列表頁面 (提供React渲染的容器)
     */
    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'opanel/cms/categories/index.twig', [
            'title' => 'CMS 分類管理',
            'apiUrl' => '/opanel/cms/categories/list'
        ]);
    }

    /**
     * API: 獲取所有分類數據
     */
    public function apiGetAll(Request $request, Response $response): Response
    {
        try {
            $categories = $this->categoryModel->getAllWithHierarchy();
            return $this->respondJson($response, [
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: 獲取單個分類數據
     */
    public function apiGetOne(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        
        try {
            $category = $this->categoryModel->findById($id);
            
            if (!$category) {
                return $this->respondJson($response, [
                    'success' => false,
                    'message' => '找不到指定的分類'
                ], 404);
            }
            
            return $this->respondJson($response, [
                'success' => true,
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: 創建新分類
     */
    public function apiCreate(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // 驗證數據
        if (empty($data['name']) || empty($data['slug'])) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '名稱和標識不能為空',
                'errors' => [
                    'name' => empty($data['name']) ? '名稱不能為空' : null,
                    'slug' => empty($data['slug']) ? '標識不能為空' : null
                ]
            ], 400);
        }
        
        // 檢查標識是否唯一
        $existingCategory = $this->categoryModel->findBySlug($data['slug']);
        if ($existingCategory) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '此標識已被使用',
                'errors' => [
                    'slug' => '此標識已被使用，請選擇其他標識'
                ]
            ], 400);
        }
        
        // 創建分類
        try {
            $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
            $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : true;
            
            $id = $this->categoryModel->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'parent_id' => $parentId,
                'is_active' => $isActive ? 1 : 0,
                'sort_order' => $data['sort_order'] ?? 0
            ]);
            
            $newCategory = $this->categoryModel->findById($id);
            
            // 記錄操作日誌
            $this->logAction('CMS 新增分類', '分類', null, [
                'id' => $id,
                'name' => $data['name'],
                'slug' => $data['slug']
            ]);
            
            return $this->respondJson($response, [
                'success' => true,
                'message' => '分類已成功建立',
                'data' => $newCategory
            ]);
        } catch (\Exception $e) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '建立分類時發生錯誤：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: 更新分類
     */
    public function apiUpdate(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        
        // 驗證數據
        if (empty($data['name']) || empty($data['slug'])) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '名稱和標識不能為空',
                'errors' => [
                    'name' => empty($data['name']) ? '名稱不能為空' : null,
                    'slug' => empty($data['slug']) ? '標識不能為空' : null
                ]
            ], 400);
        }
        
        $category = $this->categoryModel->findById($id);
        if (!$category) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '找不到指定的分類'
            ], 404);
        }
        
        // 檢查標識是否唯一 (排除當前記錄)
        $existingCategory = $this->categoryModel->findBySlug($data['slug']);
        if ($existingCategory && $existingCategory['id'] != $id) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '此標識已被使用',
                'errors' => [
                    'slug' => '此標識已被使用，請選擇其他標識'
                ]
            ], 400);
        }
        
        // 防止將自己設為父分類
        if (!empty($data['parent_id']) && (int)$data['parent_id'] === $id) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '無法將分類設為自己的父分類',
                'errors' => [
                    'parent_id' => '無法將分類設為自己的父分類'
                ]
            ], 400);
        }
        
        // 檢查是否會形成循環引用
        if (!empty($data['parent_id'])) {
            $parentId = (int)$data['parent_id'];
            $currentParent = $this->categoryModel->findById($parentId);
            
            while ($currentParent && $currentParent['parent_id']) {
                if ((int)$currentParent['parent_id'] === $id) {
                    return $this->respondJson($response, [
                        'success' => false,
                        'message' => '這會導致分類循環引用',
                        'errors' => [
                            'parent_id' => '這會導致分類循環引用'
                        ]
                    ], 400);
                }
                $currentParent = $this->categoryModel->findById($currentParent['parent_id']);
            }
        }
        
        // 更新分類
        try {
            $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
            $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : $category['is_active'];
            
            $this->categoryModel->update($id, [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'parent_id' => $parentId,
                'is_active' => $isActive ? 1 : 0,
                'sort_order' => $data['sort_order'] ?? $category['sort_order']
            ]);
            
            // 記錄操作日誌
            $this->logAction('CMS 更新分類', '分類', $id, [
                'name' => $data['name'],
                'slug' => $data['slug']
            ]);
            
            return $this->respondJson($response, [
                'success' => true,
                'message' => '分類已成功更新'
            ]);
        } catch (\Exception $e) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '更新分類時發生錯誤：' . $e->getMessage()
            ], 500);
        }
        
        // 記錄操作日誌
        $this->logAction('CMS 更新分類', '分類', $id, [
            'name' => $data['name'],
            'slug' => $data['slug']
        ]);
        
        return $response->withJson([
            'success' => true,
            'message' => '分類已成功更新'
        ]);
    }

    /**
     * API: 刪除分類
     */
    public function apiDelete(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        
        try {
            $this->categoryModel->delete($id);
            
            // 記錄操作日誌
            $this->logAction('CMS 刪除分類', '分類', $id, [
                'name' => $request->getParsedBody()['name'],
                'slug' => $request->getParsedBody()['slug']
            ]);
            
            return $this->respondJson($response, [
                'success' => true,
                'message' => '分類已成功刪除'
            ]);
        } catch (\Exception $e) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '刪除分類時發生錯誤：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: 切換分類啟用狀態
     */
    public function apiToggleActive(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $data = $request->getParsedBody();
        
        try {
            $category = $this->categoryModel->findById($id);
            
            if (!$category) {
                return $this->respondJson($response, [
                    'success' => false,
                    'message' => '找不到指定的分類'
                ], 404);
            }
            
            $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : !$category['is_active'];
            
            // 更新狀態
            $this->categoryModel->toggleActive($id, $isActive);
            
            // 記錄操作日誌
            LogUtil::adminLog('CMS 切換分類狀態', [
                'id' => $id,
                'name' => $category['name'],
                'is_active' => $isActive
            ]);
            
            return $this->respondJson($response, [
                'success' => true,
                'message' => '分類狀態已更新',
                'data' => [
                    'id' => $id,
                    'is_active' => $isActive
                ]
            ]);
        } catch (\Exception $e) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '更新分類狀態時發生錯誤：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: 批量更新分類排序
     */
    public function apiUpdateOrder(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        if (empty($data['categories']) || !is_array($data['categories'])) {
            return $this->respondJson($response, [
                'success' => false,
                'message' => '未提供有效的分類排序數據'
            ], 400);
        }
        
        try {
            $this->db->beginTransaction();
            
            foreach ($data['categories'] as $item) {
                if (isset($item['id'], $item['sort_order'])) {
                    $this->categoryModel->update((int)$item['id'], [
                        'sort_order' => (int)$item['sort_order']
                    ]);
                }
            }
            
            $this->db->commit();
            
            // 記錄操作日誌
            LogUtil::adminLog('CMS 更新分類排序', [
                'count' => count($data['categories'])
            ]);
            
            return $this->respondJson($response, [
                'success' => true,
                'message' => '分類排序已更新'
            ]);
        } catch (\Exception $e) {
            $this->conn->rollBack();
            
            return $this->respondJson($response, [
                'success' => false,
                'message' => '更新分類排序時發生錯誤：' . $e->getMessage()
            ], 500);
        }
    }
}