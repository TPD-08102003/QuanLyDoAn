<?php
// controllers/NotificationController.php
// Note: Not in router, but adding for completeness

namespace App\Controllers;

use PDO;
use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    private NotificationModel $notificationModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->notificationModel = new NotificationModel($pdo);
    }

    public function index(int $userId): void
    {
        $notifications = $this->notificationModel->findByUser($userId);
        $this->render('notifications/index', ['notifications' => $notifications]);
    }

    public function unread(int $userId): void
    {
        $notifications = $this->notificationModel->findByUser($userId, true);
        $this->render('notifications/unread', ['notifications' => $notifications]);
    }

    public function markRead(int $id): void
    {
        if ($this->notificationModel->markAsRead($id)) {
            $this->jsonResponse(['success' => true]);
        }
        $this->jsonResponse(['success' => false]);
    }

    public function markAllRead(int $userId): void
    {
        $this->notificationModel->markAllAsRead($userId);
        $this->redirect('notifications');
    }

    public function count(int $userId): void
    {
        $count = $this->notificationModel->getUnreadCount($userId);
        $this->jsonResponse(['count' => $count]);
    }
}
