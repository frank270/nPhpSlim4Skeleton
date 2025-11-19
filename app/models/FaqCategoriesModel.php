<?php
declare(strict_types=1);

namespace App\Models;

use Doctrine\DBAL\Connection;

class FaqCategoriesModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from('faq_categories')
            ->orderBy('sort_order', 'ASC')
            ->addOrderBy('name', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function getActive(): array
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from('faq_categories')
            ->where('is_active = :is_active')
            ->setParameter('is_active', true)
            ->orderBy('sort_order', 'ASC')
            ->addOrderBy('name', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function findById(int $id): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('*')
            ->from('faq_categories')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('*')
            ->from('faq_categories')
            ->where('slug = :slug')
            ->setParameter('slug', $slug)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $this->db->insert('faq_categories', [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
            'is_active' => isset($data['is_active']) ? (int)((bool)$data['is_active']) : 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $update = [];

        foreach (['name', 'slug', 'sort_order', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                if ($field === 'sort_order') {
                    $update[$field] = (int)$data[$field];
                } elseif ($field === 'is_active') {
                    $update[$field] = (int)((bool)$data[$field]);
                } else {
                    $update[$field] = $data[$field];
                }
            }
        }

        if (empty($update)) {
            return false;
        }

        return (bool) $this->db->update('faq_categories', $update, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db->delete('faq_categories', ['id' => $id]);
    }
}

