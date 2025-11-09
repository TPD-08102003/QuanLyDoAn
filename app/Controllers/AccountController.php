<?php

namespace App\Controllers;

use PDO;
use App\Models\AccountModel;
use App\Models\UserModel;
use Exception;

// class AccountController extends BaseController
// {
//     private AccountModel $accountModel;
//     private UserModel $userModel;

//     public function __construct(PDO $pdo)
//     {
//         parent::__construct($pdo);
//         $this->accountModel = new AccountModel($pdo);
//         $this->userModel = new UserModel($pdo);
//     }

//     /**
//      * Hiển thị trang quản lý tài khoản với tìm kiếm và phân trang.
//      */
//     public function manage(): void
//     {
//         // 1. Nhận tham số tìm kiếm và phân trang
//         $page = (int)($_GET['page'] ?? 1);
//         $keyword = trim($_GET['keyword'] ?? '');
//         $limit = 5; // Số lượng tài khoản trên mỗi trang
//         $offset = ($page - 1) * $limit;

//         // 2. Lấy dữ liệu
//         // Cần phương thức mới trong Model để lấy dữ liệu kết hợp và đếm tổng số
//         $result = $this->accountModel->getCombinedUsersWithPagination($limit, $offset, $keyword);

//         $users = $result['users'];
//         $totalUsers = $result['total'];
//         $totalPages = ceil($totalUsers / $limit);

//         $title = "Quản lý Tài khoản";

//         // 3. Render View
//         $this->render('accounts/manage', [
//             'title' => $title,
//             'users' => $users,
//             'keyword' => $keyword,
//             'page' => $page,
//             'totalPages' => $totalPages
//         ]);
//     }

//     /**
//      * Phương thức xử lý việc thêm người dùng (từ Modal trong manage.php).
//      */
//     public function addUser(): void
//     {
//         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//             // --- Xử lý upload avatar ---
//             $avatarFileName = null;
//             if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
//                 $avatarFileName = $this->handleFileUpload($_FILES['avatar'], 'assets/images/');
//             }

//             // --- Lấy loại người dùng ---
//             $userType = $_POST['user_type'] ?? 'student'; // giá trị nhận từ form
//             $role = ($userType === 'lecturer') ? 'lecturer' : 'student';

//             // --- Tạo tài khoản ---
//             $accountData = [
//                 'username' => $_POST['username'] ?? '',
//                 'email' => $_POST['email'] ?? '',
//                 'password' => $_POST['password'] ?? '',
//                 'role' => $role,
//             ];

//             // Kiểm tra username đã tồn tại chưa
//             if ($this->accountModel->isUsernameExists($accountData['username'])) {
//                 $_SESSION['message'] = 'Tên đăng nhập "' . htmlspecialchars($accountData['username']) . '" đã tồn tại!';
//                 $_SESSION['message_type'] = 'danger';
//                 $this->redirect('/quanlydoan/Account/manage');
//             }

//             try {
//                 $this->pdo->beginTransaction();

//                 // Lưu tài khoản
//                 $accountId = $this->accountModel->create($this->accountModel->prepareData($accountData));
//                 if (!$accountId) {
//                     throw new Exception('Không thể tạo tài khoản.');
//                 }

//                 // --- Tạo người dùng ---
//                 $userData = [
//                     'account_id' => $accountId,
//                     'full_name' => $_POST['full_name'] ?? '',
//                     'date_of_birth' => $_POST['date_of_birth'] ?? null,
//                     'phone_number' => $_POST['phone_number'] ?? null,
//                     'address' => $_POST['address'] ?? null,
//                     'avatar' => $avatarFileName
//                 ];

//                 $userId = $this->userModel->create($userData);
//                 if (!$userId) {
//                     throw new Exception('Không thể tạo thông tin người dùng.');
//                 }

