<?php

namespace App;

use PDO;

class Student
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getStudentById($student_id)
    {
        $query = "SELECT * FROM students WHERE student_id = :student_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStudentByUserId($user_id)
    {
        $query = "SELECT * FROM students WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllStudents()
    {
        $query = "SELECT * FROM students ORDER BY student_id DESC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createStudent($user_id, $mssv, $class = null)
    {
        $query = "INSERT INTO students (user_id, mssv, class) VALUES (:user_id, :mssv, :class)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':mssv', $mssv, PDO::PARAM_STR);
        $stmt->bindValue(':class', $class, $class ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateStudent($student_id, $mssv, $class = null)
    {
        $query = "UPDATE students SET mssv = :mssv, class = :class WHERE student_id = :student_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':mssv', $mssv, PDO::PARAM_STR);
        $stmt->bindValue(':class', $class, $class ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteStudent($student_id)
    {
        $query = "DELETE FROM students WHERE student_id = :student_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
