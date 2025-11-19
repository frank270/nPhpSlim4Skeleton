<?php
declare(strict_types=1);

namespace App\Models;

use Doctrine\DBAL\Connection;

class FaqsModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function paginate(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('f.*', 'fc.name as category_name', 'fc.slug as category_slug')
            ->from('faqs', 'f')
            ->leftJoin('f', 'faq_categories', 'fc', 'f.category_id = fc.id')
            ->orderBy('f.sort_order', 'ASC')
            ->addOrderBy('f.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $this->applyFilters($qb, $filters);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function count(array $filters = []): int
    {
        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(f.id)')
            ->from('faqs', 'f')
            ->leftJoin('f', 'faq_categories', 'fc', 'f.category_id = fc.id');

        $this->applyFilters($qb, $filters);

        return (int) $qb->executeQuery()->fetchOne();
    }

    public function findById(int $id): ?array
    {
        $record = $this->db->createQueryBuilder()
            ->select('f.*', 'fc.name as category_name', 'fc.slug as category_slug')
            ->from('faqs', 'f')
            ->leftJoin('f', 'faq_categories', 'fc', 'f.category_id = fc.id')
            ->where('f.id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();

        return $record ?: null;
    }

    public function getPublished(array $filters = []): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('f.*', 'fc.name as category_name', 'fc.slug as category_slug')
            ->from('faqs', 'f')
            ->leftJoin('f', 'faq_categories', 'fc', 'f.category_id = fc.id')
            ->where('f.status = :status')
            ->setParameter('status', 'published')
            ->andWhere('(fc.is_active = :is_active OR fc.is_active IS NULL)')
            ->setParameter('is_active', true)
            ->orderBy('f.sort_order', 'ASC')
            ->addOrderBy('f.created_at', 'DESC');

        if (!empty($filters['category_id'])) {
            $qb->andWhere('f.category_id = :category_id')
               ->setParameter('category_id', $filters['category_id']);
        }

        if (!empty($filters['is_highlight'])) {
            $qb->andWhere('f.is_highlight = :is_highlight')
               ->setParameter('is_highlight', true);
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getHighlights(int $limit = 5): array
    {
        return $this->db->createQueryBuilder()
            ->select('f.*', 'fc.name as category_name', 'fc.slug as category_slug')
            ->from('faqs', 'f')
            ->leftJoin('f', 'faq_categories', 'fc', 'f.category_id = fc.id')
            ->where('f.status = :status')
            ->setParameter('status', 'published')
            ->andWhere('f.is_highlight = :is_highlight')
            ->setParameter('is_highlight', true)
            ->andWhere('(fc.is_active = :is_active OR fc.is_active IS NULL)')
            ->setParameter('is_active', true)
            ->orderBy('f.sort_order', 'ASC')
            ->setMaxResults($limit)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function create(array $data): int
    {
        $this->db->insert('faqs', [
            'category_id' => isset($data['category_id']) && $data['category_id'] ? (int)$data['category_id'] : null,
            'question' => $data['question'],
            'answer' => $data['answer'],
            'is_highlight' => isset($data['is_highlight']) ? (int)((bool)$data['is_highlight']) : 0,
            'status' => $data['status'] ?? 'draft',
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $update = [];

        foreach (['category_id', 'question', 'answer', 'is_highlight', 'status', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                if ($field === 'category_id') {
                    $update[$field] = $data[$field] ? (int)$data[$field] : null;
                } elseif ($field === 'is_highlight') {
                    $update[$field] = (int)((bool)$data[$field]);
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

        return (bool) $this->db->update('faqs', $update, ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db->delete('faqs', ['id' => $id]);
    }

    public function toggleStatus(int $id): ?string
    {
        $faq = $this->findById($id);
        if (!$faq) {
            return null;
        }

        $newStatus = $faq['status'] === 'published' ? 'draft' : 'published';
        $this->update($id, ['status' => $newStatus]);

        return $newStatus;
    }

    protected function applyFilters($qb, array $filters): void
    {
        if (!empty($filters['category_id'])) {
            $qb->andWhere('f.category_id = :category_id')
               ->setParameter('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('f.status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['is_highlight'])) {
            $qb->andWhere('f.is_highlight = :is_highlight')
               ->setParameter('is_highlight', (bool)$filters['is_highlight']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = '%' . $filters['keyword'] . '%';
            $qb->andWhere('(f.question LIKE :keyword OR f.answer LIKE :keyword)')
               ->setParameter('keyword', $keyword);
        }
    }
}