//                 // --- Tạo thêm trong bảng student / lecturer ---
//                 if ($role === 'student') {
//                     // Sinh mã sinh viên tự động nếu không nhập
//                     $mssv = $_POST['mssv'] ?? ('B' . date('y') . str_pad($userId, 5, '0', STR_PAD_LEFT));
//                     $classId = (int)($_POST['class_id'] ?? 0);
//                     if ($classId <= 0) {
//                         throw new Exception('Vui lòng chọn lớp học hợp lệ cho sinh viên.');
//                     }
//                     if (!$this->userModel->createStudent($userId, $mssv, $classId)) {
//                         throw new Exception('Không thể tạo hồ sơ sinh viên.');
//                     }
//                 } elseif ($role === 'lecturer') {
//                     // Nếu không nhập, mặc định là CNTT
//                     $department = $_POST['department'] ?? 'Công nghệ thông tin';
//                     if (!$this->userModel->createLecturer($userId, $department)) {
//                         throw new Exception('Không thể tạo hồ sơ giảng viên.');
//                     }
//                 }

//                 $this->pdo->commit();

//                 $_SESSION['message'] = 'Thêm tài khoản ' . ($role === 'lecturer' ? 'giảng viên' : 'sinh viên') . ' thành công!';
//                 $_SESSION['message_type'] = 'success';
//             } catch (Exception $e) {
//                 $this->pdo->rollBack();
//                 if ($avatarFileName) {
//                     unlink('assets/images/' . $avatarFileName);
//                 }
//                 $_SESSION['message'] = 'Lỗi khi thêm tài khoản: ' . $e->getMessage();
//                 $_SESSION['message_type'] = 'danger';
//             }

//             $this->redirect('/quanlydoan/Account/manage');
//         }

//         // Nếu không phải POST → quay lại trang manage
//         $this->redirect('/quanlydoan/Account/manage');
//     }

//     /**
//      * Phương thức xử lý cập nhật người dùng (từ Modal trong manage.php).
//      */
//     public function updateUser(): void
//     {
//         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//             $accountId = (int)($_POST['account_id'] ?? 0);
//             if ($accountId === 0) {
//                 $this->jsonResponse(['success' => false, 'message' => 'ID tài khoản không hợp lệ.']);
//                 return;
//             }

//             // --- Xử lý Upload Avatar ---
//             $avatarFileName = $_POST['current_avatar'] ?? null;
//             if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
//                 if ($avatarFileName && $avatarFileName !== 'profile.png') {
//                     unlink('assets/images/' . $avatarFileName);
//                 }
//                 $avatarFileName = $this->handleFileUpload($_FILES['avatar'], 'assets/images/');
//             }

//             // 1. Cập nhật Account
//             $accountData = [
//                 'username' => $_POST['username'] ?? '',
//                 'email' => $_POST['email'] ?? '',
//                 'role' => $_POST['role'] ?? 'student',

//             ];

//             if (!empty($_POST['password'])) {
//                 $accountData['password'] = $_POST['password'];
//             }

//             try {
//                 $isAccountUpdated = $this->accountModel->update($accountId, $this->accountModel->prepareData($accountData, $accountId));
//             } catch (\PDOException $e) {
//                 $_SESSION['message'] = 'Lỗi khi cập nhật tài khoản: ' . $e->getMessage();
//                 $_SESSION['message_type'] = 'danger';
//                 $this->redirect('/quanlydoan/Account/manage');
//             }

//             // 2. Cập nhật User (Details)
//             $userData = [
//                 'full_name' => $_POST['full_name'] ?? '',
//                 'date_of_birth' => $_POST['date_of_birth'] ?? null,
//                 'phone_number' => $_POST['phone_number'] ?? null,
//                 'address' => $_POST['address'] ?? null,
//                 'avatar' => $avatarFileName
//             ];

//             $isUserUpdated = $this->userModel->updateByAccountId($accountId, $userData);

