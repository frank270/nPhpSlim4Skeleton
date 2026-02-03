<?php
namespace App\Models;

use Doctrine\DBAL\Connection;
use DateTime;

class ContentBlockModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * 建立新的內容區塊
     */
    public function create(array $data): int
    {
        $this->db->insert('page_content_blocks', [
            'short_code' => $data['short_code'],
            'type' => $data['type'],
            'content' => $data['content'] ?? '',
            'page_link' => $data['page_link'] ?? null,
            'group_name' => $data['group_name'] ?? null,
            'status' => $data['status'] ?? 1,
            'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s')
        ]);
        
        return (int)$this->db->lastInsertId();
    }

    /**
     * 更新內容區塊
     */
    public function update(int $id, array $data): bool
    {
        $fields = [
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s')
        ];

        // 僅更新有提供的欄位
        if (isset($data['short_code'])) $fields['short_code'] = $data['short_code'];
        if (isset($data['type'])) $fields['type'] = $data['type'];
        if (isset($data['content'])) $fields['content'] = $data['content'];
        if (isset($data['page_link'])) $fields['page_link'] = $data['page_link'];
        if (isset($data['group_name'])) $fields['group_name'] = $data['group_name'];
        if (isset($data['status'])) $fields['status'] = $data['status'];

        return $this->db->update('page_content_blocks', $fields, ['id' => $id]) > 0;
    }

    /**
     * 刪除內容區塊
     */
    public function delete(int $id): bool
    {
        return $this->db->delete('page_content_blocks', ['id' => $id]) > 0;
    }

    /**
     * 根據 ID 取得
     */
    public function find(int $id): ?array
    {
        $result = $this->db->fetchAssociative('SELECT * FROM page_content_blocks WHERE id = ?', [$id]);
        return $result ?: null;
    }

    /**
     * 根據短碼取得內容 (供前台使用)
     */
    public function findByShortCode(string $code): ?array
    {
        $result = $this->db->fetchAssociative(
            'SELECT * FROM page_content_blocks WHERE short_code = ? AND status = 1', 
            [$code]
        );
        return $result ?: null;
    }

    /**
     * 檢查短碼是否存在 (排除自己)
     */
    public function shortCodeExists(string $code, ?int $excludeId = null): bool
    {
        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('page_content_blocks')
            ->where('short_code = :code')
            ->setParameter('code', $code);

        if ($excludeId) {
            $qb->andWhere('id != :id')->setParameter('id', $excludeId);
        }

        return (int)$qb->executeQuery()->fetchOne() > 0;
    }

    /**
     * 分頁取得列表
     */
    public function paginate(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('*')
            ->from('page_content_blocks', 'cb');

        if (!empty($filters['keyword'])) {
            $qb->andWhere('cb.short_code LIKE :keyword OR cb.group_name LIKE :keyword OR cb.page_link LIKE :keyword')
               ->setParameter('keyword', '%' . $filters['keyword'] . '%');
        }

        if (!empty($filters['group'])) {
            $qb->andWhere('cb.group_name = :group')
               ->setParameter('group', $filters['group']);
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $qb->andWhere('cb.type = :type')
               ->setParameter('type', $filters['type']);
        }
        
        if (isset($filters['status']) && $filters['status'] !== '') {
            $qb->andWhere('cb.status = :status')
               ->setParameter('status', $filters['status']);
        }

        // 先依群組排序，再依短碼排序
        return $qb->orderBy('cb.group_name', 'ASC')
            ->addOrderBy('cb.short_code', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * 取得總筆數
     */
    public function count(array $filters = []): int
    {
        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('page_content_blocks', 'cb');

        if (!empty($filters['keyword'])) {
            $qb->andWhere('cb.short_code LIKE :keyword OR cb.group_name LIKE :keyword OR cb.page_link LIKE :keyword')
               ->setParameter('keyword', '%' . $filters['keyword'] . '%');
        }

        if (!empty($filters['group'])) {
            $qb->andWhere('cb.group_name = :group')
               ->setParameter('group', $filters['group']);
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $qb->andWhere('cb.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $qb->andWhere('cb.status = :status')
               ->setParameter('status', $filters['status']);
        }

        return (int)$qb->executeQuery()->fetchOne();
    }

    /**
     * 取得所有群組名稱 (供篩選用)
     */
    public function getGroups(): array
    {
        $rows = $this->db->fetchAllAssociative('SELECT DISTINCT group_name FROM page_content_blocks WHERE group_name IS NOT NULL AND group_name != "" ORDER BY group_name ASC');
        return array_column($rows, 'group_name');
    }
}
