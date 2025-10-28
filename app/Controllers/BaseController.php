<?php
// app/Controllers/BaseController.php

namespace App\Controllers;

use PDO;

abstract class BaseController
{
    protected PDO $pdo;

    // Ánh xạ vai trò với layout
    private array $layoutMap = [
        'admin' => 'admin_layout',
        'user' => 'user_layout',
        'default' => 'user_layout' // Layout mặc định nếu không xác định vai trò
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    protected function render(string $view, array $data = [], ?string $layout = null): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Chọn layout: Ưu tiên layout được truyền vào, nếu không thì dựa vào vai trò
        if ($layout === null) {
            $role = $_SESSION['role'] ?? 'default';
            $layout = $this->layoutMap[$role] ?? $this->layoutMap['default'];
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../views/' . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/' . $layout . '.php';
    }

    protected function redirect(string $url): void
    {
        header("Location: /quanlydoan" . $url);
        exit;
    }

    protected function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
