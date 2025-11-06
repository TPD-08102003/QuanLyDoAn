<?php
// app/Models/ClassesModel.php

namespace App\Models;

use PDO;

class ClassesModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
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

    public function create(array $data): int|false
    {
        $sql = "INSERT INTO classes (class_name, faculty_id, description, created_at, updated_at) 
                VALUES (:class_name, :faculty_id, :description, :created_at, :updated_at)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function update(int $classId, array $data): bool
    {
        $sql = "UPDATE classes 
                SET class_name = :class_name, 
                    faculty_id = :faculty_id, 
                    description = :description, 
                    updated_at = :updated_at 
                WHERE class_id = :class_id";

        $data['class_id'] = $classId;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $classId): bool
    {
        $sql = "DELETE FROM classes WHERE class_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$classId]);
    }
}
