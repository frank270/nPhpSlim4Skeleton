<?php
namespace App\Models;

use Doctrine\DBAL\Connection;

class CmsContentModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * 取得所有內容
     * 
     * @param array $filters 篩選條件
     * @param int $limit 限制數量
     * @param int $offset 偏移量
     * @return array
     */
    public function all(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('c.*, ct.name as content_type_name, ct.slug as content_type_slug, u.display_name as author_name')
            ->from('cms_contents', 'c')
            ->leftJoin('c', 'cms_content_types', 'ct', 'c.content_type_id = ct.id')
            ->leftJoin('c', 'admin_users', 'u', 'c.author_id = u.id')
            ->orderBy('c.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        // 應用篩選條件
        if (!empty($filters['content_type_id'])) {
            $queryBuilder->andWhere('c.content_type_id = :content_type_id')
                ->setParameter('content_type_id', $filters['content_type_id']);
        }
        
        if (!empty($filters['status'])) {
            $queryBuilder->andWhere('c.status = :status')
                ->setParameter('status', $filters['status']);
        }
        
        if (!empty($filters['author_id'])) {
            $queryBuilder->andWhere('c.author_id = :author_id')
                ->setParameter('author_id', $filters['author_id']);
        }
        
        if (isset($filters['is_featured'])) {
            $queryBuilder->andWhere('c.is_featured = :is_featured')
                ->setParameter('is_featured', $filters['is_featured']);
        }
        
        if (!empty($filters['search'])) {
            $queryBuilder->andWhere('c.title LIKE :search OR c.content LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }
        
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    /**
     * 計算內容總數
     * 
     * @param array $filters 篩選條件
     * @return int
     */
    public function count(array $filters = []): int
    {
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from('cms_contents', 'c');
        
        // 應用篩選條件
        if (!empty($filters['content_type_id'])) {
            $queryBuilder->andWhere('c.content_type_id = :content_type_id')
                ->setParameter('content_type_id', $filters['content_type_id']);
        }
        
        if (!empty($filters['status'])) {
            $queryBuilder->andWhere('c.status = :status')
                ->setParameter('status', $filters['status']);
        }
        
        if (!empty($filters['author_id'])) {
            $queryBuilder->andWhere('c.author_id = :author_id')
                ->setParameter('author_id', $filters['author_id']);
        }
        
        if (isset($filters['is_featured'])) {
            $queryBuilder->andWhere('c.is_featured = :is_featured')
                ->setParameter('is_featured', $filters['is_featured']);
        }
        
        if (!empty($filters['search'])) {
            $queryBuilder->andWhere('c.title LIKE :search OR c.content LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }
        
        return (int) $queryBuilder->executeQuery()->fetchOne();
    }

    /**
     * 依據ID查找內容
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $result = $this->db->createQueryBuilder()
            ->select('c.*, ct.name as content_type_name, ct.slug as content_type_slug, u.display_name as author_name')
            ->from('cms_contents', 'c')
            ->leftJoin('c', 'cms_content_types', 'ct', 'c.content_type_id = ct.id')
            ->leftJoin('c', 'admin_users', 'u', 'c.author_id = u.id')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();
            
        return $result ?: null;
    }

    /**
     * 依據Slug和內容類型查找內容
     * 
     * @param string $slug
     * @param int|null $contentTypeId
     * @return array|null
     */
    public function findBySlug(string $slug, ?int $contentTypeId = null): ?array
    {
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('c.*, ct.name as content_type_name, ct.slug as content_type_slug, u.display_name as author_name')
            ->from('cms_contents', 'c')
            ->leftJoin('c', 'cms_content_types', 'ct', 'c.content_type_id = ct.id')
            ->leftJoin('c', 'admin_users', 'u', 'c.author_id = u.id')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug);
            
        if ($contentTypeId) {
            $queryBuilder->andWhere('c.content_type_id = :content_type_id')
                ->setParameter('content_type_id', $contentTypeId);
        }
        
        $result = $queryBuilder->executeQuery()->fetchAssociative();
        return $result ?: null;
    }

    /**
     * 建立新的內容
     * 
     * @param array $data
     * @return int 新建內容的ID
     */
    public function create(array $data): int
    {
        $this->db->insert('cms_contents', [
            'content_type_id' => $data['content_type_id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'content' => $data['content'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
            'thumbnail' => $data['thumbnail'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'is_featured' => $data['is_featured'] ?? 0,
            'sort_order' => $data['sort_order'] ?? 0,
            'author_id' => $data['author_id'] ?? null,
            'published_at' => $data['published_at'] ?? null,
        ]);
        
        $contentId = (int) $this->db->lastInsertId();
        
        // 處理分類關聯
        if (!empty($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $categoryId) {
                $this->db->insert('cms_content_category', [
                    'content_id' => $contentId,
                    'category_id' => $categoryId
                ]);
            }
        }
        
        // 處理標籤關聯
        if (!empty($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tagId) {
                $this->db->insert('cms_content_tag', [
                    'content_id' => $contentId,
                    'tag_id' => $tagId
                ]);
            }
        }
        
        return $contentId;
    }

    /**
     * 更新內容
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $updateData = [];
        
        if (isset($data['content_type_id'])) {
            $updateData['content_type_id'] = $data['content_type_id'];
        }
        
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }
        
        if (isset($data['slug'])) {
            $updateData['slug'] = $data['slug'];
        }
        
        if (isset($data['content'])) {
            $updateData['content'] = $data['content'];
        }
        
        if (isset($data['excerpt'])) {
            $updateData['excerpt'] = $data['excerpt'];
        }
        
        if (isset($data['thumbnail'])) {
            $updateData['thumbnail'] = $data['thumbnail'];
        }
        
        if (isset($data['meta_title'])) {
            $updateData['meta_title'] = $data['meta_title'];
        }
        
        if (isset($data['meta_description'])) {
            $updateData['meta_description'] = $data['meta_description'];
        }
        
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
            
            // 如果狀態變更為已發布，且未設定發布時間，則設定為當前時間
            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $updateData['published_at'] = date('Y-m-d H:i:s');
            }
        }
        
        if (isset($data['is_featured'])) {
            $updateData['is_featured'] = $data['is_featured'];
        }
        
        if (isset($data['sort_order'])) {
            $updateData['sort_order'] = $data['sort_order'];
        }
        
        if (isset($data['author_id'])) {
            $updateData['author_id'] = $data['author_id'];
        }
        
        if (isset($data['published_at'])) {
            $updateData['published_at'] = $data['published_at'];
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        $result = $this->db->update('cms_contents', $updateData, ['id' => $id]);
        
        // 處理分類關聯
        if (isset($data['categories']) && is_array($data['categories'])) {
            // 刪除現有關聯
            $this->db->delete('cms_content_category', ['content_id' => $id]);
            
            // 添加新關聯
            foreach ($data['categories'] as $categoryId) {
                $this->db->insert('cms_content_category', [
                    'content_id' => $id,
                    'category_id' => $categoryId
                ]);
            }
        }
        
        // 處理標籤關聯
        if (isset($data['tags']) && is_array($data['tags'])) {
            // 刪除現有關聯
            $this->db->delete('cms_content_tag', ['content_id' => $id]);
            
            // 添加新關聯
            foreach ($data['tags'] as $tagId) {
                $this->db->insert('cms_content_tag', [
                    'content_id' => $id,
                    'tag_id' => $tagId
                ]);
            }
        }
        
        return (bool) $result;
    }

    /**
     * 刪除內容
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        // 刪除關聯數據
        $this->db->delete('cms_content_category', ['content_id' => $id]);
        $this->db->delete('cms_content_tag', ['content_id' => $id]);
        
        // 刪除內容
        return (bool) $this->db->delete('cms_contents', ['id' => $id]);
    }

    /**
     * 獲取內容的分類
     * 
     * @param int $contentId
     * @return array
     */
    public function getCategories(int $contentId): array
    {
        return $this->db->createQueryBuilder()
            ->select('c.*')
            ->from('cms_categories', 'c')
            ->innerJoin('c', 'cms_content_category', 'cc', 'c.id = cc.category_id')
            ->where('cc.content_id = :content_id')
            ->setParameter('content_id', $contentId)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * 獲取內容的標籤
     * 
     * @param int $contentId
     * @return array
     */
    public function getTags(int $contentId): array
    {
        return $this->db->createQueryBuilder()
            ->select('t.*')
            ->from('cms_tags', 't')
            ->innerJoin('t', 'cms_content_tag', 'ct', 't.id = ct.tag_id')
            ->where('ct.content_id = :content_id')
            ->setParameter('content_id', $contentId)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * 切換內容的精選狀態
     * 
     * @param int $id
     * @param bool $isFeatured
     * @return bool
     */
    public function toggleFeatured(int $id, bool $isFeatured): bool
    {
        return (bool) $this->db->update('cms_contents', [
            'is_featured' => $isFeatured ? 1 : 0
        ], ['id' => $id]);
    }

    /**
     * 更新內容狀態
     * 
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool
    {
        $updateData = ['status' => $status];
        
        // 如果狀態變更為已發布，且未設定發布時間，則設定為當前時間
        if ($status === 'published') {
            $content = $this->findById($id);
            if (!$content || !$content['published_at']) {
                $updateData['published_at'] = date('Y-m-d H:i:s');
            }
        }
        
        return (bool) $this->db->update('cms_contents', $updateData, ['id' => $id]);
    }
}