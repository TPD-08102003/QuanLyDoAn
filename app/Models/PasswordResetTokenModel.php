<?php
// models/PasswordResetTokenModel.php

namespace App\Models;

use PDO;

class PasswordResetTokenModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'password_reset_tokens');
    }

    /**
     * Create reset token.
     * @param int $accountId
     * @param string $token
     * @param int $expiresInMinutes
     * @return int|false
     */
    public function createToken(int $accountId, string $token, int $expiresInMinutes = 60): int|false
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiresInMinutes minutes"));
        $data = [
            'account_id' => $accountId,
            'token' => $token,
            'expires_at' => $expiresAt
        ];
        return $this->create($data);
    }

    /**
     * Find token by token string.
     * @param string $token
     * @return array|false
     */
    public function findByToken(string $token): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE token = :token AND expires_at > NOW() LIMIT 1");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Validate and consume token.
     * @param string $token
     * @return int|false Account ID or false
     */
    public function validateToken(string $token): int|false
    {
        $tokenData = $this->findByToken($token);
        if ($tokenData) {
            // Delete token after use
            $this->delete((int) $tokenData['token_id']);
            return (int) $tokenData['account_id'];
        }
        return false;
    }

    /**
     * Delete expired tokens.
     * @return bool
     */
    public function cleanupExpired(): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE expires_at < NOW()");
        return $stmt->execute();
    }
}
