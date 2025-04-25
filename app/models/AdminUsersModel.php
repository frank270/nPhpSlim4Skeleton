<?php
namespace App\Models;

use Doctrine\DBAL\Connection;
use DateTime;

class AdminUsersModel
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * 取得所有未刪除的使用者
     */
    public function all(): array
    {
        return $this->db->createQueryBuilder()
            ->select('au.*, pg.name as group_name')
            ->from('admin_users', 'au')
            ->leftJoin('au', 'permissions_groups', 'pg', 'au.group_id = pg.id')
            ->where('au.deleted_at IS NULL')
            ->orderBy('au.id', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * 根據條件搜尋使用者
     */
    public function search(?int $groupId = null, ?string $keyword = null): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('au.*, pg.name as group_name')
            ->from('admin_users', 'au')
            ->leftJoin('au', 'permissions_groups', 'pg', 'au.group_id = pg.id')
            ->where('au.deleted_at IS NULL');
        
        if ($groupId) {
            $qb->andWhere('au.group_id = :group_id')
               ->setParameter('group_id', $groupId);
        }
        
        if ($keyword) {
            $qb->andWhere('(au.username LIKE :keyword OR au.display_name LIKE :keyword)')
               ->setParameter('keyword', "%{$keyword}%");
        }
        
        return $qb->orderBy('au.id', 'ASC')
                 ->executeQuery()
                 ->fetchAllAssociative();
    }

    /**
     * 根據ID查找使用者
     */
    public function findById(int $id): ?array
    {
        return $this->db->createQueryBuilder()
            ->select('au.*, pg.name as group_name')
            ->from('admin_users', 'au')
            ->leftJoin('au', 'permissions_groups', 'pg', 'au.group_id = pg.id')
            ->where('au.id = :id')
            ->andWhere('au.deleted_at IS NULL')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative() ?: null;
    }

    /**
     * 根據使用者名稱查找使用者
     */
    public function findByUsername(string $username): ?array
    {
        return $this->db->createQueryBuilder()
            ->select('au.*, pg.name as group_name')
            ->from('admin_users', 'au')
            ->leftJoin('au', 'permissions_groups', 'pg', 'au.group_id = pg.id')
            ->where('au.username = :username')
            ->andWhere('au.deleted_at IS NULL')
            ->setParameter('username', $username)
            ->executeQuery()
            ->fetchAssociative() ?: null;
    }

    /**
     * 創建新使用者
     */
    public function create(array $data): int
    {
        $this->db->insert('admin_users', [
            'username' => $data['username'],
            'password_hash' => $data['password'],
            'display_name' => $data['display_name'],
            'group_id' => $data['group_id'],
            'status' => $data['status'] ?? 1,
            'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s')
        ]);
        
        return (int)$this->db->lastInsertId();
    }

    /**
     * 更新使用者資料
     */
    public function update(int $id, array $data): bool
    {
        $updateData = [
            'display_name' => $data['display_name'],
            'group_id' => $data['group_id'],
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s')
        ];
        
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }
        
        if (!empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }
        
        return $this->db->update('admin_users', $updateData, ['id' => $id]) > 0;
    }

    /**
     * 更新使用者狀態
     */
    public function updateStatus(int $id, int $status): bool
    {
        return $this->db->update('admin_users', [
            'status' => $status,
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s')
        ], ['id' => $id]) > 0;
    }

    /**
     * 更新使用者密碼
     */
    public function updatePassword(int $id, string $hashedPassword): bool
    {
        return $this->db->update('admin_users', [
            'password_hash' => $hashedPassword,
            'updated_at' => (new DateTime())->format('Y-m-d H:i:s')
        ], ['id' => $id]) > 0;
    }

    /**
     * 更新最後登入時間
     */
    public function updateLastLogin(int $id): bool
    {
        return $this->db->update('admin_users', [
            'last_login_at' => (new DateTime())->format('Y-m-d H:i:s')
        ], ['id' => $id]) > 0;
    }

    /**
     * 軟刪除使用者
     */
    public function softDelete(int $id): bool
    {
        return $this->db->update('admin_users', [
            'deleted_at' => (new DateTime())->format('Y-m-d H:i:s')
        ], ['id' => $id]) > 0;
    }

    /**
     * 恢復已刪除的使用者
     */
    public function restore(int $id): bool
    {
        return $this->db->update('admin_users', [
            'deleted_at' => null
        ], ['id' => $id]) > 0;
    }
}
