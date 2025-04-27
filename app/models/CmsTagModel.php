<?php
namespace App\Models;

use Doctrine\DBAL\Connection;

class CmsTagModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * 取得所有標籤
     * 
     * @param string|null $search 搜尋關鍵字
     * @return array
     */
    public function all(?string $search = null): array
    {
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('t.*')
            ->from('cms_tags', 't')
            ->orderBy('t.name', 'ASC');
            
        if ($search) {
            $queryBuilder->where('t.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    /**
     * 依據ID查找標籤
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $result = $this->db->createQueryBuilder()
            ->select('*')
            ->from('cms_tags')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();
            
        return $result ?: null;
    }

    /**
     * 依據Slug查找標籤
     * 
     * @param string $slug
     * @return array|null
     */
    public function findBySlug(string $slug): ?array
    {
        $result = $this->db->createQueryBuilder()
            ->select('*')
            ->from('cms_tags')
            ->where('slug = :slug')
            ->setParameter('slug', $slug)
            ->executeQuery()
            ->fetchAssociative();
            
        return $result ?: null;
    }

    /**
     * 依據名稱查找標籤
     * 
     * @param string $name
     * @return array|null
     */
    public function findByName(string $name): ?array
    {
        $result = $this->db->createQueryBuilder()
            ->select('*')
            ->from('cms_tags')
            ->where('name = :name')
            ->setParameter('name', $name)
            ->executeQuery()
            ->fetchAssociative();
            
        return $result ?: null;
    }

    /**
     * 建立新的標籤
     * 
     * @param array $data
     * @return int 新建標籤的ID
     */
    public function create(array $data): int
    {
        $this->db->insert('cms_tags', [
            'name' => $data['name'],
            'slug' => $data['slug'],
        ]);
        
        return (int) $this->db->lastInsertId();
    }

    /**
     * 更新標籤
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return (bool) $this->db->update('cms_tags', [
            'name' => $data['name'],
            'slug' => $data['slug'],
        ], ['id' => $id]);
    }

    /**
     * 刪除標籤
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        // 刪除標籤關聯
        $this->db->delete('cms_content_tag', ['tag_id' => $id]);
        
        // 刪除標籤
        return (bool) $this->db->delete('cms_tags', ['id' => $id]);
    }

    /**
     * 獲取標籤的內容
     * 
     * @param int $tagId
     * @param array $filters 額外篩選條件
     * @param int $limit 限制數量
     * @param int $offset 偏移量
     * @return array
     */
    public function getContents(int $tagId, array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('c.*, ct.name as content_type_name, ct.slug as content_type_slug, u.display_name as author_name')
            ->from('cms_contents', 'c')
            ->innerJoin('c', 'cms_content_tag', 'ct2', 'c.id = ct2.content_id')
            ->leftJoin('c', 'cms_content_types', 'ct', 'c.content_type_id = ct.id')
            ->leftJoin('c', 'admin_users', 'u', 'c.author_id = u.id')
            ->where('ct2.tag_id = :tag_id')
            ->setParameter('tag_id', $tagId)
            ->orderBy('c.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        // 應用額外篩選條件
        if (!empty($filters['status'])) {
            $queryBuilder->andWhere('c.status = :status')
                ->setParameter('status', $filters['status']);
        }
        
        if (!empty($filters['content_type_id'])) {
            $queryBuilder->andWhere('c.content_type_id = :content_type_id')
                ->setParameter('content_type_id', $filters['content_type_id']);
        }
        
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    /**
     * 獲取標籤的內容計數
     * 
     * @param int $tagId
     * @param array $filters 額外篩選條件
     * @return int
     */
    public function getContentsCount(int $tagId, array $filters = []): int
    {
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from('cms_contents', 'c')
            ->innerJoin('c', 'cms_content_tag', 'ct', 'c.id = ct.content_id')
            ->where('ct.tag_id = :tag_id')
            ->setParameter('tag_id', $tagId);
        
        // 應用額外篩選條件
        if (!empty($filters['status'])) {
            $queryBuilder->andWhere('c.status = :status')
                ->setParameter('status', $filters['status']);
        }
        
        if (!empty($filters['content_type_id'])) {
            $queryBuilder->andWhere('c.content_type_id = :content_type_id')
                ->setParameter('content_type_id', $filters['content_type_id']);
        }
        
        return (int) $queryBuilder->executeQuery()->fetchOne();
    }

    /**
     * 查找或創建標籤
     * 
     * @param string $name
     * @return int 標籤ID
     */
    public function findOrCreate(string $name): int
    {
        // 先嘗試查找現有標籤
        $tag = $this->findByName($name);
        
        if ($tag) {
            return (int) $tag['id'];
        }
        
        // 如果沒找到，創建新標籤
        $slug = $this->generateSlug($name);
        
        return $this->create([
            'name' => $name,
            'slug' => $slug
        ]);
    }

    /**
     * 獲取熱門標籤
     * 
     * @param int $limit 限制數量
     * @return array
     */
    public function getPopularTags(int $limit = 10): array
    {
        return $this->db->createQueryBuilder()
            ->select('t.*, COUNT(ct.content_id) as content_count')
            ->from('cms_tags', 't')
            ->leftJoin('t', 'cms_content_tag', 'ct', 't.id = ct.tag_id')
            ->groupBy('t.id')
            ->orderBy('content_count', 'DESC')
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * 批量獲取標籤
     * 
     * @param array $tagIds 標籤ID數組
     * @return array
     */
    public function getByIds(array $tagIds): array
    {
        if (empty($tagIds)) {
            return [];
        }
        
        $placeholders = implode(', ', array_fill(0, count($tagIds), '?'));
        
        $sql = "SELECT * FROM cms_tags WHERE id IN ({$placeholders}) ORDER BY name ASC";
        
        return $this->db->executeQuery($sql, $tagIds)->fetchAllAssociative();
    }

    /**
     * 檢查標籤名稱是否唯一
     * 
     * @param string $name
     * @param int|null $excludeId
     * @return bool
     */
    public function isNameUnique(string $name, ?int $excludeId = null): bool
    {
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('COUNT(id)')
            ->from('cms_tags')
            ->where('name = :name')
            ->setParameter('name', $name);
            
        if ($excludeId) {
            $queryBuilder->andWhere('id != :id')
                ->setParameter('id', $excludeId);
        }
        
        return (int) $queryBuilder->executeQuery()->fetchOne() === 0;
    }

    /**
     * 檢查標籤Slug是否唯一
     * 
     * @param string $slug
     * @param int|null $excludeId
     * @return bool
     */
    public function isSlugUnique(string $slug, ?int $excludeId = null): bool
    {
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('COUNT(id)')
            ->from('cms_tags')
            ->where('slug = :slug')
            ->setParameter('slug', $slug);
            
        if ($excludeId) {
            $queryBuilder->andWhere('id != :id')
                ->setParameter('id', $excludeId);
        }
        
        return (int) $queryBuilder->executeQuery()->fetchOne() === 0;
    }

    /**
     * 生成Slug
     * 
     * @param string $name
     * @return string
     */
    public function generateSlug(string $name): string
    {
        // 轉為小寫，並替換空格為連字符
        $slug = strtolower(trim($name));
        $slug = preg_replace('/\s+/', '-', $slug);
        
        // 移除非字母數字字符（除了連字符）
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        
        // 移除連續的連字符
        $slug = preg_replace('/-+/', '-', $slug);
        
        // 檢查slug是否唯一，如果不是則添加數字後綴
        $baseSlug = $slug;
        $counter = 1;
        
        while (!$this->isSlugUnique($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}