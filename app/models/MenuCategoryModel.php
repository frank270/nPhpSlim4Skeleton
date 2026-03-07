<?php
declare(strict_types=1);

namespace App\Models;

use Doctrine\DBAL\Connection;

class MenuCategoryModel
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
            ->from('menu_categories')
            ->orderBy('sort_order', 'ASC')
            ->addOrderBy('created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        // Apply filters if needed
        if (!empty($filters['keyword'])) {
            $qb->andWhere('name LIKE :keyword')
               ->setParameter('keyword', '%' . $filters['keyword'] . '%');
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getAll(): array
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from('menu_categories')
            ->where('is_active = 1')
            ->orderBy('sort_order', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function count(array $filters = []): int
    {
        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(id)')
            ->from('menu_categories');

        if (!empty($filters['keyword'])) {
             $qb->andWhere('name LIKE :keyword')
               ->setParameter('keyword', '%' . $filters['keyword'] . '%');
        }

        return (int) $qb->executeQuery()->fetchOne();
    }

    public function findById(int $id): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('*')
            ->from('menu_categories')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $this->db->insert('menu_categories', [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $update = [];
        $fields = ['name', 'slug', 'description', 'sort_order', 'is_active'];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                 if ($field === 'sort_order' || $field === 'is_active') {
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

        return (bool) $this->db->update('menu_categories', $update, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db->delete('menu_categories', ['id' => $id]);
    }
}
