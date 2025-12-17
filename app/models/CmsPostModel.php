<?php
namespace App\Models;

use Doctrine\DBAL\Connection;

class CmsPostModel
{
    private Connection $db;
    private string $table = 'cms_posts';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function paginate(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->orderBy('sort_order', 'DESC')
            ->addOrderBy('published_at', 'DESC')
            ->addOrderBy('created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $this->applyFilters($qb, $filters);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function count(array $filters = []): int
    {
        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(id)')
            ->from($this->table);

        $this->applyFilters($qb, $filters);

        return (int) $qb->executeQuery()->fetchOne();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchAssociative("SELECT * FROM {$this->table} WHERE id = ?", [$id]) ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetchAssociative("SELECT * FROM {$this->table} WHERE slug = ?", [$slug]) ?: null;
    }

    public function create(array $data): int
    {
        $this->db->insert($this->table, [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'cover_image' => $data['cover_image'] ?? null,
            'content' => $data['content'] ?? null,
            'tags' => $data['tags'] ?? null,
            'type' => $data['type'] ?? 'news',
            'status' => $data['status'] ?? 'draft',
            'sort_order' => $data['sort_order'] ?? 0,
            'published_at' => !empty($data['published_at']) ? $data['published_at'] : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $update = [];
        $fields = ['title', 'slug', 'cover_image', 'content', 'tags', 'type', 'status', 'sort_order'];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }

        if (array_key_exists('published_at', $data)) {
            $update['published_at'] = !empty($data['published_at']) ? $data['published_at'] : null;
        }

        $update['updated_at'] = date('Y-m-d H:i:s');

        return (bool) $this->db->update($this->table, $update, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db->delete($this->table, ['id' => $id]);
    }

    private function applyFilters($qb, array $filters): void
    {
        if (!empty($filters['status'])) {
            $qb->andWhere('status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['keyword'])) {
            $qb->andWhere('title LIKE :keyword')
               ->setParameter('keyword', '%' . $filters['keyword'] . '%');
        }
    }
}
