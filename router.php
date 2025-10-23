<?php
// router.php (placed in root directory, outside config/)
// This is the main entry point for routing in the MVC structure.

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php'; // PDO connection from config/
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoload (if using Composer)

use App\AccountController;
use App\FeedbackController;
use App\GroupController;
use App\GroupMemberController;
use App\HomeAdminController;
use App\HomeController;
use App\LecturerController;
use App\ProjectController;
use App\ReportController;
use App\StudentController;
use App\User;
use App\UserController;

session_start();

// Lấy URI và loại bỏ prefix nếu có (giả sử root là /quanlydoan)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim(str_replace('/quanlydoan', '', $uri), '/'); // Adjust prefix as needed
$method = $_SERVER['REQUEST_METHOD'];

// Định nghĩa các tuyến đường tĩnh (nếu cần, ví dụ: assets, etc.)
$staticRoutes = [
    // Ví dụ: 'assets/css/style.css' => ['method' => 'GET', 'view' => __DIR__ . '/public/assets/css/style.css', 'layout' => 'none'],
];

// Định nghĩa các controller được phép (namespace App nếu dùng, nhưng giữ simple)
$allowedControllers = [
    'AccountController' => AccountController::class,
    'FeedbackController' => FeedbackController::class,
    'GroupController' => GroupController::class,
    'GroupMemberController' => GroupMemberController::class,
    'HomeAdminController' => HomeAdminController::class,
    'HomeController' => HomeController::class,
    'LecturerController' => LecturerController::class,
    'ProjectController' => ProjectController::class,
    'ReportController' => ReportController::class,
    'StudentController' => StudentController::class,
    'UserController' => UserController::class,
];

// Xử lý tuyến đường
function handleRoute($uri, $method, $pdo, $staticRoutes, $allowedControllers)
{
    // Kiểm tra tuyến đường tĩnh
    if (array_key_exists($uri, $staticRoutes)) {
        $route = $staticRoutes[$uri];
        if ($method === $route['method']) {
            $title = $route['title'];
            $layout = $route['layout'];
            ob_start();
            require $route['view'];
            $content = ob_get_clean();
            require __DIR__ . '/views/layouts/' . $layout;
            exit;
        } else {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
    }

    // Xử lý tuyến đường động (controller-based)
    $parts = explode('/', $uri);
    $controllerName = !empty($parts[0]) ? ucfirst($parts[0]) . 'Controller' : 'HomeController';
    $action = !empty($parts[1]) ? $parts[1] : 'index';
    $params = array_slice($parts, 2);

    error_log("Processing URI: $uri, Controller: $controllerName, Action: $action");

    if (array_key_exists($controllerName, $allowedControllers)) {
        $controllerClass = $allowedControllers[$controllerName];
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass($pdo);
            if (method_exists($controller, $action) && is_callable([$controller, $action])) {
                if (in_array($method, ['GET', 'POST'])) {
                    ob_start();
                    call_user_func_array([$controller, $action], $params);
                    $output = ob_get_clean();
                    if (!headers_sent() && !empty($output)) {
                        echo $output;
                    }
                    exit;
                } else {
                    http_response_code(405);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                    exit;
                }
            } else {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "Action '$action' not found in $controllerName"]);
                exit;
            }
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => "Controller class $controllerClass not found"]);
            exit;
        }
    }

    // Nếu không khớp, trả về 404
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Page not found']);
    exit;
}




// Gọi hàm xử lý tuyến đường
handleRoute($uri, $method, $pdo, $staticRoutes, $allowedControllers);
