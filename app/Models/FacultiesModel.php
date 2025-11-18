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
    // public function findByName(string $name): array|false
    // {
    //     $sql = "SELECT faculty_id, faculty_name, description, deleted_at, created_at, updated_at 
    //     FROM faculties 
    //     WHERE faculty_name = :name";

    //     $result = $this->query($sql, [':name' => $name]);
    //     return $result[0] ?? false;
    // }

    public function findByName(string $name): array|false
    {
        $sql = "SELECT faculty_id, faculty_name, description, deleted_at, created_at, updated_at 
            FROM faculties 
            WHERE TRIM(faculty_name) = :name AND deleted_at IS NULL";

        $result = $this->query($sql, [':name' => trim($name)]);
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
    // public function softDelete(int $id): bool
    // {
    //     $sql = "UPDATE faculties SET deleted_at = NOW() WHERE faculty_id = :id AND deleted_at IS NULL";
    //     $stmt = $this->pdo->prepare($sql);
    //     return $stmt->execute([':id' => $id]);
    // }
    public function softDelete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE {$this->primaryKey} = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }


    public function getFacultiesWithPagination($limit, $offset, $keyword = null)
    {
        try {
            // Lấy danh sách faculties
            if (!empty($keyword)) {
                $sql = "SELECT * FROM faculties 
                    WHERE faculty_name LIKE ? 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(['%' . $keyword . '%', (int)$limit, (int)$offset]);
                $faculties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $sql = "SELECT * FROM faculties 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([(int)$limit, (int)$offset]);
                $faculties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Lấy tổng số records
            if (!empty($keyword)) {
                $countSql = "SELECT COUNT(*) as total FROM faculties WHERE faculty_name LIKE ?";
                $countStmt = $this->pdo->prepare($countSql);
                $countStmt->execute(['%' . $keyword . '%']);
            } else {
                $countSql = "SELECT COUNT(*) as total FROM faculties";
                $countStmt = $this->pdo->prepare($countSql);
                $countStmt->execute();
            }

            $totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
            $total = $totalResult['total'] ?? 0;

            return [
                'faculties' => $faculties,
                'total' => $total
            ];
        } catch (PDOException $e) {
            error_log("Database error in getFacultiesWithPagination: " . $e->getMessage());
            return [
                'faculties' => [],
                'total' => 0
            ];
        }
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
    // public function update(int $id, array $data): bool
    // {
    //     try {
    //         $sql = "UPDATE faculties 
    //                 SET faculty_name = :faculty_name, 
    //                     description = :description,
    //                     updated_at = NOW()
    //                 WHERE faculty_id = :id";
    //         $stmt = $this->pdo->prepare($sql);
    //         return $stmt->execute([
    //             ':faculty_name' => $data['faculty_name'],
    //             ':description' => $data['description'] ?? null,
    //             ':id' => $id
    //         ]);
    //     } catch (PDOException $e) {
    //         error_log("Error in FacultiesModel::update: " . $e->getMessage());
    //         return false;
    //     }
    // }

    // Trong App/Models/FacultiesModel.php

    public function update(int $id, array $data): bool
    {
        // THÊM TRY...CATCH VÀO ĐÂY
        try {
            $fields = [];
            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
            }

            // Đảm bảo có gì đó để cập nhật
            if (empty($fields)) {
                return true; // Không có gì thay đổi, nhưng không phải lỗi
            }

            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = :id";

            $stmt = $this->pdo->prepare($sql);

            // Chỉ bind các giá trị trong $data, và bind :id riêng
            $data['id'] = $id;

            return $stmt->execute($data);
        } catch (PDOException $e) {
            // ĐÂY LÀ "LOG SERVER" MÀ BẠN CẦN KIỂM TRA
            error_log("!!! PDOException in FacultiesModel::update: " . $e->getMessage());
            return false;
        }
    }

    // public function update(int $id, array $data): bool
    // {
    //     try { // Thêm try-catch
    //         $fields = [];
    //         $params = []; // Dùng params riêng biệt

    //         foreach ($data as $key => $value) {
    //             // Chỉ lấy những trường hợp lệ để cập nhật
    //             if (in_array($key, ['faculty_name', 'description'])) {
    //                 $fields[] = "$key = :$key";
    //                 $params[":$key"] = $value;
    //             }
    //         }

    //         if (empty($fields)) {
    //             // Không có trường nào để cập nhật, coi như thành công (hoặc warning)
    //             error_log("No fields to update for faculty ID: " . $id);
    //             return true;
    //         }

    //         $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE {$this->primaryKey} = :id";
    //         $stmt = $this->pdo->prepare($sql);

    //         // Bind giá trị cho id và các trường
    //         $params[':id'] = $id;

    //         return $stmt->execute($params);
    //     } catch (PDOException $e) {
    //         error_log("Error in FacultiesModel::update: " . $e->getMessage()); // Ghi log lỗi
    //         return false;
    //     }
    // }
    public function getTotalFaculties(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM faculties");
        return (int) $stmt->fetchColumn();
    }
}
