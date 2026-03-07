<?php
namespace App\Models;

use Doctrine\DBAL\Connection;

class HomeContentsModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function paginate(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('*')
            ->from('home_contents')
            ->orderBy('created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($filters['status'])) {
            $qb->andWhere('status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $qb->andWhere('(hero_title LIKE :keyword OR mission_title LIKE :keyword OR vision_title LIKE :keyword)')
               ->setParameter('keyword', $keyword);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function count(array $filters = []): int
    {
        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(id)')
            ->from('home_contents');

        if (!empty($filters['status'])) {
            $qb->andWhere('status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $qb->andWhere('(hero_title LIKE :keyword OR mission_title LIKE :keyword OR vision_title LIKE :keyword)')
               ->setParameter('keyword', $keyword);
        }

        return (int) $qb->executeQuery()->fetchOne();
    }

    public function findById(int $id): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('*')
            ->from('home_contents')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function findPublished(?string $status = 'published'): ?array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('*')
            ->from('home_contents')
            ->orderBy('published_at', 'DESC')
            ->setMaxResults(1);

        if ($status) {
            $qb->andWhere('status = :status')
               ->setParameter('status', $status);
        }

        $record = $qb->executeQuery()->fetchAssociative();
        return $record ?: null;
    }

    public function create(array $data): int
    {
        $this->db->insert('home_contents', [
            'hero_title' => $data['hero_title'] ?? null,
            'hero_subtitle' => $data['hero_subtitle'] ?? null,
            'hero_tagline' => $data['hero_tagline'] ?? null,
            'hero_cta_text' => $data['hero_cta_text'] ?? null,
            'hero_cta_link_id' => $data['hero_cta_link_id'] ?? null,
            'mission_title' => $data['mission_title'] ?? null,
            'mission_content' => $data['mission_content'] ?? null,
            'vision_title' => $data['vision_title'] ?? null,
            'vision_content' => $data['vision_content'] ?? null,
            'narrative' => $data['narrative'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'published_at' => $data['published_at'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $update = [];

        foreach ([
            'hero_title', 'hero_subtitle', 'hero_tagline', 'hero_cta_text',
            'hero_cta_link_id', 'mission_title', 'mission_content', 'vision_title',
            'vision_content', 'narrative', 'status', 'published_at'
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }

        if (empty($update)) {
            return false;
        }

        return (bool) $this->db->update('home_contents', $update, ['id' => $id]);
    }

    public function softDelete(int $id): bool
    {
        return (bool) $this->db->update('home_contents', [
            'deleted_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
    }

    public function restore(int $id): bool
    {
        return (bool) $this->db->update('home_contents', [
            'deleted_at' => null
        ], ['id' => $id]);
    }
}
