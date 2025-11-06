<?php
// app/Models/ClassesModel.php

namespace App\Models;

use PDO;
use PDOException;

class ClassesModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {

        parent::__construct($pdo, 'classes', 'class_id');
    }


    /**
     * Lấy thông tin chi tiết của một lớp học dựa trên ID, bao gồm tên Khoa (Faculty).
     * @param int $classId
     * @return array|null
     */
    public function findWithFaculty(int $classId): ?array
    {
        $sql = "
        SELECT 
            c.*, 
            f.faculty_name
        FROM 
            classes c
        LEFT JOIN 
            faculties f ON c.faculty_id = f.faculty_id
        WHERE 
           c.class_id = ?
    ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$classId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Trả về null nếu không tìm thấy, ngược lại trả về mảng dữ liệu
            return $result ?: null;
        } catch (\PDOException $e) {
            // Luôn ghi log lỗi để dễ dàng debug
            error_log("Database Error in ClassesModel::findWithFaculty: " . $e->getMessage());
            return null;
        }
    }

    public function getClassesWithPagination(int $limit = 10, int $offset = 0, string $keyword = ''): array
    {
        $sql = "SELECT c.*, f.faculty_name 
                FROM classes c 
                LEFT JOIN faculties f ON c.faculty_id = f.faculty_id 
                WHERE 1=1";

        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (c.class_name LIKE ? OR c.description LIKE ?)";
            $searchTerm = "%$keyword%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalClasses(string $keyword = ''): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM classes c 
                WHERE 1=1";

        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (c.class_name LIKE ? OR c.description LIKE ?)";
            $searchTerm = "%$keyword%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getClassById(int $classId): ?array
    {
        $sql = "SELECT c.*, f.faculty_name 
                FROM classes c 
                LEFT JOIN faculties f ON c.faculty_id = f.faculty_id 
                WHERE c.class_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$classId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getClassByName(string $className): ?array
    {
        $sql = "SELECT * FROM classes WHERE class_name = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$className]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }



    /**
     * Create a new record.
     * @param array $data
     * @return int|false Inserted ID or false on failure
     */
    // HÀM CREATE ĐÃ SỬA
    public function create(array $data): int|false
    {
        // Câu lệnh SQL chỉ cần 3 cột, vì created_at có DEFAULT trong DB
        $sql = "INSERT INTO classes (class_name, faculty_id, description) 
                 VALUES (:class_name, :faculty_id, :description)";

        try {
            $stmt = $this->pdo->prepare($sql);

            // Chỉ giữ lại các khóa cần thiết (class_name, faculty_id, description)
            $bindData = [
                ':class_name' => $data['class_name'] ?? null,
                ':faculty_id' => $data['faculty_id'] ?? null,
                ':description' => $data['description'] ?? null
            ];

            // Xóa các key null để tránh lỗi PDO, mặc dù ở Controller đã kiểm tra
            $bindData = array_filter($bindData, function ($key) {
                return $key !== null;
            });


            if ($stmt->execute($bindData)) {
                return (int) $this->pdo->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("ClassesModel::create error: " . $e->getMessage());
            return false;
        }
    }

    public function update(int $classId, array $data): bool
    {
        $sql = "UPDATE classes 
            SET class_name = :class_name, 
                faculty_id = :faculty_id, 
                description = :description
               
            WHERE class_id = :class_id";

        // Thêm class_id vào mảng data để bind parameter
        $data['class_id'] = $classId;

        try {
            $stmt = $this->pdo->prepare($sql);

            // Thực thi lệnh SQL
            $result = $stmt->execute($data);

            // Trả về true nếu thành công
            return $result;
        } catch (\PDOException $e) {

            error_log("ClassesModel::update PDO Error (Class ID $classId): " . $e->getMessage() .
                "| SQLSTATE: " . $e->getCode() .
                "| Data Sent: " . print_r($data, true));

            // Trả về false để ClassesController bắt và báo lỗi 500 JSON
            return false;
        }
    }

    public function delete(int $classId): bool
    {
        $sql = "DELETE FROM classes WHERE class_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$classId]);
    }

    /**
     * Lấy tất cả các lớp học, bao gồm cả tên Khoa (Faculty Name).
     * Override lại phương thức findAll() của BaseModel.
     * @return array
     */
    public function findAll(): array
    {
        $sql = "SELECT 
                    c.class_id, 
                    c.class_name, 
                    c.faculty_id, 
                    c.description, 
                    c.created_at, 
                    f.faculty_name
                FROM 
                    classes c
                LEFT JOIN 
                    faculties f ON c.faculty_id = f.faculty_id
                ORDER BY 
                    c.class_name ASC";

        try {
            // Sử dụng $this->pdo->query() vì không có tham số
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error in ClassesModel::findAll (Custom): " . $e->getMessage());
            return [];
        }
    }
}
