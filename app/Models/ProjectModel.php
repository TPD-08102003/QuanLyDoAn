<?php
// models/ProjectModel.php

namespace App\Models;

use PDO;

class ProjectModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'projects', 'project_id');
    }

    /**
     * Get all projects with lecturer information
     * @return array
     */
    public function getAllWithDetails(): array
    {
        $sql = "
            SELECT 
                p.project_id,
                p.title,
                p.description,
                p.status,
                p.created_at,
                l.lecturer_id,
                u.full_name AS lecturer_name,
                f.faculty_name
            FROM {$this->table} p
            LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            ORDER BY p.created_at DESC
        ";

        return $this->query($sql);
    }

    /**
     * Get projects with pagination and search
     * @param int $limit
     * @param int $offset
     * @param string $keyword
     * @return array
     */
    public function getProjectsWithPagination(int $limit, int $offset, string $keyword): array
    {
        $sql = "
            SELECT 
                p.project_id,
                p.title,
                p.description,
                p.status,
                p.created_at,
                l.lecturer_id,
                u.full_name AS lecturer_name,
                f.faculty_name
            FROM {$this->table} p
            LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            WHERE p.title LIKE :keyword 
               OR p.description LIKE :keyword 
               OR u.full_name LIKE :keyword
               OR f.faculty_name LIKE :keyword
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':keyword', "%$keyword%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countSql = "
            SELECT COUNT(*) as total
            FROM {$this->table} p
            LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            WHERE p.title LIKE :keyword 
               OR p.description LIKE :keyword 
               OR u.full_name LIKE :keyword
               OR f.faculty_name LIKE :keyword
        ";

        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->bindValue(':keyword', "%$keyword%");
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return ['projects' => $projects, 'total' => $total];
    }

    /**
     * Get project with details by ID
     * @param int $id
     * @return array|false
     */
    public function getByIdWithDetails(int $id): array|false
    {
        $sql = "
            SELECT 
                p.*,
                l.lecturer_id,
                u.full_name AS lecturer_name,
                f.faculty_name
               
            FROM {$this->table} p
            LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            WHERE p.project_id = :id
        ";

        $result = $this->query($sql, [':id' => $id]);
        return $result ? $result[0] : false;
    }

    /**
     * Find project by title
     * @param string $title
     * @return array|false
     */
    public function findByTitle(string $title): array|false
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE title = :title
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':title' => $title]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new project
     * @param array $data
     * @return int|false
     */
    public function createProject(array $data): int|false
    {
        // Validate required fields
        if (empty($data['title']) || empty($data['lecturer_id'])) {
            return false;
        }

        $data['status'] = $data['status'] ?? 'ChoDuyet';
        $data['created_at'] = date('Y-m-d H:i:s');

        return $this->create($data);
    }

    /**
     * Update project
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProject(int $id, array $data): bool
    {
        // Remove empty fields if needed
        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });

        return $this->update($id, $data);
    }

    /**
     * Delete project and related data (groups, reports, etc.)
     * @param int $id
     * @return bool
     */
    public function deleteProject(int $id): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Delete related report_types
            $this->pdo->prepare("DELETE FROM report_types WHERE project_id = :id")->execute([':id' => $id]);

            // Delete related groups
            $this->pdo->prepare("DELETE FROM groups WHERE project_id = :id")->execute([':id' => $id]);

            // Delete project
            $result = $this->delete($id);

            $this->pdo->commit();
            return $result;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error deleting project: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available lecturers for assignment
     * @return array
     */
    public function getAvailableLecturers(): array
    {
        $sql = "
            SELECT 
                l.lecturer_id,
                u.full_name,
                f.faculty_name,
                COUNT(p.project_id) AS project_count
            FROM lecturers l
            LEFT JOIN users u ON l.user_id = u.user_id
            LEFT JOIN faculties f ON l.faculty_id = f.faculty_id
            LEFT JOIN projects p ON l.lecturer_id = p.lecturer_id
            WHERE l.deleted_at IS NULL
            GROUP BY l.lecturer_id
            HAVING project_count < 10  -- Giả sử giới hạn 10 đồ án/giảng viên
            ORDER BY u.full_name
        ";

        return $this->query($sql);
    }

    /**
     * Change project status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function changeStatus(int $id, string $status): bool
    {
        $validStatuses = ['ChoDuyet', 'DaDuyet', 'DangThucHien', 'DaNopBaoCao', 'DaBaoVe', 'HoanThanh', 'Huy'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return $this->update($id, ['status' => $status]);
    }
}
