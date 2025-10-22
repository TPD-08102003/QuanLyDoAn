<?php

namespace App;

use PDO;

class GroupMember
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getMembersByGroupId($group_id)
    {
        $query = "SELECT * FROM group_members WHERE group_id = :group_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMember($group_id, $student_id)
    {
        $query = "INSERT INTO group_members (group_id, student_id) VALUES (:group_id, :student_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function removeMember($group_id, $student_id)
    {
        $query = "DELETE FROM group_members WHERE group_id = :group_id AND student_id = :student_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
