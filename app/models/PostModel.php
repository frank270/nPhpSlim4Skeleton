<?php
namespace App\Models;

use Doctrine\DBAL\Connection;

class PostModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from('posts')
            ->orderBy('created_at', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function findById(int $id): ?array
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from('posts')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative();
    }

    public function create(array $data): void
    {
        $this->db->insert('posts', [
            'title' => $data['title'],
            'content' => $data['content'],
        ]);
    }
}
