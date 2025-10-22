<?php

namespace App;

use PDO;

class Project
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getProjectById($project_id)
    {
        $query = "SELECT * FROM projects WHERE project_id = :project_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllProjects()
    {
        $query = "SELECT * FROM projects ORDER BY created_at DESC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createProject($title, $description, $lecturer_id, $status = 'DangThucHien')
    {
        $query = "INSERT INTO projects (title, description, lecturer_id, status) VALUES (:title, :description, :lecturer_id, :status)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':lecturer_id', $lecturer_id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateProject($project_id, $title, $description, $lecturer_id, $status)
    {
        $query = "UPDATE projects SET title = :title, description = :description, lecturer_id = :lecturer_id, status = :status WHERE project_id = :project_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':lecturer_id', $lecturer_id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteProject($project_id)
    {
        $query = "DELETE FROM projects WHERE project_id = :project_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
