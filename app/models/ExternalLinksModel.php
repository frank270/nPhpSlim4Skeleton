<?php
namespace App\Models;

use Doctrine\DBAL\Connection;

class ExternalLinksModel
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
            ->from('external_links')
            ->where('deleted_at IS NULL')
            ->orderBy('created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($filters['type'])) {
            $qb->andWhere('type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $qb->andWhere('is_active = :is_active')
               ->setParameter('is_active', $filters['is_active']);
        }

        if (!empty($filters['locale'])) {
            $qb->andWhere('(locale = :locale OR locale IS NULL)')
               ->setParameter('locale', $filters['locale']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $qb->andWhere('(name LIKE :keyword OR url LIKE :keyword)')
               ->setParameter('keyword', $keyword);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function count(array $filters = []): int
    {
        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(id)')
            ->from('external_links')
            ->where('deleted_at IS NULL');

        if (!empty($filters['type'])) {
            $qb->andWhere('type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $qb->andWhere('is_active = :is_active')
               ->setParameter('is_active', $filters['is_active']);
        }

        if (!empty($filters['locale'])) {
            $qb->andWhere('(locale = :locale OR locale IS NULL)')
               ->setParameter('locale', $filters['locale']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $qb->andWhere('(name LIKE :keyword OR url LIKE :keyword)')
               ->setParameter('keyword', $keyword);
        }

        return (int) $qb->executeQuery()->fetchOne();
    }

    public function findById(int $id): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('*')
            ->from('external_links')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function findByUuid(string $uuid): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('*')
            ->from('external_links')
            ->where('uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $this->db->insert('external_links', [
            'uuid' => $data['uuid'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'url' => $data['url'],
            'type' => $data['type'] ?? 'default',
            'locale' => $data['locale'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'opened_in_new_tab' => $data['opened_in_new_tab'] ?? 1,
            'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $update = [];

        foreach ([
            'name', 'description', 'url', 'type', 'locale',
            'is_active', 'opened_in_new_tab'
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }

        if (array_key_exists('metadata', $data)) {
            $update['metadata'] = $data['metadata'] !== null ? json_encode($data['metadata']) : null;
        }

        if (empty($update)) {
            return false;
        }

        return (bool) $this->db->update('external_links', $update, ['id' => $id]);
    }

    public function softDelete(int $id): bool
    {
        return (bool) $this->db->update('external_links', [
            'deleted_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
    }

    public function restore(int $id): bool
    {
        return (bool) $this->db->update('external_links', [
            'deleted_at' => null
        ], ['id' => $id]);
    }
}
