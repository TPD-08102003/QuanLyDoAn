<?php
// controllers/StudentController.php

namespace App\Controllers;

use PDO;
use Exception;
use App\Models\StudentModel;
use App\Models\UserModel;
use App\Models\AccountModel;
use App\Models\ClassesModel;
use App\Models\FacultiesModel;

class StudentController extends BaseController
{
    private StudentModel $studentModel;
    private UserModel $userModel;
    private AccountModel $accountModel;
    private ClassesModel $classesModel;
    private FacultiesModel $facultiesModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->studentModel = new StudentModel($pdo);

        // BỔ SUNG: Khởi tạo các Model cần thiết cho việc thêm sinh viên
        $this->userModel = new UserModel($pdo);
        $this->accountModel = new AccountModel($pdo);
        $this->classesModel = new ClassesModel($pdo);
        $this->facultiesModel = new FacultiesModel($pdo);
    }

    // public function manage(): void
    // {
    //     try {
    //         // 1. Nhận tham số tìm kiếm và phân trang
    //         $page = (int)($_GET['page'] ?? 1);
    //         $keyword = trim($_GET['keyword'] ?? '');
    //         $limit = 10; // Số sinh viên trên mỗi trang
    //         $offset = ($page - 1) * $limit;

    //         // 2. Lấy dữ liệu
    //         $result = $this->studentModel->getStudentsWithPagination($limit, $offset, $keyword);
    //         $students = $result['students'];


    //         $totalStudents = $result['total'];
    //         $totalPages = ceil($totalStudents / $limit);

    //         // 3. Render View
    //         $this->render('students/manage', [
    //             'students' => $students,
    //             'page' => $page,
    //             'totalPages' => $totalPages,
    //             'totalStudents' => $totalStudents,
    //             'keyword' => $keyword
    //         ]);

    //         // KHAI BÁO MODEL BẮT BUỘC:
    //         // $classesModel = new ClassesModel($this->pdo); 
    //         // Lấy danh sách lớp
    //         $classes = $this->classesModel->findAll(); // HOẶC $classesModel->findAll(); 

    //         // Lấy danh sách khoa (cho form tạo/sửa)
    //         // $faculties = $this->facultiesModel->getActiveFaculties(); 

    //         $this->render('students/manage', [ // Tên view là 'students/manage' hay 'manage'?
    //             // ... (Các biến sinh viên, phân trang) ...

    //             // BẮT BUỘC PHẢI CÓ DÒNG NÀY:
    //             'classes' => $classes,
    //             // 'faculties' => $faculties
    //         ]);
    //     } catch (Exception $e) {
    //         error_log("Error in StudentController::manage: " . $e->getMessage());
    //         // Hiển thị trang lỗi hoặc thông báo
    //         $this->render('students/manage', [
    //             'students' => [],
    //             'page' => 1,
    //             'totalPages' => 0,
    //             'totalStudents' => 0,
    //             'keyword' => '',
    //             'error' => 'Đã xảy ra lỗi khi tải dữ liệu sinh viên'
    //         ]);
    //     }
    // }

    public function manage(): void
    {
        try {
            // 1. Nhận tham số tìm kiếm và phân trang
            $page = (int)($_GET['page'] ?? 1);
            $keyword = trim($_GET['keyword'] ?? '');
            $limit = 10; // Số sinh viên trên mỗi trang
            $offset = ($page - 1) * $limit;

            // 2. Lấy dữ liệu
            $result = $this->studentModel->getStudentsWithPagination($limit, $offset, $keyword);
            $students = $result['students'];

            $totalStudents = $result['total'];
            $totalPages = ceil($totalStudents / $limit);

            // BỔ SUNG: Lấy danh sách lớp học (bao gồm tên Khoa) cho modal
            $classes = $this->classesModel->findAll(); // Phương thức này trả về class_name và faculty_name

            // 3. Render View
            $this->render('students/manage', [
                'students' => $students,
                'totalStudents' => $totalStudents,
                'totalPages' => $totalPages,
                'page' => $page,
                'keyword' => $keyword,
                'classes' => $classes, // **TRUYỀN DỮ LIỆU LỚP VÀO VIEW**
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'manage');
        }
    }

    /**
     * Xử lý việc thêm mới một sinh viên (qua AJAX).
     * Phương thức này sẽ tạo 1 Account, 1 User và 1 Student.
     * Trả về JSON Response.
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        try {
            // Lấy dữ liệu từ $_POST (hoặc $this->getPostData() nếu bạn có)
            $data = $_POST;
            $mssv = trim($data['mssv'] ?? '');
            $fullname = trim($data['full_name'] ?? '');
            $email = trim($data['email'] ?? '');
            $class_id = (int) ($data['class_id'] ?? 0);
            $default_password = '123';

            // 1. Validation cơ bản và Trùng lặp
            if (empty($mssv) || empty($fullname) || empty($email) || $class_id <= 0) {
                $this->jsonResponse(['success' => false, 'message' => 'Vui lòng điền đủ các trường bắt buộc.'], 400);
                return;
            }

            if ($this->studentModel->isStudentCodeExists($mssv)) {
                $this->jsonResponse(['success' => false, 'message' => "Mã số sinh viên **{$mssv}** đã tồn tại."], 409);
                return;
            }
            if ($this->accountModel->findByEmail($email)) { // Cần đảm bảo hàm này có trong AccountModel
                $this->jsonResponse(['success' => false, 'message' => "Email **{$email}** đã tồn tại."], 409);
                return;
            }

            $class = $this->classesModel->findById($class_id); // Giả sử Model có hàm findById
            if (!$class || empty($class['faculty_id'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Lớp học không hợp lệ hoặc thiếu thông tin Khoa.'], 400);
                return;
            }
            $facultyId = $class['faculty_id'];


            // BẮT ĐẦU TRANSACTION
            $this->pdo->beginTransaction();

            // 2. Tạo Account mới (Username = MSSV, Role = user)
            $accountId = $this->accountModel->create([
                'username' => $mssv,
                'email' => $email,
                'password' => password_hash($default_password, PASSWORD_DEFAULT),
                'role' => 'user',
                'status' => $data['status'] ?? 'active'
            ]);

            // 3. Tạo User mới
            $userId = $this->userModel->create([
                'account_id' => $accountId,
                'full_name' => $fullname,
                'gender' => $data['gender'] ?? 'Khác',
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            // 4. Tạo Student mới
            $studentId = $this->studentModel->create([
                'user_id' => $userId,
                'student_code' => $mssv,
                'class_id' => $class_id,
                // 'student_name' => $fullname,
                // 'email' => $email,
                'faculty_id' => $facultyId, // <<< BỔ SUNG DÒNG NÀY
                'academic_year' => $data['academic_year'] ?? '2021-2025'
            ]);

            if (!$accountId || !$userId || !$studentId) {
                throw new Exception("Lỗi hệ thống: Không thể tạo đầy đủ 3 bản ghi.");
            }

            // HOÀN TẤT TRANSACTION
            $this->pdo->commit();

            // Thành công, trả về JSON 201 Created
            $this->jsonResponse([
                'success' => true,
                'message' => "Thêm sinh viên **{$fullname}** thành công! (MSSV: {$mssv})",
            ], 201);
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log("StudentController::store PDO Error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi Database. Vui lòng kiểm tra Server Log.'], 500); // <<< Sẽ bắt lỗi DB ở đây!
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("StudentController::store Logic Error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    // Phương thức hỗ trợ để làm sạch dữ liệu
    private function sanitizeStudentInput(array $input): array
    {
        // Đây chỉ là một ví dụ, cần dùng filter_input hoặc Validation Library thực tế
        return [
            'mssv' => trim($input['mssv'] ?? ''),
            'full_name' => trim($input['full_name'] ?? ''),
            'email' => filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL),
            'gender' => trim($input['gender'] ?? ''),
            'class_id' => (int)($input['class_id'] ?? 0),
            'status' => trim($input['status'] ?? 'active'),
            'date_of_birth' => trim($input['date_of_birth'] ?? ''),
            'phone_number' => trim($input['phone_number'] ?? ''),
            'address' => trim($input['address'] ?? ''),
            'academic_year' => trim($input['academic_year'] ?? ''),
        ];
    }

    public function getStudentDetails(int $id): void
    {
        $student = $this->studentModel->getFullStudent($id);

        if ($student) {
            // Format lại dữ liệu giới tính
            $student['gender_display'] = $this->getGenderDisplay($student['gender'] ?? null);
            $student['gender_icon'] = $this->getGenderIcon($student['gender'] ?? null);

            $this->jsonResponse([
                'success' => true,
                'student' => $student
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Không tìm thấy thông tin sinh viên'
            ], 404);
        }
    }

    // Thêm phương thức helper để hiển thị giới tính
    private function getGenderDisplay(?string $gender): string
    {
        return match ($gender) {
            'male' => 'Nam',
            'female' => 'Nữ',
            'other' => 'Khác',
            default => 'Chưa cập nhật'
        };
    }

    private function getGenderIcon(?string $gender): string
    {
        return match ($gender) {
            'male' => 'bi-gender-male',
            'female' => 'bi-gender-female',
            'other' => 'bi-gender-ambiguous',
            default => 'bi-gender-trans'
        };
    }

    public function index(): void
    {
        $students = $this->studentModel->findAll();
        $this->render('students/index', ['students' => $students]);
    }

    /**
     * Hiển thị form thêm sinh viên.
     * Đã cập nhật để load danh sách Lớp và Khoa cho ô <select> trong View.
     */
    public function create(): void
    {
        // Lấy danh sách lớp và khoa để render ra form
        $classes = $this->classesModel->findAll();
        $faculties = $this->facultiesModel->getActiveFaculties();

        $this->render('students/create', [
            'classes' => $classes,
            'faculties' => $faculties
        ]);
    }


    /**
     * Xử lý việc lưu thông tin sinh viên mới.
     * Bao gồm tạo Account, User và Student.
     */
    // public function store(): void
    // {
    //     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //         $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ'], 405);
    //         return;
    //     }

    //     $input = $_POST;
    //     $errors = [];

    //     // 1. Dữ liệu đầu vào
    //     $full_name = trim($input['full_name'] ?? '');
    //     $email = trim($input['email'] ?? '');
    //     $mssv = trim($input['mssv'] ?? '');
    //     $class_id = (int)($input['class_id'] ?? 0);
    //     $gender = $input['gender'] ?? '';
    //     $defaultPassword = '123'; // Mật khẩu mặc định

    //     // 2. Validation (Thêm kiểm tra bắt buộc và trùng lặp)
    //     if (empty($full_name)) $errors['full_name'] = 'Họ và tên không được để trống.';
    //     if (empty($mssv)) $errors['mssv'] = 'Mã số sinh viên không được để trống.';
    //     if ($class_id <= 0) $errors['class_id'] = 'Vui lòng chọn lớp học.';
    //     if (empty($gender)) $errors['gender'] = 'Vui lòng chọn giới tính.';
    //     if (empty($email)) $errors['email'] = 'Email không được để trống.';
    //     else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Địa chỉ email không hợp lệ.';
    //     else if ($this->accountModel->findByEmail($email)) $errors['email'] = 'Email đã tồn tại trong hệ thống.';

    //     // *Lưu ý: Bạn cần thêm phương thức findByMssv(string $mssv) vào StudentModel*
    //     if (!empty($mssv) && $this->studentModel->findByMssv($mssv)) {
    //         $errors['mssv'] = 'Mã số sinh viên đã tồn tại.';
    //     }

    //     if (!empty($errors)) {
    //         $this->jsonResponse(['success' => false, 'message' => 'Lỗi kiểm tra dữ liệu.', 'errors' => $errors], 422);
    //         return;
    //     }

    //     // 3. Xử lý Transaction để đảm bảo tính toàn vẹn dữ liệu
    //     $this->pdo->beginTransaction();

    //     try {
    //         // A. Tìm Class và Faculty ID
    //         $class = $this->classesModel->findAll($class_id);
    //         if (!$class) {
    //             $this->pdo->rollBack();
    //             $this->jsonResponse(['success' => false, 'message' => 'Lớp học không tồn tại.'], 404);
    //             return;
    //         }
    //         $facultyId = $class['faculty_id'] ?? 0;

    //         // B. Tạo Account (Bảng accounts)
    //         $accountData = [
    //             'email' => $email,
    //             'username' => $mssv, // Dùng MSSV làm username mặc định
    //             'password' => password_hash($defaultPassword, PASSWORD_DEFAULT),
    //             'role' => 'student',
    //             'status' => 'active'
    //         ];
    //         $accountId = $this->accountModel->create($accountData);

    //         if (!$accountId) {
    //             throw new Exception("Không thể tạo tài khoản.");
    //         }

    //         // C. Tạo User (Bảng users)
    //         $userData = [
    //             'account_id' => $accountId,
    //             'full_name' => $full_name,
    //             'gender' => $gender,
    //             'date_of_birth' => $input['date_of_birth'] ?: null,
    //             'phone_number' => $input['phone_number'] ?: null,
    //             'address' => $input['address'] ?: null,
    //             'email' => $email
    //         ];
    //         $userId = $this->userModel->create($userData);

    //         if (!$userId) {
    //             throw new Exception("Không thể tạo hồ sơ người dùng.");
    //         }

    //         // D. Tạo Student (Bảng students)
    //         $studentData = [
    //             'user_id' => $userId,
    //             'mssv' => $mssv,
    //             'class_id' => $class_id, // Lớp đã được chọn
    //             'faculty_id' => $facultyId, // Khoa được tự động lấy từ Lớp
    //             'academic_year' => $input['academic_year'] ?? 'N/A'
    //         ];

    //         $studentId = $this->studentModel->create($studentData);

    //         if (!$studentId) {
    //             throw new Exception("Không thể tạo thông tin sinh viên.");
    //         }

    //         // 4. Commit và Phản hồi
    //         $this->pdo->commit();
    //         $this->jsonResponse(['success' => true, 'message' => 'Thêm sinh viên thành công!', 'student_id' => $studentId]);
    //     } catch (Exception $e) {
    //         $this->pdo->rollBack();
    //         error_log("StudentController::store Error: " . $e->getMessage());
    //         $this->jsonResponse(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
    //     }
    // }


    public function show(int $id): void
    {
        $student = $this->studentModel->getFullStudent($id);
        $this->render('students/show', ['student' => $student]);
    }

    public function edit(int $id): void
    {
        $student = $this->studentModel->getFullStudent($id);
        $this->render('students/edit', ['student' => $student]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'full_name' => $_POST['full_name'] ?? '',
                'gender' => $_POST['gender'] ?? null,
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'phone_number' => $_POST['phone_number'] ?? null,
                'address' => $_POST['address'] ?? null
            ];

            $student = $this->studentModel->getFullStudent($id);
            $userId = $student['user_id'] ?? null;

            if ($userId) {
                $userModel = new UserModel($this->pdo);
                $updated = $userModel->update($userId, $userData);
                if ($updated) {
                    $this->jsonResponse(['success' => true]);
                }
            }

            $this->jsonResponse(['success' => false, 'message' => 'Cập nhật thất bại']);
        }
    }


    public function destroy(int $id): void
    {
        if ($this->studentModel->delete($id)) {
            $this->redirect('students');
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to delete student']);
    }

    public function available(): void
    {
        $students = $this->studentModel->findAvailableStudents();
        $this->render('students/available', ['students' => $students]);
    }

    // Thêm các phương thức mới
    public function export(): void
    {
        // Logic export Excel
        $students = $this->studentModel->findAll();
        // Implement export logic here
    }

    public function downloadTemplate(): void
    {
        // Logic download template Excel
        // Implement template download logic here
    }
}
