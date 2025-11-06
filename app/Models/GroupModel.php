<?php
// models/GroupModel.php

namespace App\Models;

use PDO;

class GroupModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'groups');
    }

    /**
     * Get full group info with project and leader.
     * @param int $groupId
     * @return array|false
     */
    public function getFullGroup(int $groupId): array|false
    {
        $sql = "SELECT g.*, p.title as project_title, s.mssv as leader_mssv, u.full_name as leader_name 
                FROM {$this->table} g 
                JOIN projects p ON g.project_id = p.project_id 
                JOIN students s ON g.leader_id = s.student_id 
                JOIN users u ON s.user_id = u.user_id 
                WHERE g.group_id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $groupId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find groups by project ID.
     * @param int $projectId
     * @return array
     */
    public function findByProject(int $projectId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE project_id = :project_id");
        $stmt->execute(['project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find groups by leader ID.
     * @param int $leaderId
     * @return array
     */
    public function findByLeader(int $leaderId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE leader_id = :leader_id");
        $stmt->execute(['leader_id' => $leaderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get group member count.
     * @param int $groupId
     * @return int
     */
    public function getMemberCount(int $groupId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM group_members WHERE group_id = :group_id");
        $stmt->execute(['group_id' => $groupId]);
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
}
