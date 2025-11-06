<?php

namespace App\Models;

use PDO;
use PDOException;

class FacultiesModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'faculties', 'faculty_id');
    }

    /**
     * Tìm khoa theo ID
     */
    public function findById(int $id): array|false
    {
        try {
            $sql = "SELECT faculty_id, faculty_name, description, deleted_at, created_at 
                    FROM faculties 
                    WHERE faculty_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in FacultiesModel::findById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách khoa không bị xóa mềm (deleted_at IS NULL)
     */
    public function getActiveFaculties(): array
    {
        $sql = "SELECT faculty_id, faculty_name, description, created_at 
                FROM faculties 
                WHERE deleted_at IS NULL 
                ORDER BY faculty_name";
        return $this->query($sql);
    }

    /**
     * Tìm khoa theo tên
     */
    public function findByName(string $name): array|false
    {
        $sql = "SELECT faculty_id, faculty_name, description, created_at 
                FROM faculties 
                WHERE faculty_name = :name AND deleted_at IS NULL 
                LIMIT 1";
        $result = $this->query($sql, [':name' => $name]);
        return $result[0] ?? false;
    }

    /**
     * Kiểm tra tên khoa đã tồn tại chưa
     */
    public function isNameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM faculties WHERE faculty_name = :name AND deleted_at IS NULL";
        $params = [':name' => $name];

        if ($excludeId !== null) {
            $sql .= " AND faculty_id != :excludeId";
            $params[':excludeId'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Xóa mềm khoa (soft delete)
     */
    public function softDelete(int $id): bool
    {
        $sql = "UPDATE faculties SET deleted_at = NOW() WHERE faculty_id = :id AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Lấy danh sách khoa với phân trang và tìm kiếm
     */
    public function getFacultiesWithPagination(int $limit, int $offset, string $keyword = ''): array
    {
        $params = [];
        $where = 'deleted_at IS NULL';

        if (!empty($keyword)) {
            $where .= " AND (faculty_name LIKE :keyword OR description LIKE :keyword)";
            $params[':keyword'] = "%$keyword%";
        }

        // Đếm tổng số
        $countSql = "SELECT COUNT(*) FROM faculties WHERE $where";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Lấy dữ liệu
        $sql = "SELECT faculty_id, faculty_name, description, created_at 
                FROM faculties 
                WHERE $where 
                ORDER BY faculty_name 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => &$value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'faculties' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total
        ];
    }

    /**
     * Tạo khoa mới
     */
    // public function create(array $data): int|false
    // {
    //     try {
    //         $sql = "INSERT INTO faculties (faculty_name, description, created_at) 
    //                 VALUES (:faculty_name, :description, NOW())";
    //         $stmt = $this->pdo->prepare($sql);
    //         return $stmt->execute([
    //             ':faculty_name' => $data['faculty_name'],
    //             ':description' => $data['description'] ?? null
    //         ]);
    //     } catch (PDOException $e) {
    //         error_log("Error in FacultiesModel::create: " . $e->getMessage());
    //         return false;
    //     }
    // }

    // Trong FacultiesModel
    /**
     * Tạo khoa mới
     */
    public function create(array $data): int|false
    {
        try {
            $sql = "INSERT INTO faculties (faculty_name, description, created_at) 
                VALUES (:faculty_name, :description, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                ':faculty_name' => $data['faculty_name'],
                ':description' => $data['description'] ?? null
            ]);

            // Trả về ID nếu thành công, false nếu thất bại
            return $result ? (int)$this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Error in FacultiesModel::create: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Cập nhật khoa
     */
    public function update(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE faculties 
                    SET faculty_name = :faculty_name, 
                        description = :description,
                        updated_at = NOW()
                    WHERE faculty_id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':faculty_name' => $data['faculty_name'],
                ':description' => $data['description'] ?? null,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error in FacultiesModel::update: " . $e->getMessage());
            return false;
        }
    }
}
