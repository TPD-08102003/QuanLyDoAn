<?php

namespace App;

use PDO;

class Feedback
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getFeedbackById($feedback_id)
    {
        $query = "SELECT * FROM feedback WHERE feedback_id = :feedback_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFeedbackByReportId($report_id)
    {
        $query = "SELECT * FROM feedback WHERE report_id = :report_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':report_id', $report_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createFeedback($report_id, $lecturer_id, $comment = null, $score = null)
    {
        $query = "INSERT INTO feedback (report_id, lecturer_id, comment, score) VALUES (:report_id, :lecturer_id, :comment, :score)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':report_id', $report_id, PDO::PARAM_INT);
        $stmt->bindParam(':lecturer_id', $lecturer_id, PDO::PARAM_INT);
        $stmt->bindValue(':comment', $comment, $comment ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':score', $score, $score ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateFeedback($feedback_id, $comment = null, $score = null)
    {
        $query = "UPDATE feedback SET comment = :comment, score = :score WHERE feedback_id = :feedback_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':comment', $comment, $comment ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':score', $score, $score ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteFeedback($feedback_id)
    {
        $query = "DELETE FROM feedback WHERE feedback_id = :feedback_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
