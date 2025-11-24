<?php

namespace App\Models;

use PDO;

class GroupMessageModel extends BaseModel
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'group_messages', 'message_id');
    }

    public function getMessagesByGroupId($groupId)
    {
        $sql = "SELECT m.message_id, m.group_id, m.user_id, m.message, m.created_at, m.is_recalled, 
                       u.full_name, u.avatar 
                FROM group_messages m 
                JOIN users u ON m.user_id = u.user_id 
                WHERE m.group_id = :group_id 
                ORDER BY m.created_at ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveMessage($groupId, $userId, $message)
    {
        return $this->create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'message' => $message
        ]);
    }
    // Hàm thu hồi tin nhắn
    public function recall($messageId, $userId)
    {
        // Chỉ cho phép thu hồi nếu đúng người gửi (user_id khớp)
        $sql = "UPDATE group_messages SET is_recalled = 1 WHERE message_id = :msg_id AND user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['msg_id' => $messageId, 'user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }
}
