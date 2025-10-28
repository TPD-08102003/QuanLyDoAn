<?php
// models/UserModel.php

namespace App\Models;

use PDO;

class UserModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'users');
    }

    /**
     * Find user by account ID.
     * @param int $accountId
     * @return array|false
     */
    public function findByAccountId(int $accountId): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE account_id = :account_id LIMIT 1");
        $stmt->execute(['account_id' => $accountId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get full user info with account.
     * @param int $userId
     * @return array|false
     */
    public function getFullUser(int $userId): array|false
    {
        $sql = "SELECT u.*, a.username, a.email, a.role, a.status 
                FROM {$this->table} u 
                JOIN accounts a ON u.account_id = a.account_id 
                WHERE u.user_id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find users by role (via account).
     * @param string $role
     * @return array
     */
    public function findByRole(string $role): array
    {
        $sql = "SELECT u.*, a.username, a.email, a.role 
                FROM {$this->table} u 
                JOIN accounts a ON u.account_id = a.account_id 
                WHERE a.role = :role";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
