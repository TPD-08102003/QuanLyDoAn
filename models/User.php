<?php

namespace App;

use PDO;

class User
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getUserById($user_id)
    {
        $query = "SELECT * FROM users WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByAccountId($account_id)
    {
        $query = "SELECT * FROM users WHERE account_id = :account_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers()
    {
        $query = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUser($account_id, $full_name, $avatar = 'profile.png', $date_of_birth = null, $phone_number = null, $address = null)
    {
        $query = "INSERT INTO users (account_id, full_name, avatar, date_of_birth, phone_number, address) VALUES (:account_id, :full_name, :avatar, :date_of_birth, :phone_number, :address)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
        $stmt->bindParam(':avatar', $avatar, PDO::PARAM_STR);
        $stmt->bindValue(':date_of_birth', $date_of_birth, $date_of_birth ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':phone_number', $phone_number, $phone_number ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':address', $address, $address ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function updateUser($user_id, $full_name, $avatar = 'profile.png', $date_of_birth = null, $phone_number = null, $address = null)
    {
        $query = "UPDATE users SET full_name = :full_name, avatar = :avatar, date_of_birth = :date_of_birth, phone_number = :phone_number, address = :address WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
        $stmt->bindParam(':avatar', $avatar, PDO::PARAM_STR);
        $stmt->bindValue(':date_of_birth', $date_of_birth, $date_of_birth ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':phone_number', $phone_number, $phone_number ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':address', $address, $address ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteUser($user_id)
    {
        $query = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
