<?php
namespace App\Models;

use Doctrine\DBAL\Connection;

class CmsCategoryModel
{
    private Connection $db;
    private string $table = 'cms_categories';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * 獲取所有分類
     */
    public function all()
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->orderBy('sort_order', 'ASC')
            ->addOrderBy('name', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * 獲取所有分類並組織成樹狀結構
     */
    public function getAllWithHierarchy()
    {
        $categories = $this->all();
        $categoriesById = [];
        $rootCategories = [];

        // 首先，按ID建立索引
        foreach ($categories as $category) {
            $categoriesById[$category['id']] = $category;
            $categoriesById[$category['id']]['children'] = [];
        }

        // 然後，構建樹結構
        foreach ($categories as $category) {
            $id = $category['id'];
            
            if (!empty($category['parent_id']) && isset($categoriesById[$category['parent_id']])) {
                $categoriesById[$category['parent_id']]['children'][] = &$categoriesById[$id];
            } else {
                $rootCategories[] = &$categoriesById[$id];
            }
        }

        return $rootCategories;
    }

    /**
     * 根據ID查詢分類
     */
    public function findById(int $id)
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative() ?: null;
    }

    /**
     * 根據標識查詢分類
     */
    public function findBySlug(string $slug)
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('slug = :slug')
            ->setParameter('slug', $slug)
            ->executeQuery()
            ->fetchAssociative() ?: null;
    }

    /**
     * 獲取活躍的分類
     */
    public function getActive()
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('is_active = 1')
            ->orderBy('sort_order', 'ASC')
            ->addOrderBy('name', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * 創建新分類
     */
    public function create(array $data): int
    {
        $this->db->insert($this->table, [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'sort_order' => $data['sort_order'] ?? 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * 更新分類
     */
    public function update(int $id, array $data): bool
    {
        $updateData = [
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['slug'])) {
            $updateData['slug'] = $data['slug'];
        }

        if (array_key_exists('description', $data)) {
            $updateData['description'] = $data['description'];
        }

        if (array_key_exists('parent_id', $data)) {
            $updateData['parent_id'] = $data['parent_id'];
        }

        if (isset($data['is_active'])) {
            $updateData['is_active'] = $data['is_active'];
        }

        if (isset($data['sort_order'])) {
            $updateData['sort_order'] = $data['sort_order'];
        }

        return $this->db->update(
            $this->table,
            $updateData,
            ['id' => $id]
        ) > 0;
    }

    /**
     * 切換分類的啟用狀態
     */
    public function toggleActive(int $id, bool $isActive): bool
    {
        return $this->update($id, [
            'is_active' => $isActive ? 1 : 0
        ]);
    }

    /**
     * 刪除分類
     */
    public function delete(int $id): bool
    {
        return $this->db->delete($this->table, ['id' => $id]) > 0;
    }

    /**
     * 獲取分類的子分類
     */
    public function getChildren(int $categoryId)
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->where('parent_id = :parent_id')
            ->setParameter('parent_id', $categoryId)
            ->orderBy('sort_order', 'ASC')
            ->addOrderBy('name', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * 獲取分類的所有子分類（包括子分類的子分類）
     */
    public function getAllDescendants(int $categoryId)
    {
        $allCategories = $this->all();
        $descendants = [];
        $this->collectDescendants($allCategories, $categoryId, $descendants);
        return $descendants;
    }

    /**
     * 遞歸收集所有子分類
     */
    private function collectDescendants(array $categories, int $parentId, array &$result)
    {
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $result[] = $category;
                $this->collectDescendants($categories, $category['id'], $result);
            }
        }
    }

    /**
     * 檢查分類是否是指定分類的子分類
     */
    public function isDescendantOf(int $categoryId, int $potentialParentId): bool
    {
        $category = $this->findById($categoryId);
        
        if (!$category || empty($category['parent_id'])) {
            return false;
        }

        if ((int)$category['parent_id'] === $potentialParentId) {
            return true;
        }

        return $this->isDescendantOf((int)$category['parent_id'], $potentialParentId);
    }

    /**
     * 獲取分類的層級路徑（麵包屑）
     */
    public function getBreadcrumb(int $categoryId): array
    {
        $breadcrumb = [];
        $category = $this->findById($categoryId);
        
        if (!$category) {
            return $breadcrumb;
        }
        
        $breadcrumb[] = $category;
        
        while (!empty($category['parent_id'])) {
            $category = $this->findById($category['parent_id']);
            if (!$category) break;
            array_unshift($breadcrumb, $category);
        }
        
        return $breadcrumb;
    }
}