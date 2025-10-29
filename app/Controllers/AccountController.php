<?php

namespace App\Controllers;

use PDO;
use App\Models\AccountModel;
use App\Models\UserModel;

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
        $limit = 10; // Số lượng tài khoản trên mỗi trang
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

            // --- Xử lý Upload Avatar (Tùy chọn) ---
            $avatarFileName = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatarFileName = $this->handleFileUpload($_FILES['avatar'], 'assets/images/');
            }

            // 1. Tạo bản ghi Account
            $accountData = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '', // Sẽ được hash trong Model
                'role' => $_POST['role'] ?? 'student',
                'avatar' => $avatarFileName // Lưu tên file avatar
            ];

            // Sửa tên hàm/đường dẫn redirect để phù hợp với manage
            $accountId = $this->accountModel->create($this->accountModel->prepareData($accountData));

            if ($accountId) {
                // 2. Tạo bản ghi User (Details)
                $userData = [
                    'account_id' => $accountId,
                    'full_name' => $_POST['full_name'] ?? '',
                    'date_of_birth' => $_POST['date_of_birth'] ?? null,
                    'phone_number' => $_POST['phone_number'] ?? null,
                    'address' => $_POST['address'] ?? null
                ];
                $this->userModel->create($userData);

                $_SESSION['message'] = 'Thêm người dùng thành công!';
                $_SESSION['message_type'] = 'success';
                $this->redirect('account/manage');
            } else {
                $_SESSION['message'] = 'Lỗi khi thêm tài khoản.';
                $_SESSION['message_type'] = 'danger';
                $this->redirect('account/manage');
            }
        }
        $this->redirect('account/manage');
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
                // Xóa ảnh cũ nếu nó không phải là 'profile.png' và upload ảnh mới
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
                'avatar' => $avatarFileName
            ];

            if (!empty($_POST['password'])) {
                $accountData['password'] = $_POST['password']; // Sẽ được hash trong Model
            }

            $isAccountUpdated = $this->accountModel->update($accountId, $this->accountModel->prepareData($accountData, $accountId));

            // 2. Cập nhật User (Details)
            $userData = [
                'full_name' => $_POST['full_name'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'phone_number' => $_POST['phone_number'] ?? null,
                'address' => $_POST['address'] ?? null
            ];

            // Cần phương thức update chi tiết người dùng dựa trên account_id
            $isUserUpdated = $this->userModel->updateByAccountId($accountId, $userData);

            if ($isAccountUpdated || $isUserUpdated) {
                $_SESSION['message'] = 'Cập nhật người dùng thành công!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Không có thay đổi nào được thực hiện hoặc có lỗi xảy ra.';
                $_SESSION['message_type'] = 'warning';
            }
            $this->redirect('account/manage');
        }
        $this->redirect('account/manage');
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
                if ($this->accountModel->update($accountId, $data)) {
                    $action = $newStatus === 'banned' ? 'khóa' : 'mở khóa';
                    $this->jsonResponse(['success' => true, 'message' => "Đã $action tài khoản thành công!"]);
                    return;
                }
            }
            $this->jsonResponse(['success' => false, 'message' => 'Không thể cập nhật trạng thái tài khoản.']);
        }
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
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? 'student'
            ];
            $data = $this->accountModel->prepareData($data);
            $accountId = $this->accountModel->create($data);
            if ($accountId) {
                // Create user record
                $userData = [
                    'account_id' => $accountId,
                    'full_name' => $_POST['full_name'] ?? '',
                    'date_of_birth' => $_POST['date_of_birth'] ?? null,
                    'phone_number' => $_POST['phone_number'] ?? null,
                    'address' => $_POST['address'] ?? null
                ];
                $this->userModel->create($userData);
                // CHÚ Ý: Đã thay đổi redirect từ 'accounts' thành 'account/manage' (hoặc 'accounts' nếu routing của bạn tự động xử lý)
                $this->redirect('account/manage');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to create account']);
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
