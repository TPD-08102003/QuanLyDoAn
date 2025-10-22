<?php

namespace App;

use PDO;

class Group
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getGroupById($group_id)
    {
        $query = "SELECT * FROM `groups` WHERE group_id = :group_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllGroups()
    {
        $query = "SELECT * FROM `groups` ORDER BY group_id DESC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createGroup($project_id, $leader_id)
    {
        $query = "INSERT INTO `groups` (project_id, leader_id) VALUES (:project_id, :leader_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->bindParam(':leader_id', $leader_id, PDO::PARAM_INT);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateGroup($group_id, $project_id, $leader_id)
    {
        $query = "UPDATE `groups` SET project_id = :project_id, leader_id = :leader_id WHERE group_id = :group_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->bindParam(':leader_id', $leader_id, PDO::PARAM_INT);
        $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteGroup($group_id)
    {
        $query = "DELETE FROM `groups` WHERE group_id = :group_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
