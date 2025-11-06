<?php
// models/UserModel.php

namespace App\Models;

use PDO;
use PDOException;

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

    /**
     * Create a new user associated with an account ID.
     * Supports dynamic fields including the new 'gender' column.
     * Uses parent::create() for compatibility.
     * @param int $accountId
     * @param array $data User data (e.g., ['full_name' => '...', 'gender' => 'male', ...])
     * @return int|false The new user_id or false on failure
     */
    public function createWithAccount(int $accountId, array $data): int|false
    {
        if (empty($data)) {
            return false;
        }

        $data['account_id'] = $accountId;

        return parent::create($data);
    }

    public function updateByAccountId(int $accountId, array $data): bool
    {
        if (empty($data)) {
            return true;
        }

        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $params[':account_id'] = $accountId;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE account_id = :account_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            // Ghi log lỗi hoặc xử lý (Nếu bạn có một hệ thống ghi log)
            // error_log("UserModel update error: " . $e->getMessage());
            return false;
        }
    }

    public function createStudent(int $userId, string $mssv, int $classId): bool
    {
        $sql = "INSERT INTO students (user_id, mssv, class_id) VALUES (:user_id, :mssv, :class_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'mssv' => $mssv,
            'class_id' => $classId  // Thêm param này
        ]);
    }

    public function createLecturer(int $userId, string $department = 'Công nghệ thông tin'): bool
    {
        $sql = "INSERT INTO lecturers (user_id, department) VALUES (:user_id, :department)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'department' => $department
        ]);
    }

    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $params[':id'] = $id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            die("❌ UserModel update error: " . $e->getMessage());
        }
    }
}
