<?php
namespace App\Models;

use Doctrine\DBAL\Connection;

class CmsContentTypeModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * 取得所有內容類型
     * 
     * @param bool $activeOnly 是否只返回啟用的類型
     * @return array
     */
    public function all(bool $activeOnly = false): array
    {
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('*')
            ->from('cms_content_types')
            ->orderBy('name', 'ASC');
            
        if ($activeOnly) {
            $queryBuilder->where('is_active = 1');
        }
        
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    /**
     * 依據ID查找內容類型
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $result = $this->db->createQueryBuilder()
            ->select('*')
            ->from('cms_content_types')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();
            
        return $result ?: null;
    }

    /**
     * 依據Slug查找內容類型
     * 
     * @param string $slug
     * @return array|null
     */
    public function findBySlug(string $slug): ?array
    {
        $result = $this->db->createQueryBuilder()
            ->select('*')
            ->from('cms_content_types')
            ->where('slug = :slug')
            ->setParameter('slug', $slug)
            ->executeQuery()
            ->fetchAssociative();
            
        return $result ?: null;
    }

    /**
     * 建立新的內容類型
     * 
     * @param array $data
     * @return int 新建內容類型的ID
     */
    public function create(array $data): int
    {
        $this->db->insert('cms_content_types', [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
        ]);
        
        return (int) $this->db->lastInsertId();
    }

    /**
     * 更新內容類型
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return (bool) $this->db->update('cms_content_types', [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
        ], ['id' => $id]);
    }

    /**
     * 刪除內容類型
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return (bool) $this->db->delete('cms_content_types', ['id' => $id]);
    }

    /**
     * 切換內容類型啟用狀態
     * 
     * @param int $id
     * @param bool $isActive
     * @return bool
     */
    public function toggleActive(int $id, bool $isActive): bool
    {
        return (bool) $this->db->update('cms_content_types', [
            'is_active' => $isActive ? 1 : 0
        ], ['id' => $id]);
    }
}