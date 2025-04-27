<?php
namespace App\Actions\Opanel;

use App\Models\CmsCategoryModel;
use App\Utils\LogUtil;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Doctrine\DBAL\Connection;
use Slim\Flash\Messages;

class CmsCategoryAction
{
    private Twig $twig;
    private Connection $db;
    private Messages $flash;
    private CmsCategoryModel $categoryModel;

    public function __construct(Twig $twig, Connection $db, Messages $flash)
    {
        $this->twig = $twig;
        $this->db = $db;
        $this->flash = $flash;
        $this->categoryModel = new CmsCategoryModel($db);
    }

    /**
     * 顯示分類列表頁面 (提供React渲染的容器)
     */
    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'opanel/cms/categories/index.twig', [
            'title' => 'CMS 分類管理',
            'apiUrl' => '/api/opanel/cms/categories'
        ]);
    }

    /**
     * API: 獲取所有分類數據
     */
    public function apiGetAll(Request $request, Response $response): Response
    {
        try {
            $categories = $this->categoryModel->getAllWithHierarchy();
            return $response->withJson([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return $response->withJson([
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
                return $response->withJson([
                    'success' => false,
                    'message' => '找不到指定的分類'
                ], 404);
            }
            
            return $response->withJson([
                'success' => true,
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return $response->withJson([
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
            return $response->withJson([
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
            return $response->withJson([
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
            LogUtil::adminLog('CMS 新增分類', [
                'id' => $id,
                'name' => $data['name'],
                'slug' => $data['slug']
            ]);
            
            return $response->withJson([
                'success' => true,
                'message' => '分類已成功建立',
                'data' => $newCategory
            ]);
        } catch (\Exception $e) {
            return $response->withJson([
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
            return $response->withJson([
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
            return $response->withJson([
                'success' => false,
                'message' => '找不到指定的分類'
            ], 404);
        }
        
        // 檢查標識是否唯一 (排除當前記錄)
        $existingCategory = $this->categoryModel->findBySlug($data['slug']);
        if ($existingCategory && $existingCategory['id'] != $id) {
            return $response->withJson([
                'success' => false,
                'message' => '此標識已被使用',
                'errors' => [
                    'slug' => '此標識已被使用，請選擇其他標識'
                ]
            ], 400);
        }
        
        // 防止將自己設為父分類
        if (!empty($data['parent_id']) && (int)$data['parent_id'] === $id) {
            return $response->withJson([
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
                    return $response->withJson([
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
            
            $updatedCategory = $this->categoryModel->findById($id);
            
            // 記錄操作日誌
            LogUtil::adminLog('CMS 更新分類', [
                'id' => $id,
                'name' => $data['name'],
                'slug' => $data['slug']
            ]);
            
            return $response->withJson([
                'success' => true,
                'message' => '分類已成功更新',
                'data' => $updatedCategory
            ]);
        } catch (\Exception $e) {
            return $response->withJson([
                'success' => false,
                'message' => '更新分類時發生錯誤：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: 刪除分類
     */
    public function apiDelete(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        
        try {
            $category = $this->categoryModel->findById($id);
            
            if (!$category) {
                return $response->withJson([
                    'success' => false,
                    'message' => '找不到指定的分類'
                ], 404);
            }
            
            // 檢查是否有子分類
            $childrenCount = $this->db->createQueryBuilder()
                ->select('COUNT(id)')
                ->from('cms_categories')
                ->where('parent_id = :parent_id')
                ->setParameter('parent_id', $id)
                ->executeQuery()
                ->fetchOne();
                
            if ($childrenCount > 0) {
                return $response->withJson([
                    'success' => false,
                    'message' => '無法刪除此分類，因為它有 ' . $childrenCount . ' 個子分類'
                ], 400);
            }
            
            // 檢查是否有內容使用此分類
            $contentCount = $this->db->createQueryBuilder()
                ->select('COUNT(cc.content_id)')
                ->from('cms_content_categories', 'cc')
                ->where('cc.category_id = :category_id')
                ->setParameter('category_id', $id)
                ->executeQuery()
                ->fetchOne();
                
            if ($contentCount > 0) {
                return $response->withJson([
                    'success' => false,
                    'message' => '無法刪除此分類，因為有 ' . $contentCount . ' 個內容正在使用它'
                ], 400);
            }
            
            // 刪除分類
            $this->categoryModel->delete($id);
            
            // 記錄操作日誌
            LogUtil::adminLog('CMS 刪除分類', [
                'id' => $id,
                'name' => $category['name'],
                'slug' => $category['slug']
            ]);
            
            return $response->withJson([
                'success' => true,
                'message' => '分類已成功刪除'
            ]);
        } catch (\Exception $e) {
            return $response->withJson([
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
                return $response->withJson([
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
            
            return $response->withJson([
                'success' => true,
                'message' => '分類狀態已更新',
                'data' => [
                    'id' => $id,
                    'is_active' => $isActive
                ]
            ]);
        } catch (\Exception $e) {
            return $response->withJson([
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
            return $response->withJson([
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
            
            return $response->withJson([
                'success' => true,
                'message' => '分類排序已更新'
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            
            return $response->withJson([
                'success' => false,
                'message' => '更新分類排序時發生錯誤：' . $e->getMessage()
            ], 500);
        }
    }
}