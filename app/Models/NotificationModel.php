<?php
// models/NotificationModel.php

namespace App\Models;

use PDO;

class NotificationModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'notifications');
    }

    /**
     * Find notifications by user ID.
     * @param int $userId
     * @param bool $unreadOnly
     * @return array
     */
    public function findByUser(int $userId, bool $unreadOnly = false): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id" . ($unreadOnly ? " AND status = 'unread'" : "");
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create notification.
     * @param int $userId
     * @param string $title
     * @param string $message
     * @return int|false
     */
    public function createNotification(int $userId, string $title, string $message): int|false
    {
        $data = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message
        ];
        return $this->create($data);
    }

    /**
     * Mark as read.
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead(int $notificationId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET status = 'read' WHERE notification_id = :id");
        return $stmt->execute(['id' => $notificationId]);
    }

    /**
     * Mark all as read for user.
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET status = 'read' WHERE user_id = :user_id AND status = 'unread'");
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Get unread count for user.
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id AND status = 'unread'");
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
}
