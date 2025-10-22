<?php

namespace App;

use PDO;

class Lecturer
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getLecturerById($lecturer_id)
    {
        $query = "SELECT * FROM lecturers WHERE lecturer_id = :lecturer_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':lecturer_id', $lecturer_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLecturerByUserId($user_id)
    {
        $query = "SELECT * FROM lecturers WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllLecturers()
    {
        $query = "SELECT * FROM lecturers ORDER BY lecturer_id DESC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createLecturer($user_id, $department = null)
    {
        $query = "INSERT INTO lecturers (user_id, department) VALUES (:user_id, :department)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':department', $department, $department ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateLecturer($lecturer_id, $department = null)
    {
        $query = "UPDATE lecturers SET department = :department WHERE lecturer_id = :lecturer_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':department', $department, $department ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':lecturer_id', $lecturer_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteLecturer($lecturer_id)
    {
        $query = "DELETE FROM lecturers WHERE lecturer_id = :lecturer_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':lecturer_id', $lecturer_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
