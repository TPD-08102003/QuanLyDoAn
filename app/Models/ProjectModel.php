<?php
// models/ProjectModel.php

namespace App\Models;

use PDO;

class ProjectModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'projects');
    }

    /**
     * Get full project info with lecturer.
     * @param int $projectId
     * @return array|false
     */
    public function getFullProject(int $projectId): array|false
    {
        $sql = "SELECT p.*, l.department, u.full_name as lecturer_name 
                FROM {$this->table} p 
                JOIN lecturers l ON p.lecturer_id = l.lecturer_id 
                JOIN users u ON l.user_id = u.user_id 
                WHERE p.project_id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $projectId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find projects by lecturer ID.
     * @param int $lecturerId
     * @return array
     */
    public function findByLecturer(int $lecturerId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE lecturer_id = :lecturer_id");
        $stmt->execute(['lecturer_id' => $lecturerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find projects by status.
     * @param string $status
     * @return array
     */
    public function findByStatus(string $status): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE status = :status");
        $stmt->execute(['status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update project status.
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET status = :status WHERE project_id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /**
     * Get projects with group count.
     * @return array
     */
    public function getWithGroupCount(): array
    {
        $sql = "SELECT p.*, COUNT(g.group_id) as group_count 
                FROM {$this->table} p 
                LEFT JOIN groups g ON p.project_id = g.project_id 
                GROUP BY p.project_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
