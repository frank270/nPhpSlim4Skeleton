<?php
declare(strict_types=1);

namespace App\Models;

use Doctrine\DBAL\Connection;

class LocationStoresModel
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
            ->from('location_stores')
            ->orderBy('sort_order', 'ASC')
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
            ->from('location_stores');

        $this->applyFilters($qb, $filters);

        return (int) $qb->executeQuery()->fetchOne();
    }

    public function findById(int $id): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('*')
            ->from('location_stores')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function getCounties(): array
    {
        $result = $this->db->createQueryBuilder()
            ->select('DISTINCT county')
            ->from('location_stores')
            ->where('county IS NOT NULL')
            ->andWhere("county != ''")
            ->orderBy('county', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_column($result, 'county');
    }

    public function getStoresByCounty(?string $county = null): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('*')
            ->from('location_stores')
            ->where('status = :status')
            ->setParameter('status', 'open')
            ->orderBy('sort_order', 'ASC')
            ->addOrderBy('name', 'ASC');

        if ($county !== null) {
            $qb->andWhere('county = :county')
               ->setParameter('county', $county);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function create(array $data): int
    {
        $this->db->insert('location_stores', [
            'name' => $data['name'],
            'county' => $data['county'] ?? null,
            'district' => $data['district'] ?? null,
            'zipcode' => $data['zipcode'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'latitude' => isset($data['latitude']) ? (string)$data['latitude'] : null,
            'longitude' => isset($data['longitude']) ? (string)$data['longitude'] : null,
            'map_url' => $data['map_url'] ?? null,
            'order_url' => $data['order_url'] ?? null,
            'status' => $data['status'] ?? 'open',
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
            'notes' => $data['notes'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $update = [];

        foreach ([
            'name', 'county', 'district', 'zipcode', 'address', 'phone',
            'latitude', 'longitude', 'map_url', 'order_url',
            'status', 'sort_order', 'notes'
        ] as $field) {
            if (array_key_exists($field, $data)) {
                if ($field === 'latitude' || $field === 'longitude') {
                    $update[$field] = $data[$field] !== null ? (string)$data[$field] : null;
                } elseif ($field === 'map_url' || $field === 'order_url') {
                    $update[$field] = $data[$field] !== null ? (string)$data[$field] : null;
                } elseif ($field === 'sort_order') {
                    $update[$field] = (int)$data[$field];
                } else {
                    $update[$field] = $data[$field];
                }
            }
        }

        if (empty($update)) {
            return false;
        }

        return (bool) $this->db->update('location_stores', $update, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db->delete('location_stores', ['id' => $id]);
    }

    protected function applyFilters($qb, array $filters): void
    {
        if (!empty($filters['county'])) {
            $qb->andWhere('county = :county')
               ->setParameter('county', $filters['county']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $qb->andWhere('(name LIKE :keyword OR address LIKE :keyword OR phone LIKE :keyword)')
               ->setParameter('keyword', $keyword);
        }
    }
}

