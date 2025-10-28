<?php
// models/GroupMemberModel.php

namespace App\Models;

use PDO;

class GroupMemberModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'group_members');
    }

    /**
     * Add member to group.
     * @param int $groupId
     * @param int $studentId
     * @return bool
     */
    public function addMember(int $groupId, int $studentId): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (group_id, student_id) VALUES (:group_id, :student_id)");
        return $stmt->execute(['group_id' => $groupId, 'student_id' => $studentId]);
    }

    /**
     * Remove member from group.
     * @param int $groupId
     * @param int $studentId
     * @return bool
     */
    public function removeMember(int $groupId, int $studentId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE group_id = :group_id AND student_id = :student_id");
        return $stmt->execute(['group_id' => $groupId, 'student_id' => $studentId]);
    }

    /**
     * Get members of a group.
     * @param int $groupId
     * @return array
     */
    public function getMembers(int $groupId): array
    {
        $sql = "SELECT gm.*, s.mssv, u.full_name 
                FROM {$this->table} gm 
                JOIN students s ON gm.student_id = s.student_id 
                JOIN users u ON s.user_id = u.user_id 
                WHERE gm.group_id = :group_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if student is in group.
     * @param int $groupId
     * @param int $studentId
     * @return bool
     */
    public function isMember(int $groupId, int $studentId): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->table} WHERE group_id = :group_id AND student_id = :student_id LIMIT 1");
        $stmt->execute(['group_id' => $groupId, 'student_id' => $studentId]);
        return $stmt->fetch() !== false;
    }
}
