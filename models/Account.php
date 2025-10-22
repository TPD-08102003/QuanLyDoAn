<?php

namespace App;

use PDO;
use PDOException;

class Account
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAccountById($account_id)
    {
        $query = "SELECT * FROM accounts WHERE account_id = :account_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllAccounts()
    {
        $query = "SELECT * FROM accounts ORDER BY created_at DESC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAccount($username, $email, $password, $role = 'student', $status = 'active')
    {
        $query = "INSERT INTO accounts (username, email, password, role, status) VALUES (:username, :email, :password, :role, :status)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateAccount($account_id, $username, $email, $role, $status, $password = null)
    {
        $query = "UPDATE accounts SET username = :username, email = :email, role = :role, status = :status";
        if ($password) {
            $query .= ", password = :password";
        }
        $query .= " WHERE account_id = :account_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        if ($password) {
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        }
        return $stmt->execute();
    }

    public function deleteAccount($account_id)
    {
        $query = "DELETE FROM accounts WHERE account_id = :account_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAccountByUsernameOrEmail($username_or_email)
    {
        $query = "SELECT * FROM accounts WHERE username = :username_or_email OR email = :username_or_email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username_or_email', $username_or_email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($account_id, $password)
    {
        $query = "UPDATE accounts SET password = :password WHERE account_id = :account_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
