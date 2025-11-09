<?php
// models/LecturerModel.php

namespace App\Models;

use PDO;
use PDOException;

class LecturerModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'lecturers', 'lecturer_id');
    }

    /**
     * Lấy tất cả giảng viên có role teacher (bao gồm cả những chưa có khoa)
     * @return array
     */
    public function getAllLecturersWithRole(): array
    {
        $sql = "SELECT 
                l.lecturer_id, l.lecturer_code, l.faculty_id, l.position, l.specialization, l.years_of_experience,
                u.full_name, u.gender, u.phone_number, u.date_of_birth, u.address,
                a.email, a.status, a.role,
                f.faculty_name
            FROM accounts a
            JOIN users u ON a.account_id = u.account_id
            LEFT JOIN lecturers l ON u.user_id = l.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            WHERE a.role = 'teacher' AND l.deleted_at IS NULL
            ORDER BY l.lecturer_id DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("LecturerModel::getAllLecturersWithRole error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get lecturers with pagination and search
     * @param int $limit
     * @param int $offset
     * @param string $keyword
     * @return array ['lecturers' => array, 'total' => int]
     */
    public function getLecturersWithPagination(int $limit, int $offset, string $keyword = ''): array
    {
        try {
            $search = "%$keyword%";

            $sql = "SELECT 
                l.lecturer_id, l.lecturer_code, l.position, l.specialization, l.years_of_experience,
                u.full_name, u.gender, u.phone_number, u.date_of_birth, u.address,
                a.email, a.status, 
                COALESCE(f.faculty_name, 'Chưa có khoa') as faculty_name
            FROM accounts a
            INNER JOIN users u ON a.account_id = u.account_id
            LEFT JOIN lecturers l ON u.user_id = l.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            WHERE a.role = 'teacher' 
              AND (COALESCE(l.lecturer_code, '') LIKE ? OR u.full_name LIKE ?)
              AND l.deleted_at IS NULL
            ORDER BY l.lecturer_id DESC
            LIMIT ? OFFSET ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$search, $search, $limit, $offset]);
            $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Count total
            $countSql = "SELECT COUNT(*) 
                 FROM accounts a
                 INNER JOIN users u ON a.account_id = u.account_id
                 LEFT JOIN lecturers l ON u.user_id = l.user_id
                 WHERE a.role = 'teacher' 
                   AND (COALESCE(l.lecturer_code, '') LIKE ? OR u.full_name LIKE ?)
                   AND l.deleted_at IS NULL";
            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->execute([$search, $search]);
            $total = (int)$countStmt->fetchColumn();

            return [
                'lecturers' => $lecturers,
                'total' => $total
            ];
        } catch (PDOException $e) {
            error_log("LecturerModel::getLecturersWithPagination error: " . $e->getMessage());
            return ['lecturers' => [], 'total' => 0];
        }
    }

    /**
     * Create a new lecturer
     * @param array $data (user_id, lecturer_code, faculty_id, position, specialization, years_of_experience)
     * @return int|false Inserted ID or false
     */
    public function create(array $data): int|false
    {
        $sql = "INSERT INTO {$this->table} (user_id, lecturer_code, faculty_id, position, specialization, years_of_experience) 
                VALUES (:user_id, :lecturer_code, :faculty_id, :position, :specialization, :years_of_experience)";

        $stmt = $this->pdo->prepare($sql);
        try {
            $result = $stmt->execute([
                'user_id' => $data['user_id'],
                'lecturer_code' => $data['lecturer_code'],
                'faculty_id' => $data['faculty_id'],
                'position' => $data['position'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'years_of_experience' => $data['years_of_experience'] ?? 0
            ]);
            return $result ? (int)$this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("LecturerModel::create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update lecturer
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data)) return false;

        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $params[':id'] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE lecturer_id = :id AND deleted_at IS NULL";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("LecturerModel::update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete lecturer
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE lecturer_id = :id AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        try {
            return $stmt->execute(['id' => $id]) && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("LecturerModel::delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if lecturer_code exists
     * @param string $code
     * @return bool
     */
    public function isLecturerCodeExists(string $code): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE lecturer_code = :code AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['code' => $code]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Find by lecturer_code
     * @param string $code
     * @return array|false
     */
    public function findByCode(string $code): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE lecturer_code = :code AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get full lecturer info
     * @param int $id
     * @return array|false
     */
    public function getFullLecturer(int $id): array|false
    {
        $sql = "SELECT l.*, u.full_name, u.gender, u.date_of_birth, u.phone_number, u.address,
                a.username, a.email, a.status,
                f.faculty_name
                FROM {$this->table} l
                JOIN users u ON l.user_id = u.user_id
                JOIN accounts a ON u.account_id = a.account_id
                LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
                WHERE l.lecturer_id = :id AND l.deleted_at IS NULL LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
