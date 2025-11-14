<?php
namespace App\Models;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

class MediaAssetsModel
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
            ->from('media_assets')
            ->orderBy('created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($filters['status'])) {
            $qb->andWhere('status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['disk'])) {
            $qb->andWhere('disk = :disk')
               ->setParameter('disk', $filters['disk']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $qb->andWhere('(original_name LIKE :keyword OR path LIKE :keyword)')
               ->setParameter('keyword', $keyword);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function count(array $filters = []): int
    {
        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(id)')
            ->from('media_assets');

        if (!empty($filters['status'])) {
            $qb->andWhere('status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['disk'])) {
            $qb->andWhere('disk = :disk')
               ->setParameter('disk', $filters['disk']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $qb->andWhere('(original_name LIKE :keyword OR path LIKE :keyword)')
               ->setParameter('keyword', $keyword);
        }

        return (int) $qb->executeQuery()->fetchOne();
    }

    public function findById(int $id): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('*')
            ->from('media_assets')
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
            ->from('media_assets')
            ->where('uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function create(array $data): int
    {
        $this->db->insert('media_assets', [
            'uuid' => $data['uuid'],
            'disk' => $data['disk'],
            'path' => $data['path'],
            'original_name' => $data['original_name'] ?? null,
            'mime_type' => $data['mime_type'] ?? null,
            'size_bytes' => $data['size_bytes'],
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
            'caption' => $data['caption'] ?? null,
            'meta' => isset($data['meta']) ? json_encode($data['meta']) : null,
            'status' => $data['status'] ?? 'draft',
        ], [
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::INTEGER,
            ParameterType::INTEGER,
            ParameterType::INTEGER,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
            ParameterType::STRING,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $update = [];

        foreach ([
            'disk', 'path', 'original_name', 'mime_type', 'size_bytes',
            'width', 'height', 'alt_text', 'caption', 'status'
        ] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }

        if (array_key_exists('meta', $data)) {
            $update['meta'] = $data['meta'] !== null ? json_encode($data['meta']) : null;
        }

        if (empty($update)) {
            return false;
        }

        return (bool) $this->db->update('media_assets', $update, ['id' => $id]);
    }

    public function softDelete(int $id): bool
    {
        return (bool) $this->db->update('media_assets', [
            'deleted_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
    }

    public function restore(int $id): bool
    {
        return (bool) $this->db->update('media_assets', [
            'deleted_at' => null
        ], ['id' => $id]);
    }
}
