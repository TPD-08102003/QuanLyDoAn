<?php

namespace App;

use PDO;

class PasswordResetToken
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getTokenById($token_id)
    {
        $query = "SELECT * FROM password_reset_tokens WHERE token_id = :token_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token_id', $token_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createToken($account_id, $token, $expires_at)
    {
        $query = "INSERT INTO password_reset_tokens (account_id, token, expires_at) VALUES (:account_id, :token, :expires_at)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->bindParam(':expires_at', $expires_at, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function deleteToken($token_id)
    {
        $query = "DELETE FROM password_reset_tokens WHERE token_id = :token_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':token_id', $token_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
