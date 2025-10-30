<?php
// models/BaseModel.php
// Base model class for common functionality

namespace App\Models;

use PDO;
use PDOStatement;

abstract class BaseModel
{
    protected PDO $pdo;
    protected string $table;

    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
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
    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
    public function update($id, array $data): bool
    {
        $set = [];
        $params = [];

        foreach ($data as $key => $value) {
            $set[] = "$key = ?";
            $params[] = $value;
        }

        if (empty($set)) {
            return false;
        }

        $setClause = implode(', ', $set);
        $sql = "UPDATE {$this->table} SET $setClause WHERE account_id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($params);
        if (!$result) {
            $error = $stmt->errorInfo();
            error_log("Update failed: SQL: $sql, Params: " . json_encode($params) . ", Error: " . print_r($error, true));
        }
        return $result;
    }

    /**
     * Delete record by ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
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
