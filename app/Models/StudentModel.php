<?php
// models/StudentModel.php

namespace App\Models;

use PDO;
use PDOException;

class StudentModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'students', 'student_id');
    }

    /**
     * Find student by user ID.
     * @param int $userId
     * @return array|false
     */
    public function findByUserId(int $userId): array|false
    {
        $sql = "SELECT s.* FROM {$this->table} s WHERE s.user_id = :user_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get full student info with user, class, and faculty.
     * @param int $studentId
     * @return array|false
     */
    public function getFullStudent(int $studentId): array|false
    {
        $sql = "SELECT s.*, 
                   u.full_name, u.gender, u.avatar, u.date_of_birth, u.phone_number, u.address,
                   a.username, a.email, a.status,
                   c.class_name, 
                   f.faculty_name 
            FROM {$this->table} s 
            JOIN users u ON s.user_id = u.user_id 
            JOIN accounts a ON u.account_id = a.account_id 
            JOIN classes c ON s.class_id = c.class_id
            JOIN faculties f ON s.faculty_id = f.faculty_id
            WHERE s.student_id = :id LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find students by class ID.
     * @param int $classId
     * @return array
     */
    public function findByClassId(int $classId): array
    {
        $sql = "SELECT s.* FROM {$this->table} s WHERE s.class_id = :class_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['class_id' => $classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find students not in any group.
     * @return array
     */
    public function findAvailableStudents(): array
    {
        $sql = "SELECT s.* FROM {$this->table} s 
                WHERE s.student_id NOT IN (
                    SELECT gm.student_id FROM group_members gm 
                    JOIN groups g ON gm.group_id = g.group_id
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get students with pagination, search, including full info.
     * @param int $limit
     * @param int $offset
     * @param string $keyword
     * @return array
     */
    public function getStudentsWithPagination(int $limit, int $offset, string $keyword = ''): array
    {
        try {
            $search = "%$keyword%";

            // Câu SQL chính để lấy danh sách sinh viên
            $sql = "SELECT s.student_id, s.mssv, s.class_id, s.faculty_id, s.academic_year, 
                       u.full_name, u.gender, u.phone_number, u.date_of_birth, u.address,
                       a.email, a.status, c.class_name, f.faculty_name
                FROM students s 
                JOIN users u ON s.user_id = u.user_id 
                JOIN accounts a ON u.account_id = a.account_id 
                JOIN classes c ON s.class_id = c.class_id
                JOIN faculties f ON s.faculty_id = f.faculty_id
                WHERE (s.mssv LIKE :keyword OR u.full_name LIKE :keyword) 
                ORDER BY s.student_id DESC
                LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':keyword', $search, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Đếm tổng số bản ghi
            $countSql = "SELECT COUNT(*) 
                     FROM students s 
                     JOIN users u ON s.user_id = u.user_id 
                     JOIN accounts a ON u.account_id = a.account_id 
                     JOIN classes c ON s.class_id = c.class_id
                     JOIN faculties f ON s.faculty_id = f.faculty_id
                     WHERE (s.mssv LIKE :keyword OR u.full_name LIKE :keyword)";

            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->bindValue(':keyword', $search, PDO::PARAM_STR);
            $countStmt->execute();
            $total = $countStmt->fetchColumn();

            return [
                'students' => $students,
                'total' => $total
            ];
        } catch (PDOException $e) {
            error_log("PDO Error in getStudentsWithPagination: " . $e->getMessage());
            return [
                'students' => [],
                'total' => 0
            ];
        }
    }

    /**
     * Create a new student (assumes user_id exists).
     * @param array $data (user_id, mssv, class_id, faculty_id, academic_year)
     * @return int|false Inserted ID or false on failure
     */
    public function create(array $data): int|false
    {
        $sql = "INSERT INTO {$this->table} (user_id, mssv, class_id, faculty_id, academic_year) 
                VALUES (:user_id, :mssv, :class_id, :faculty_id, :academic_year)";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([
                'user_id' => $data['user_id'],
                'mssv' => $data['mssv'],
                'class_id' => $data['class_id'],
                'faculty_id' => $data['faculty_id'],
                'academic_year' => $data['academic_year'] ?? '2021-2025'
            ]);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            // error_log("StudentModel create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a student.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            return true;
        }

        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if ($key !== 'user_id') { // user_id should not be updated
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        $params[':id'] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE student_id = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            // error_log("StudentModel update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a student.
     * @param int $ClassId
     * @return bool
     */
    public function delete(int $ClassId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE student_id = :id";
        $stmt = $this->pdo->prepare($sql);
        try {
            return $stmt->execute(['id' => $ClassId]);
        } catch (PDOException $e) {
            // error_log("StudentModel delete error: " . $e->getMessage());
            return false;
        }
    }

    public function getStudentsByClass(int $classId): array
    {
        $sql = "SELECT s.student_id, s.student_code, s.student_name, s.email, s.phone, s.status
                FROM students s 
                WHERE s.class_id = ? 
                ORDER BY s.student_code";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$classId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm số sinh viên trong lớp
     */
    public function countStudentsByClass(int $classId): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM students 
                WHERE class_id = ? AND status = 'active'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$classId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
