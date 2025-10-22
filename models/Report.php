<?php

namespace App;

use PDO;

class Report
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getReportById($report_id)
    {
        $query = "SELECT * FROM reports WHERE report_id = :report_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':report_id', $report_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getReportsByGroupId($group_id)
    {
        $query = "SELECT * FROM reports WHERE group_id = :group_id ORDER BY submitted_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createReport($group_id, $file_path = null, $code_link = null)
    {
        $query = "INSERT INTO reports (group_id, file_path, code_link) VALUES (:group_id, :file_path, :code_link)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
        $stmt->bindValue(':file_path', $file_path, $file_path ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':code_link', $code_link, $code_link ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateReport($report_id, $file_path = null, $code_link = null)
    {
        $query = "UPDATE reports SET file_path = :file_path, code_link = :code_link WHERE report_id = :report_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':file_path', $file_path, $file_path ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':code_link', $code_link, $code_link ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':report_id', $report_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteReport($report_id)
    {
        $query = "DELETE FROM reports WHERE report_id = :report_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':report_id', $report_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