//             if ($isAccountUpdated || $isUserUpdated) {
//                 $_SESSION['message'] = 'Cập nhật người dùng thành công!';
//                 $_SESSION['message_type'] = 'success';
//             } else {
//                 $_SESSION['message'] = 'Không có thay đổi nào được thực hiện hoặc có lỗi xảy ra.';
//                 $_SESSION['message_type'] = 'warning';
//             }
//             $this->redirect('/quanlydoan/Account/manage');
//         }
//         $this->redirect('/quanlydoan/Account/manage');
//     }

//     /**
//      * Phương thức xử lý khóa/mở khóa tài khoản bằng AJAX.
//      */
//     public function lockUser(): void
//     {
//         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//             $accountId = (int)($_POST['account_id'] ?? 0);
//             $newStatus = $_POST['status'] ?? 'active';

//             if ($accountId > 0) {
//                 $data = ['status' => $newStatus];
//                 try {
//                     if ($this->accountModel->update($accountId, $data)) {
//                         $action = $newStatus === 'banned' ? 'khóa' : 'mở khóa';
//                         $this->jsonResponse(['success' => true, 'message' => "Đã $action tài khoản thành công!"]);
//                         return;
//                     }
//                     $this->jsonResponse(['success' => false, 'message' => 'Không thể cập nhật trạng thái tài khoản.']);
//                 } catch (\PDOException $e) {
//                     $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage()]);
//                     return;
//                 }
//             }
//             $this->jsonResponse(['success' => false, 'message' => 'ID tài khoản không hợp lệ.']);
//         }
//         // Trường hợp không phải POST
//         $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
//     }

//     /**
//      * Hàm tiện ích để xử lý upload file.
//      */
//     private function handleFileUpload(array $file, string $targetDir): ?string
//     {
//         // Đảm bảo thư mục đích tồn tại
//         if (!is_dir($targetDir)) {
//             mkdir($targetDir, 0777, true);
//         }

//         $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
//         $newFileName = uniqid('avatar_') . '.' . $extension;
//         $targetFile = $targetDir . $newFileName;

//         if (move_uploaded_file($file['tmp_name'], $targetFile)) {
//             return $newFileName;
//         }
//         return null; // Trả về null nếu upload thất bại
//     }

//     public function create(): void
//     {
//         $this->render('accounts/create');
//     }

//     public function store(): void
//     {
//         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//             // --- Xử lý upload avatar ---
//             $avatarFileName = null;
//             if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
//                 $avatarFileName = $this->handleFileUpload($_FILES['avatar'], 'assets/images/');
//             }

//             // --- Lấy loại người dùng ---
//             $userType = $_POST['user_type'] ?? 'student';
//             $role = ($userType === 'lecturer') ? 'lecturer' : 'student';

//             // --- Tạo tài khoản ---
//             $data = [
//                 'username' => $_POST['username'] ?? '',
//                 'email' => $_POST['email'] ?? '',
//                 'password' => $_POST['password'] ?? '',
//                 'role' => $role
//             ];

//             try {
//                 $this->pdo->beginTransaction();

//                 $preparedData = $this->accountModel->prepareData($data);
//                 $accountId = $this->accountModel->create($preparedData);
//                 if (!$accountId) {
//                     throw new Exception('Không thể tạo tài khoản.');
//                 }

//                 // --- Tạo người dùng ---
//                 $userData = [
//                     'account_id' => $accountId,
//                     'full_name' => $_POST['full_name'] ?? '',
//                     'date_of_birth' => $_POST['date_of_birth'] ?? null,
//                     'phone_number' => $_POST['phone_number'] ?? null,
//                     'address' => $_POST['address'] ?? null,
//                     'avatar' => $avatarFileName
//                 ];

//                 $userId = $this->userModel->create($userData); // ✅ trả về user_id
//                 if (!$userId) {
//                     throw new Exception('Không thể tạo thông tin người dùng.');
//                 }

