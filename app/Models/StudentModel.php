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
     * Lấy danh sách sinh viên theo ID lớp học
     * @param int $classId
     * @return array
     */
    public function getStudentsByClass(int $classId): array
    {
        $sql = "SELECT s.student_id, s.mssv, u.full_name, a.email 
                FROM students s
                JOIN users u ON s.user_id = u.user_id
                JOIN accounts a ON u.account_id = a.account_id
                WHERE s.class_id = ? AND s.deleted_at IS NULL";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$classId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("StudentModel::getStudentsByClass error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find student by user ID.
     * @param int $userId
     * @return array|false
     */
    public function findByUserId(int $userId): array|false
    {
        $sql = "SELECT s.* FROM {$this->table} s WHERE s.user_id = :user_id AND s.deleted_at IS NULL LIMIT 1";
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
        $sql = "SELECT COALESCE(s.student_id, 0) as student_id, 
                u.user_id,
               COALESCE(s.mssv, 'Chưa có MSSV') as mssv, 
               u.full_name, u.gender, u.avatar, u.date_of_birth, u.phone_number, u.address,
               a.username, a.email, a.status,
               COALESCE(c.class_name, 'Chưa có lớp') as class_name, 
               COALESCE(f.faculty_name, 'Chưa có khoa') as faculty_name 
        FROM accounts a 
        INNER JOIN users u ON a.account_id = u.account_id 
        LEFT JOIN students s ON u.user_id = s.user_id 
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN faculties f ON s.faculty_id = f.faculty_id
        WHERE COALESCE(s.student_id, 0) = :id AND a.role = 'student' AND s.deleted_at IS NULL LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

            $sql = "SELECT 
                COALESCE(s.student_id, 0) as student_id, 
                COALESCE(s.mssv, 'Chưa có MSSV') as mssv, 
                COALESCE(s.class_id, 0) as class_id, 
                COALESCE(s.faculty_id, 0) as faculty_id, 
                COALESCE(s.academic_year, 'Chưa có') as academic_year,
                u.full_name, u.gender, u.phone_number, u.date_of_birth, u.address,
                a.email, a.status, 
                COALESCE(c.class_name, 'Chưa có lớp') as class_name, 
                COALESCE(f.faculty_name, 'Chưa có khoa') as faculty_name
            FROM accounts a
            INNER JOIN users u ON a.account_id = u.account_id
            LEFT JOIN students s ON u.user_id = s.user_id
            LEFT JOIN classes c ON s.class_id = c.class_id
            LEFT JOIN faculties f ON s.faculty_id = f.faculty_id
            WHERE a.role = 'student' 
              AND (COALESCE(s.mssv, '') LIKE ? OR u.full_name LIKE ?)
              AND s.deleted_at IS NULL
            ORDER BY COALESCE(s.student_id, 0) DESC
            LIMIT ? OFFSET ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$search, $search, $limit, $offset]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Count total
            $countSql = "SELECT COUNT(*) 
                 FROM accounts a
                 INNER JOIN users u ON a.account_id = u.account_id
                 LEFT JOIN students s ON u.user_id = s.user_id
                 WHERE a.role = 'student' 
                   AND (COALESCE(s.mssv, '') LIKE ? OR u.full_name LIKE ?)
                   AND s.deleted_at IS NULL";
            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->execute([$search, $search]);
            $total = (int)$countStmt->fetchColumn();

            return [
                'students' => $students,
                'total' => $total
            ];
        } catch (PDOException $e) {
            error_log("StudentModel::getStudentsWithPagination error: " . $e->getMessage());
            return [
                'students' => [],
                'total' => 0
            ];
        }
    }

    /**
     * Lấy tất cả sinh viên có role là student (bao gồm cả những sinh viên chưa có lớp/khoa)
     * @return array
     */
    public function getAllStudentsWithRole(): array
    {
        $sql = "SELECT 
                s.student_id, s.mssv, s.class_id, s.faculty_id, s.academic_year,
                u.full_name, u.gender, u.phone_number, u.date_of_birth, u.address,
                a.email, a.status, a.role,
                c.class_name, 
                f.faculty_name
            FROM accounts a
            JOIN users u ON a.account_id = u.account_id
            LEFT JOIN students s ON u.user_id = s.user_id
            LEFT JOIN classes c ON s.class_id = c.class_id
            LEFT JOIN faculties f ON s.faculty_id = f.faculty_id
            WHERE a.role = 'student' AND s.deleted_at IS NULL
            ORDER BY s.student_id DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("StudentModel::getAllStudentsWithRole error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new student (assumes user_id exists).
     * @param array $data (user_id, mssv, class_id, faculty_id, academic_year)
     * @return int|false Inserted ID or false on failure
     */
    public function create(array $data): int|false
    {
        // Debug: ghi log dữ liệu đầu vào
        error_log("StudentModel::create - Input data: " . json_encode($data));

        $sql = "INSERT INTO {$this->table} (user_id, mssv, class_id, faculty_id, academic_year) 
            VALUES (:user_id, :mssv, :class_id, :faculty_id, :academic_year)";

        error_log("StudentModel::create - SQL: " . $sql);

        $stmt = $this->pdo->prepare($sql);
        try {
            $result = $stmt->execute([
                'user_id' => $data['user_id'],
                'mssv' => $data['mssv'],
                'class_id' => $data['class_id'],
                'faculty_id' => $data['faculty_id'],
                'academic_year' => $data['academic_year'] ?? '2021-2025'
            ]);
            if (empty($data['class_id']) || empty($data['faculty_id'])) {
                error_log("StudentModel::create - Error: class_id hoặc faculty_id không được để trống.");
                return false;
            }

            if ($result) {
                $lastId = (int)$this->pdo->lastInsertId();
                error_log("StudentModel::create - Success, ID: " . $lastId);
                return $lastId;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("StudentModel::create - Execute failed: " . json_encode($errorInfo));
                return false;
            }
        } catch (PDOException $e) {
            error_log("StudentModel::create PDO Error: " . $e->getMessage());
            error_log("StudentModel::create Error Info: " . json_encode($stmt->errorInfo()));
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
            return false;
        }

        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $params[':id'] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE student_id = :id AND deleted_at IS NULL";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("StudentModel update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find student by MSSV.
     * @param string $mssv
     * @return array|false
     */
    public function findByMssv(string $mssv): array|false
    {
        $sql = "SELECT s.* FROM {$this->table} s WHERE s.mssv = :mssv AND s.deleted_at IS NULL LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([':mssv' => $mssv]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("StudentModel::findByMssv error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kiểm tra xem Mã số sinh viên (mssv) đã tồn tại hay chưa.
     * @param string $mssv Mã số sinh viên
     * @return bool Trả về TRUE nếu mã số đã tồn tại, FALSE nếu chưa.
     */
    public function isStudentCodeExists(string $mssv): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE mssv = :mssv AND deleted_at IS NULL LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':mssv' => $mssv]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("StudentModel::isStudentCodeExists error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Soft delete a student by setting deleted_at.
     * @param int $studentId
     * @return bool
     */
    public function delete(int $studentId): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE student_id = :id AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        try {
            return $stmt->execute(['id' => $studentId]) && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("StudentModel::delete (soft) error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Đếm số sinh viên trong lớp
     */
    public function countStudentsByClass(int $classId): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM students 
                WHERE class_id = ? AND deleted_at IS NULL";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$classId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Find students by class ID.
     * @param int $classId
     * @return array
     */
    public function findByClassId(int $classId): array
    {
        $sql = "SELECT s.* FROM {$this->table} s WHERE s.class_id = :class_id AND s.deleted_at IS NULL";
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
                ) AND s.deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
