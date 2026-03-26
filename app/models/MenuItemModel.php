<?php
declare(strict_types=1);

namespace App\Models;

use Doctrine\DBAL\Connection;

class MenuItemModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function paginate(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('m.*, c.name as category_name')
            ->from('menu_items', 'm')
            ->leftJoin('m', 'menu_categories', 'c', 'm.category_id = c.id')
            ->orderBy('m.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $this->applyFilters($qb, $filters);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function count(array $filters = []): int
    {
        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(m.id)')
            ->from('menu_items', 'm');

        // Only join if filtering by category name (usually filtered by id which is on items table)
        // But let's keep it simple.
        
        $this->applyFilters($qb, $filters);

        return (int) $qb->executeQuery()->fetchOne();
    }

    public function findById(int $id): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('m.*, c.name as category_name')
            ->from('menu_items', 'm')
            ->leftJoin('m', 'menu_categories', 'c', 'm.category_id = c.id')
            ->where('m.id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function findByItemCode(string $code): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('m.*, c.name as category_name')
            ->from('menu_items', 'm')
            ->leftJoin('m', 'menu_categories', 'c', 'm.category_id = c.id')
            ->where('m.item_code = :code')
            ->setParameter('code', $code)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $this->db->insert('menu_items', [
            'category_id' => (int)$data['category_id'],
            'item_code' => $data['item_code'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price_original' => $data['price_original'],
            'price_sale' => $data['price_sale'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'tags' => $data['tags'] ?? null,
            'is_best_seller' => isset($data['is_best_seller']) ? (int)$data['is_best_seller'] : 0,
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
            'item_code', 'category_id', 'name', 'description', 'price_original', 'price_sale',
            'image_path', 'tags', 'is_best_seller', 'status', 'published_at'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                if ($field === 'category_id') {
                     $update[$field] = $data[$field] !== null ? (int)$data[$field] : null;
                } elseif ($field === 'is_best_seller') {
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

        return (bool) $this->db->update('menu_items', $update, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db->delete('menu_items', ['id' => $id]);
    }

    protected function applyFilters($qb, array $filters): void
    {
        if (!empty($filters['category_id'])) {
            $qb->andWhere('m.category_id = :category_id')
               ->setParameter('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('m.status = :status')
               ->setParameter('status', $filters['status']);
        }
        
        if (isset($filters['is_best_seller'])) {
             $qb->andWhere('m.is_best_seller = :is_best_seller')
                ->setParameter('is_best_seller', $filters['is_best_seller']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $qb->andWhere('(m.name LIKE :keyword OR m.description LIKE :keyword)')
               ->setParameter('keyword', $keyword);
        }

        if (!empty($filters['tags'])) {
            // Simple LIKE search for tags
            $tags = '%' . $filters['tags'] . '%';
             $qb->andWhere('m.tags LIKE :tags')
                ->setParameter('tags', $tags);
        }
    }
}
