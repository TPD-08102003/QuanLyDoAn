<?php
// models/AccountModel.php

namespace App\Models;

use PDO;

class AccountModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'accounts');
    }

    /**
     * Find account by username.
     * @param string $username
     * @return array|false
     */
    public function findByUsername(string $username): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find account by email.
     * @param string $email
     * @return array|false
     */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Authenticate user (verify password).
     * @param string $usernameOrEmail
     * @param string $password
     * @return array|false
     */
    public function authenticate(string $usernameOrEmail, string $password): array|false
    {
        $account = strpos($usernameOrEmail, '@') ? $this->findByEmail($usernameOrEmail) : $this->findByUsername($usernameOrEmail);
        if ($account && password_verify($password, $account['password'])) {
            return $account;
        }
        return false;
    }

    /**
     * Hash password before create/update.
     * @param array $data
     * @return array
     */
    public function prepareData(array $data): array
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Find accounts by role.
     * @param string $role
     * @return array
     */
    public function findByRole(string $role): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE role = :role");
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update status by ID.
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET status = :status WHERE account_id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function getCombinedUsersWithPagination(int $limit, int $offset, string $keyword = ''): array
    {
        $params = [];
        $where = '1=1';

        // 1. Xử lý Tìm kiếm
        if (!empty($keyword)) {
            $where = " (a.username LIKE :keyword1 OR a.email LIKE :keyword2 OR u.full_name LIKE :keyword3) ";
            $search = "%" . $keyword . "%";
            $params[':keyword1'] = $search;
            $params[':keyword2'] = $search;
            $params[':keyword3'] = $search;
        }

        // 2. Query lấy tổng số bản ghi
        $countSql = "SELECT COUNT(a.account_id) FROM {$this->table} a
                     LEFT JOIN users u ON a.account_id = u.account_id
                     WHERE {$where}";

        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // 3. Query lấy dữ liệu (có phân trang)
        $sql = "SELECT a.*, u.full_name, u.date_of_birth, u.phone_number, u.address 
                FROM {$this->table} a
                LEFT JOIN users u ON a.account_id = u.account_id
                WHERE {$where}
                ORDER BY a.account_id DESC
                LIMIT :limit OFFSET :offset";

        // Thêm tham số phân trang
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->pdo->prepare($sql);

        // Bind các tham số (cần bindParam cho limit/offset vì nó là giá trị số nguyên)
        foreach ($params as $key => &$val) {
            if (strpos($key, 'keyword') !== false) {
                $stmt->bindParam($key, $val, PDO::PARAM_STR);
            }
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'users' => $users,
            'total' => (int)$total
        ];
    }
}
