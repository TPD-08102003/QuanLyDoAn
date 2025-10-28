<?php
// models/AccountModel.php

namespace App\Models;

use PDO;

class AccountModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'accounts');
    }

    /**
     * Find account by username.
     * @param string $username
     * @return array|false
     */
    public function findByUsername(string $username): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find account by email.
     * @param string $email
     * @return array|false
     */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Authenticate user (verify password).
     * @param string $usernameOrEmail
     * @param string $password
     * @return array|false
     */
    public function authenticate(string $usernameOrEmail, string $password): array|false
    {
        $account = strpos($usernameOrEmail, '@') ? $this->findByEmail($usernameOrEmail) : $this->findByUsername($usernameOrEmail);
        if ($account && password_verify($password, $account['password'])) {
            return $account;
        }
        return false;
    }

    /**
     * Hash password before create/update.
     * @param array $data
     * @return array
     */
    public function prepareData(array $data): array
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Find accounts by role.
     * @param string $role
     * @return array
     */
    public function findByRole(string $role): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE role = :role");
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update status by ID.
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET status = :status WHERE account_id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }
}