//                 if ($role === 'student') {
//                     // sinh MSSV tự động hoặc lấy từ form
//                     $mssv = $_POST['mssv'] ?? ('B' . date('y') . str_pad($userId, 5, '0', STR_PAD_LEFT));
//                     $classId = (int)($_POST['class_id'] ?? 0);
//                     if ($classId <= 0) {
//                         throw new Exception('Vui lòng chọn lớp học hợp lệ cho sinh viên.');
//                     }
//                     if (!$this->userModel->createStudent($userId, $mssv, $classId)) {
//                         throw new Exception('Không thể tạo hồ sơ sinh viên.');
//                     }
//                 } elseif ($role === 'lecturer') { // ✅ Sửa 'teacher' thành 'lecturer' để đồng nhất
//                     $department = $_POST['department'] ?? 'Công nghệ thông tin';
//                     if (!$this->userModel->createLecturer($userId, $department)) {
//                         throw new Exception('Không thể tạo hồ sơ giảng viên.');
//                     }
//                 }

//                 $this->pdo->commit();

//                 // ✅ Trả JSON thành công
//                 $this->jsonResponse(['success' => true, 'message' => 'Tạo tài khoản thành công!']);
//                 return;
//             } catch (Exception $e) {
//                 $this->pdo->rollBack();
//                 if ($avatarFileName) {
//                     unlink('assets/images/' . $avatarFileName);
//                 }
//                 // --- Nếu thất bại ---
//                 $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
//                 return;
//             }
//         }

//         // --- Không phải POST ---
//         $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
//     }

//     public function show(int $id): void
//     {
//         $account = $this->accountModel->findById($id);
//         $user = $this->userModel->findByAccountId($id);
//         $this->render('accounts/show', ['account' => $account, 'user' => $user]);
//     }

//     public function edit(int $id): void
//     {
//         $account = $this->accountModel->findById($id);
//         $this->render('accounts/edit', ['account' => $account]);
//     }

//     public function update(int $id): void
//     {
//         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//             $data = [
//                 'username' => $_POST['username'] ?? '',
//                 'email' => $_POST['email'] ?? '',
//                 'role' => $_POST['role'] ?? 'student'
//             ];
//             if (!empty($_POST['password'])) {
//                 $data['password'] = $_POST['password'];
//             }
//             $data = $this->accountModel->prepareData($data);
//             if ($this->accountModel->update($id, $data)) {
//                 // CHÚ Ý: Đã thay đổi redirect từ 'accounts' thành 'account/manage'
//                 $this->redirect('account/manage');
//             }
//         }
//         $this->jsonResponse(['success' => false, 'message' => 'Failed to update account']);
//     }

//     public function destroy(int $id): void
//     {
//         if ($this->accountModel->delete($id)) {
//             // CHÚ Ý: Đã thay đổi redirect từ 'accounts' thành 'account/manage'
//             $this->redirect('account/manage');
//         }
//         $this->jsonResponse(['success' => false, 'message' => 'Failed to delete account']);
//     }

//     public function login(): void
//     {
//         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//             $usernameOrEmail = $_POST['username'] ?? '';
//             $password = $_POST['password'] ?? '';
//             $account = $this->accountModel->authenticate($usernameOrEmail, $password);
//             if ($account) {
//                 $_SESSION['user_id'] = $account['account_id'];
//                 $_SESSION['role'] = $account['role'];
//                 $this->redirect('home');
//             } else {
//                 $this->render('accounts/login', ['error' => 'Invalid credentials']);
//             }
//         } else {
//             $this->render('accounts/login');
//         }
//     }

//     public function logout(): void
//     {
//         session_destroy();
//         $this->redirect('login');
//     }
// }

