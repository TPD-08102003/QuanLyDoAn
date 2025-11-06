<?php
// models/BaseModel.php
// Base model class for common functionality

namespace App\Models;

use PDO;
use PDOStatement;
use PDOException;

abstract class BaseModel
{
    protected PDO $pdo;
    protected string $table;

    // BaseModel.php
    protected string $primaryKey = 'id';

    public function __construct(PDO $pdo, string $table, string $primaryKey = 'id')
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }

    /**
     * Find all records.
     * @return array
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find record by ID.
     * @param int $id
     * @return array|false
     */
    // public function findById(int $id): array|false
    // {
    //     $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
    //     $stmt->execute(['id' => $id]);
    //     return $stmt->fetch(PDO::FETCH_ASSOC);
    // }

    // Trong BaseModel
    // public function findById(int $id): array|false
    // {
    //     // Sử dụng $this->primaryKey thay vì 'id'
    //     $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->execute([':id' => $id]);
    //     return $stmt->fetch(PDO::FETCH_ASSOC);
    // }
    // Trong BaseModel
    public function findById(int $id): array|false
    {
        try {
            // Sử dụng $this->primaryKey thay vì 'id'
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in BaseModel::findById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new record.
     * @param array $data
     * @return int|false Inserted ID or false on failure
     */
    public function create(array $data): int|false
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    /**
     * Update record by ID.
     * @param int $id
     * @param array $data
     * @return bool
     */
    // public function update(int $id, array $data): bool
    // {
    //     if (empty($data)) {
    //         return false;
    //     }

    //     $fields = [];
    //     $params = [];

    //     foreach ($data as $key => $value) {
    //         if ($value !== null) { // Bỏ qua null nếu không muốn cập nhật
    //             $fields[] = "$key = :$key";
    //             $params[":$key"] = $value;
    //         }
    //     }

    //     if (empty($fields)) {
    //         return false;
    //     }

    //     $params[':id'] = $id;

    //     $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE account_id = :id";

    //     try {
    //         $stmt = $this->pdo->prepare($sql);
    //         return $stmt->execute($params);
    //     } catch (PDOException $e) {
    //         error_log("AccountModel update error: " . $e->getMessage());
    //         return false; // hoặc throw $e nếu muốn bắt ở controller
    //     }
    // }

    // Trong BaseModel
    /**
     * Update record by ID.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if ($value !== null) { // Bỏ qua null nếu không muốn cập nhật
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        // Sử dụng $this->primaryKey thay vì 'account_id'
        $params[':id'] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("BaseModel update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete record by ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Execute custom query.
     * @param string $sql
     * @param array $params
     * @return array|bool
     */
    public function query(string $sql, array $params = []): array|bool
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
