<?php
declare(strict_types=1);

namespace App\Models;

use Doctrine\DBAL\Connection;

class FoodSafetyCategoryModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function paginate(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('*')
            ->from('food_safety_categories', 'c')
            ->orderBy('c.sort_order', 'ASC')
            ->addOrderBy('c.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $qb->andWhere('(c.name LIKE :keyword OR c.slug LIKE :keyword)')
               ->setParameter('keyword', $keyword);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function count(array $filters = []): int
    {
        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from('food_safety_categories', 'c');

        if (!empty($filters['keyword'])) {
             $keyword = '%' . $filters['keyword'] . '%';
             $qb->andWhere('(c.name LIKE :keyword OR c.slug LIKE :keyword)')
                ->setParameter('keyword', $keyword);
        }

        return (int) $qb->executeQuery()->fetchOne();
    }

    public function findById(int $id): ?array
    {
        $record = $this->db->fetchAssociative('SELECT * FROM food_safety_categories WHERE id = ?', [$id]);
        return $record ?: null;
    }

    public function create(array $data): int
    {
        $this->db->insert('food_safety_categories', [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'is_active' => (int)($data['is_active'] ?? 1),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $update = [];
        if (isset($data['name'])) $update['name'] = $data['name'];
        if (isset($data['slug'])) $update['slug'] = $data['slug'];
        if (isset($data['description'])) $update['description'] = $data['description'];
        if (isset($data['sort_order'])) $update['sort_order'] = (int)$data['sort_order'];
        if (isset($data['is_active'])) $update['is_active'] = (int)$data['is_active'];
        
        $update['updated_at'] = date('Y-m-d H:i:s');

        return (bool) $this->db->update('food_safety_categories', $update, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db->delete('food_safety_categories', ['id' => $id]);
    }

    public function getAll(): array
    {
        return $this->db->fetchAllAssociative('SELECT * FROM food_safety_categories WHERE is_active = 1 ORDER BY sort_order ASC');
    }
}
