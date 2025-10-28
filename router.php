<?php
// router.php (in root)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php'; // PDO connection
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoload
// Debug tạm thời (xóa sau khi test)
if (!class_exists('App\Controllers\HomeController')) {
    error_log('DEBUG: File HomeController tồn tại? ' . (file_exists(__DIR__ . '/Controllers/HomeController.php') ? 'CÓ' : 'KHÔNG'));
    error_log('DEBUG: Các namespace đã load: ' . implode(', ', array_filter(get_declared_classes(), function ($class) {
        return strpos($class, 'App\\') === 0;
    })));
} else {
    error_log('DEBUG: HomeController đã load thành công!');
}
// Use full namespaces for controllers
use App\Controllers\AccountController;
use App\Controllers\AuthController;
use App\Controllers\FeedbackController;
use App\Controllers\GroupController;
use App\Controllers\GroupMemberController;
use App\Controllers\HomeAdminController;
use App\Controllers\HomeController;
use App\Controllers\LecturerController;
use App\Controllers\ProjectController;
use App\Controllers\ReportController;
use App\Controllers\StudentController;
use App\Controllers\UserController;
// Remove 'App\User' if it's not a controller; use models separately if needed

session_start();

// Get URI (strip /quanlydoan prefix)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim(str_replace('/quanlydoan', '', $uri), '/');
$method = $_SERVER['REQUEST_METHOD'];

// Static routes (for assets if needed)
$staticRoutes = [];

// Allowed controllers map
$allowedControllers = [
    'AccountController' => AccountController::class,
    'AuthController' => AuthController::class,
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

// Handle route function
function handleRoute($uri, $method, $pdo, $staticRoutes, $allowedControllers)
{
    // Static routes check (unchanged)
    if (array_key_exists($uri, $staticRoutes)) {
        $route = $staticRoutes[$uri];
        if ($method === $route['method']) {
            ob_start();
            require $route['view'];
            $content = ob_get_clean();
            require __DIR__ . '/views/layouts/' . $route['layout'] . '.php';
            exit;
        } else {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
    }

    // Dynamic routing
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
                if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {  // Allow more methods if needed
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
            echo json_encode(['success' => false, 'message' => "Controller class $controllerClass not found. Check autoload."]);
            exit;
        }
    }

    // 404 fallback
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Page not found']);
    exit;
}

// Call handler
handleRoute($uri, $method, $pdo, $staticRoutes, $allowedControllers);