class AccountController extends BaseController
{
    private AccountModel $accountModel;
    private UserModel $userModel;
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->accountModel = new AccountModel($pdo);
        $this->userModel = new UserModel($pdo);
    }
    /**
     * Hiển thị trang quản lý tài khoản với tìm kiếm và phân trang.
     */
    public function manage(): void
    {
        // 1. Nhận tham số tìm kiếm và phân trang
        $page = (int)($_GET['page'] ?? 1);
        $keyword = trim($_GET['keyword'] ?? '');
        $limit = 5; // Số lượng tài khoản trên mỗi trang
        $offset = ($page - 1) * $limit;
        // 2. Lấy dữ liệu
        // Cần phương thức mới trong Model để lấy dữ liệu kết hợp và đếm tổng số
        $result = $this->accountModel->getCombinedUsersWithPagination($limit, $offset, $keyword);
        $users = $result['users'];
        $totalUsers = $result['total'];
        $totalPages = ceil($totalUsers / $limit);
        $title = "Quản lý Tài khoản";
        // 3. Render View
        $this->render('accounts/manage', [
            'title' => $title,
            'users' => $users,
            'keyword' => $keyword,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }
    /**
     * Phương thức xử lý việc thêm người dùng (từ Modal trong manage.php).
     */
    public function addUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // --- Xử lý upload avatar ---
            $avatarFileName = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatarFileName = $this->handleFileUpload($_FILES['avatar'], 'assets/images/');
            }
            // --- Lấy loại người dùng ---
            $userType = $_POST['user_type'] ?? 'student'; // giá trị nhận từ form
            $role = ($userType === 'lecturer') ? 'lecturer' : 'student';
            // --- Tạo tài khoản ---
            $accountData = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => $role,
            ];
            // Kiểm tra username đã tồn tại chưa
            if ($this->accountModel->isUsernameExists($accountData['username'])) {
                $_SESSION['message'] = 'Tên đăng nhập "' . htmlspecialchars($accountData['username']) . '" đã tồn tại!';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/quanlydoan/Account/manage');
            }
            try {
                $this->pdo->beginTransaction();
                // Lưu tài khoản
                $accountId = $this->accountModel->create($this->accountModel->prepareData($accountData));
                if (!$accountId) {
                    throw new Exception('Không thể tạo tài khoản.');
                }
                // --- Tạo người dùng ---
                $userData = [
                    'account_id' => $accountId,
                    'full_name' => $_POST['full_name'] ?? '',
                    'date_of_birth' => $_POST['date_of_birth'] ?? null,
                    'phone_number' => $_POST['phone_number'] ?? null,
                    'address' => $_POST['address'] ?? null,
                    'avatar' => $avatarFileName
                ];
                $userId = $this->userModel->create($userData);
                if (!$userId) {
                    throw new Exception('Không thể tạo thông tin người dùng.');
                }
                // --- Tạo thêm trong bảng student / lecturer ---
                if ($role === 'student') {
                    // Sinh mã sinh viên tự động nếu không nhập
                    $mssv = $_POST['mssv'] ?? ('B' . date('y') . str_pad($userId, 5, '0', STR_PAD_LEFT));
                    $classId = (int)($_POST['class_id'] ?? 0);
                    if ($classId <= 0) {
                        throw new Exception('Vui lòng chọn lớp học hợp lệ cho sinh viên.');
                    }
                    if (!$this->userModel->createStudent($userId, $mssv, $classId)) {
                        throw new Exception('Không thể tạo hồ sơ sinh viên.');
                    }
                } elseif ($role === 'lecturer') {
                    // Nếu không nhập, mặc định là CNTT
                    $department = $_POST['department'] ?? 'Công nghệ thông tin';
                    if (!$this->userModel->createLecturer($userId, $department)) {
                        throw new Exception('Không thể tạo hồ sơ giảng viên.');
                    }
                }
                $this->pdo->commit();
                $_SESSION['message'] = 'Thêm tài khoản ' . ($role === 'lecturer' ? 'giảng viên' : 'sinh viên') . ' thành công!';
                $_SESSION['message_type'] = 'success';
            } catch (Exception $e) {
                $this->pdo->rollBack();
                if ($avatarFileName) {
                    unlink('assets/images/' . $avatarFileName);
                }
                $_SESSION['message'] = 'Lỗi khi thêm tài khoản: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
            $this->redirect('/quanlydoan/Account/manage');
        }
        // Nếu không phải POST → quay lại trang manage
        $this->redirect('/quanlydoan/Account/manage');
    }
    /**
     * Phương thức xử lý cập nhật người dùng (từ Modal trong manage.php).
     */
    public function updateUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accountId = (int)($_POST['account_id'] ?? 0);
            if ($accountId === 0) {
                $this->jsonResponse(['success' => false, 'message' => 'ID tài khoản không hợp lệ.']);
                return;
            }
            // --- Xử lý Upload Avatar ---
            $avatarFileName = $_POST['current_avatar'] ?? null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                if ($avatarFileName && $avatarFileName !== 'profile.png') {
                    unlink('assets/images/' . $avatarFileName);
                }
                $avatarFileName = $this->handleFileUpload($_FILES['avatar'], 'assets/images/');
            }
            // 1. Cập nhật Account
            $accountData = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? 'student',
            ];
            if (!empty($_POST['password'])) {
                $accountData['password'] = $_POST['password'];
            }
            try {
                $isAccountUpdated = $this->accountModel->update($accountId, $this->accountModel->prepareData($accountData, $accountId));
            } catch (\PDOException $e) {
                $_SESSION['message'] = 'Lỗi khi cập nhật tài khoản: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
                $this->redirect('/quanlydoan/Account/manage');
            }
            // 2. Cập nhật User (Details)
            $userData = [
                'full_name' => $_POST['full_name'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'phone_number' => $_POST['phone_number'] ?? null,
                'address' => $_POST['address'] ?? null,
                'avatar' => $avatarFileName
            ];
            $isUserUpdated = $this->userModel->updateByAccountId($accountId, $userData);
            if ($isAccountUpdated || $isUserUpdated) {
                $_SESSION['message'] = 'Cập nhật người dùng thành công!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Không có thay đổi nào được thực hiện hoặc có lỗi xảy ra.';
                $_SESSION['message_type'] = 'warning';
            }
            $this->redirect('/quanlydoan/Account/manage');
        }
        $this->redirect('/quanlydoan/Account/manage');
    }
    /**
     * Phương thức xử lý khóa/mở khóa tài khoản bằng AJAX.
     */
    public function lockUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $accountId = (int)($_POST['account_id'] ?? 0);
            $newStatus = $_POST['status'] ?? 'active';
            if ($accountId > 0) {
                $data = ['status' => $newStatus];
                try {
                    if ($this->accountModel->update($accountId, $data)) {
                        $action = $newStatus === 'banned' ? 'khóa' : 'mở khóa';
                        $this->jsonResponse(['success' => true, 'message' => "Đã $action tài khoản thành công!"]);
                        return;
                    }
                    $this->jsonResponse(['success' => false, 'message' => 'Không thể cập nhật trạng thái tài khoản.']);
                } catch (\PDOException $e) {
                    $this->jsonResponse(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage()]);
                    return;
                }
            }
            $this->jsonResponse(['success' => false, 'message' => 'ID tài khoản không hợp lệ.']);
        }
        // Trường hợp không phải POST
        $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
    }
    /**
     * Hàm tiện ích để xử lý upload file.
     */
    private function handleFileUpload(array $file, string $targetDir): ?string
    {
        // Đảm bảo thư mục đích tồn tại
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('avatar_') . '.' . $extension;
        $targetFile = $targetDir . $newFileName;
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $newFileName;
        }
        return null; // Trả về null nếu upload thất bại
    }
    public function create(): void
    {
        $this->render('accounts/create');
    }
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // --- Xử lý upload avatar ---
            $avatarFileName = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatarFileName = $this->handleFileUpload($_FILES['avatar'], 'assets/images/');
            }
            // --- Lấy loại người dùng ---
            $userType = $_POST['user_type'] ?? 'student';
            $role = ($userType === 'lecturer') ? 'lecturer' : 'student';
            // --- Tạo tài khoản ---
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => $role
            ];
            try {
                $this->pdo->beginTransaction();
                $preparedData = $this->accountModel->prepareData($data);
                $accountId = $this->accountModel->create($preparedData);
                if (!$accountId) {
                    throw new Exception('Không thể tạo tài khoản.');
                }
                // --- Tạo người dùng ---
                $userData = [
                    'account_id' => $accountId,
                    'full_name' => $_POST['full_name'] ?? '',
                    'date_of_birth' => $_POST['date_of_birth'] ?? null,
                    'phone_number' => $_POST['phone_number'] ?? null,
                    'address' => $_POST['address'] ?? null,
                    'avatar' => $avatarFileName
                ];
                $userId = $this->userModel->create($userData); // ✅ trả về user_id
                if (!$userId) {
                    throw new Exception('Không thể tạo thông tin người dùng.');
                }
                if ($role === 'student') {
                    // sinh MSSV tự động hoặc lấy từ form
                    $mssv = $_POST['mssv'] ?? ('B' . date('y') . str_pad($userId, 5, '0', STR_PAD_LEFT));
                    $classId = (int)($_POST['class_id'] ?? 0);
                    if ($classId <= 0) {
                        throw new Exception('Vui lòng chọn lớp học hợp lệ cho sinh viên.');
                    }
                    if (!$this->userModel->createStudent($userId, $mssv, $classId)) {
                        throw new Exception('Không thể tạo hồ sơ sinh viên.');
                    }
                } elseif ($role === 'lecturer') { // ✅ Sửa 'teacher' thành 'lecturer' để đồng nhất
                    $department = $_POST['department'] ?? 'Công nghệ thông tin';
                    if (!$this->userModel->createLecturer($userId, $department)) {
                        throw new Exception('Không thể tạo hồ sơ giảng viên.');
                    }
                }
                $this->pdo->commit();
                // ✅ Trả JSON thành công
                $this->jsonResponse(['success' => true, 'message' => 'Tạo tài khoản thành công!']);
                return;
            } catch (Exception $e) {
                $this->pdo->rollBack();
                if ($avatarFileName) {
                    unlink('assets/images/' . $avatarFileName);
                }
                // --- Nếu thất bại ---
                $this->jsonResponse(['success' => false, 'message' => $e->getMessage()]);
                return;
            }
        }
        // --- Không phải POST ---
        $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    }
    public function show(int $id): void
    {
        $account = $this->accountModel->findById($id);
        $user = $this->userModel->findByAccountId($id);
        $this->render('accounts/show', ['account' => $account, 'user' => $user]);
    }
    public function edit(int $id): void
    {
        $account = $this->accountModel->findById($id);
        $this->render('accounts/edit', ['account' => $account]);
    }
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? 'student'
            ];
            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }
            $data = $this->accountModel->prepareData($data);
            if ($this->accountModel->update($id, $data)) {
                // CHÚ Ý: Đã thay đổi redirect từ 'accounts' thành 'account/manage'
                $this->redirect('account/manage');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to update account']);
    }
    public function destroy(int $id): void
    {
        if ($this->accountModel->delete($id)) {
            // CHÚ Ý: Đã thay đổi redirect từ 'accounts' thành 'account/manage'
            $this->redirect('account/manage');
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to delete account']);
    }
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usernameOrEmail = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $account = $this->accountModel->authenticate($usernameOrEmail, $password);
            if ($account) {
                $_SESSION['user_id'] = $account['account_id'];
                $_SESSION['role'] = $account['role'];
                $this->redirect('home');
            } else {
                $this->render('accounts/login', ['error' => 'Invalid credentials']);
            }
        } else {
            $this->render('accounts/login');
        }
    }
    public function logout(): void
    {
        session_destroy();
        $this->redirect('login');
    }
}
