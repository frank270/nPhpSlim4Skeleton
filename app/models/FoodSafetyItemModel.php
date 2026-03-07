<?php
declare(strict_types=1);

namespace App\Models;

use Doctrine\DBAL\Connection;

class FoodSafetyItemModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function paginate(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('i.*, c.name as category_name')
            ->from('food_safety_items', 'i')
            ->leftJoin('i', 'food_safety_categories', 'c', 'i.category_id = c.id')
            ->orderBy('i.is_highlight', 'DESC') // Highlighted first
            ->addOrderBy('i.report_date', 'DESC') // Newest report first
            ->addOrderBy('i.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $this->applyFilters($qb, $filters);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function count(array $filters = []): int
    {
        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from('food_safety_items', 'i');
        
        $this->applyFilters($qb, $filters);

        return (int) $qb->executeQuery()->fetchOne();
    }

    public function findById(int $id): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('i.*, c.name as category_name')
            ->from('food_safety_items', 'i')
            ->leftJoin('i', 'food_safety_categories', 'c', 'i.category_id = c.id')
            ->where('i.id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $this->db->insert('food_safety_items', [
            'category_id' => (int)$data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'report_date' => $data['report_date'] ?? null,
            'expired_at' => $data['expired_at'] ?? null,
            'tags' => $data['tags'] ?? null,
            'is_highlight' => isset($data['is_highlight']) ? (int)$data['is_highlight'] : 0,
            'status' => $data['status'] ?? 'draft',
            'published_at' => $data['published_at'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $update = [];
        $fields = [
            'category_id', 'name', 'description', 
            'image_path', 'file_path', 
            'report_date', 'expired_at',
            'tags', 'is_highlight', 'status', 'published_at'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                if ($field === 'category_id') {
                     $update[$field] = $data[$field] !== null ? (int)$data[$field] : null;
                } elseif ($field === 'is_highlight') {
                     $update[$field] = (int)$data[$field];
                } else {
                     $update[$field] = $data[$field];
                }
            }
        }
        
        $update['updated_at'] = date('Y-m-d H:i:s');

        if (empty($update)) {
            return false;
        }

        return (bool) $this->db->update('food_safety_items', $update, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db->delete('food_safety_items', ['id' => $id]);
    }

    protected function applyFilters($qb, array $filters): void
    {
        if (!empty($filters['category_id'])) {
            $qb->andWhere('i.category_id = :category_id')
               ->setParameter('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('i.status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $qb->andWhere('(i.name LIKE :keyword OR i.description LIKE :keyword)')
               ->setParameter('keyword', $keyword);
        }
    }
}
