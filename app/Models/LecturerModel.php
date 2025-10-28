<?php
// models/LecturerModel.php

namespace App\Models;

use PDO;

class LecturerModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'lecturers');
    }

    /**
     * Find lecturer by user ID.
     * @param int $userId
     * @return array|false
     */
    public function findByUserId(int $userId): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get full lecturer info with user.
     * @param int $lecturerId
     * @return array|false
     */
    public function getFullLecturer(int $lecturerId): array|false
    {
        $sql = "SELECT l.*, u.full_name, u.avatar, u.date_of_birth, u.phone_number, u.address, a.username, a.email 
                FROM {$this->table} l 
                JOIN users u ON l.user_id = u.user_id 
                JOIN accounts a ON u.account_id = a.account_id 
                WHERE l.lecturer_id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $lecturerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find lecturers by department.
     * @param string $department
     * @return array
     */
    public function findByDepartment(string $department): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE department = :department");
        $stmt->execute(['department' => $department]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get lecturers with project count.
     * @return array
     */
    public function getWithProjectCount(): array
    {
        $sql = "SELECT l.*, COUNT(p.project_id) as project_count 
                FROM {$this->table} l 
                LEFT JOIN projects p ON l.lecturer_id = p.lecturer_id 
                GROUP BY l.lecturer_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
