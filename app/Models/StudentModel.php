<?php
// models/StudentModel.php

namespace App\Models;

use PDO;

class StudentModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'students');
    }

    /**
     * Find student by user ID.
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
     * Get full student info with user.
     * @param int $studentId
     * @return array|false
     */
    public function getFullStudent(int $studentId): array|false
    {
        $sql = "SELECT s.*, u.full_name, u.avatar, u.date_of_birth, u.phone_number, u.address, a.username, a.email 
                FROM {$this->table} s 
                JOIN users u ON s.user_id = u.user_id 
                JOIN accounts a ON u.account_id = a.account_id 
                WHERE s.student_id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find students by class.
     * @param string $class
     * @return array
     */
    public function findByClass(string $class): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE class = :class");
        $stmt->execute(['class' => $class]);
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
}
