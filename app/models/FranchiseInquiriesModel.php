<?php
namespace App\Models;

use Doctrine\DBAL\Connection;
use DateTime;

class FranchiseInquiriesModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * 儲存新的加盟諮詢
     */
    public function create(array $data): int
    {
        $this->db->insert('franchise_inquiries', [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'ip_address' => $data['ip_address'] ?? null,
            'status' => 0, // Pending
            'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s')
        ]);
        
        return (int)$this->db->lastInsertId();
    }
    /**
     * 分頁取得加盟諮詢列表
     */
    public function paginate(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('*')
            ->from('franchise_inquiries', 'fi');

        if (!empty($filters['keyword'])) {
            $qb->andWhere('fi.name LIKE :keyword OR fi.email LIKE :keyword OR fi.phone LIKE :keyword')
               ->setParameter('keyword', '%' . $filters['keyword'] . '%');
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $qb->andWhere('fi.status = :status')
               ->setParameter('status', $filters['status']);
        }

        return $qb->orderBy('fi.created_at', 'DESC')
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
            ->from('franchise_inquiries', 'fi');

        if (!empty($filters['keyword'])) {
            $qb->andWhere('fi.name LIKE :keyword OR fi.email LIKE :keyword OR fi.phone LIKE :keyword')
               ->setParameter('keyword', '%' . $filters['keyword'] . '%');
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $qb->andWhere('fi.status = :status')
               ->setParameter('status', $filters['status']);
        }

        return (int)$qb->executeQuery()->fetchOne();
    }

    /**
     * 更新狀態
     */
    public function updateStatus(int $id, int $status): bool
    {
        return $this->db->update('franchise_inquiries', [
            'status' => $status,
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s')
        ], ['id' => $id]) > 0;
    }
}
