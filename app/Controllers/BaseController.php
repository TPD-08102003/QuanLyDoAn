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

    // protected function jsonResponse(array $data, int $status = 200): void
    // {
    //     http_response_code($status);
    //     header('Content-Type: application/json');
    //     echo json_encode($data);
    //     exit;
    // }

    /**
     * Trả về phản hồi JSON an toàn và thoát (exit) để ngăn lỗi HTML/PHP notices.
     *
     * @param array $data Dữ liệu sẽ được mã hóa thành JSON.
     * @param int $status Mã trạng thái HTTP (ví dụ: 200, 201, 400, 500).
     * @return void
     */
    protected function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);

        // Đảm bảo header Content-Type là application/json
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        // Làm sạch bộ đệm đầu ra để loại bỏ bất kỳ PHP notice/warning hoặc HTML thừa nào
        if (ob_get_length() > 0) {
            ob_end_clean();
        }

        // Xuất JSON
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Xử lý lỗi Exception (Ghi log và chuyển hướng người dùng).
     *
     * @param \Exception $e Đối tượng Exception đã bị bắt.
     * @param string $action Hành động xảy ra lỗi (ví dụ: 'manage', 'store').
     * @param string $redirectUrl URL để chuyển hướng sau khi xảy ra lỗi.
     * @return void
     */
    protected function handleError(\Exception $e, string $action = 'general_action', string $redirectUrl = ''): void
    {
        // 1. Ghi log lỗi vào server log (RẤT QUAN TRỌNG CHO DEBUG)
        $controllerName = (new \ReflectionClass($this))->getShortName();
        error_log("!!! [FATAL ERROR] Controller: {$controllerName}, Action: {$action}, Message: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");

        // 2. Thiết lập thông báo lỗi trong session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Thông báo lỗi thân thiện với người dùng, không tiết lộ chi tiết kỹ thuật
        $_SESSION['message'] = "Đã xảy ra lỗi hệ thống trong quá trình xử lý. Vui lòng thử lại.";
        $_SESSION['message_type'] = 'danger'; // Dùng cho alert-danger của Bootstrap

        // 3. Chuyển hướng người dùng về trang đã chỉ định
        $url = $redirectUrl ?: '/'; // Mặc định về trang chủ nếu không có URL nào được cung cấp
        $this->redirect($url);
    }


    /**
     * Xử lý lỗi Exception (Ghi log và chuyển hướng người dùng).
     *
     * @param \Exception $e Đối tượng Exception đã bị bắt.
     * @param string $action Hành động xảy ra lỗi (ví dụ: 'manage', 'store').
     * @param string $redirectUrl URL để chuyển hướng sau khi xảy ra lỗi.
     * @return void
     */
    // protected function handleError(\Exception $e, string $action = 'general_action', string $redirectUrl = ''): void
    // {
    //     // 1. Ghi log lỗi vào server log (RẤT QUAN TRỌNG CHO DEBUG)
    //     $controllerName = (new \ReflectionClass($this))->getShortName();
    //     error_log("!!! [FATAL ERROR] Controller: {$controllerName}, Action: {$action}, Message: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");

    //     // 2. Thiết lập thông báo lỗi trong session
    //     if (session_status() === PHP_SESSION_NONE) {
    //         session_start();
    //     }

    //     // Thông báo lỗi thân thiện với người dùng, không tiết lộ chi tiết kỹ thuật
    //     $_SESSION['message'] = "Đã xảy ra lỗi hệ thống trong quá trình xử lý. Vui lòng thử lại.";
    //     $_SESSION['message_type'] = 'danger'; // Dùng cho alert-danger của Bootstrap

    //     // Nếu muốn hiển thị thông báo chi tiết hơn cho Admin:
    //     // $_SESSION['error'] = "Lỗi: " . $e->getMessage(); 

    //     // 3. Chuyển hướng người dùng về trang đã chỉ định
    //     $url = $redirectUrl ?: '/'; // Mặc định về trang chủ nếu không có URL nào được cung cấp
    //     $this->redirect($url);
    // }
}
