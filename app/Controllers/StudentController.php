<?php
// controllers/StudentController.php

namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use PDO;
use Exception;
use PDOException;
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

    // Thêm vào StudentController
    public function getAllStudents(): void
    {
        try {
            $students = $this->studentModel->getAllStudentsWithRole();

            $this->render('students/manage', [
                'students' => $students,
                'totalStudents' => count($students),
                'totalPages' => 1,
                'page' => 1,
                'keyword' => '',
                'classes' => $this->classesModel->findAll(),
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'getAllStudents');
        }
    }

    // Hoặc sửa phương thức manage hiện tại để hiển thị tất cả sinh viên có role student
    public function manage(): void
    {
        try {
            // 1. Nhận tham số tìm kiếm và phân trang
            $page = (int)($_GET['page'] ?? 1);
            $keyword = trim($_GET['keyword'] ?? '');
            $limit = 10;
            $offset = ($page - 1) * $limit;

            // 2. Lấy dữ liệu - Sử dụng phương thức mới
            if (empty($keyword)) {
                // Nếu không có từ khóa tìm kiếm, lấy tất cả sinh viên có role student
                $allStudents = $this->studentModel->getAllStudentsWithRole();
                $totalStudents = count($allStudents);

                // Phân trang thủ công
                $students = array_slice($allStudents, $offset, $limit);
                $totalPages = ceil($totalStudents / $limit);
            } else {
                // Nếu có từ khóa, sử dụng phương thức tìm kiếm cũ
                $result = $this->studentModel->getStudentsWithPagination($limit, $offset, $keyword);
                $students = $result['students'];
                $totalStudents = $result['total'];
                $totalPages = ceil($totalStudents / $limit);
            }

            // 3. Lấy danh sách lớp học
            $classes = $this->classesModel->findAll();

            // 4. Render View
            $this->render('students/manage', [
                'students' => $students,
                'totalStudents' => $totalStudents,
                'totalPages' => $totalPages,
                'page' => $page,
                'keyword' => $keyword,
                'classes' => $classes,
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'manage');
        }
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        try {
            // Lấy dữ liệu từ $_POST
            $data = $_POST;
            $mssv = trim($data['mssv'] ?? '');
            $username = trim($data['username'] ?? '');  // THÊM MỚI: Lấy username từ POST
            $fullname = trim($data['full_name'] ?? '');
            $email = trim($data['email'] ?? '');
            $class_id = (int) ($data['class_id'] ?? 0);
            $default_password = '123';

            // 1. Validation cơ bản và Trùng lặp
            if (empty($mssv) || empty($fullname) || empty($email) || $class_id <= 0) {
                $this->jsonResponse(['success' => false, 'message' => 'Vui lòng điền đủ các trường bắt buộc.'], 400);
                return;
            }
            if (empty($username)) {
                $username = $mssv;  // Fallback to MSSV nếu username rỗng
            }

            // Kiểm tra trùng MSSV
            if ($this->studentModel->isStudentCodeExists($mssv)) {
                $this->jsonResponse(['success' => false, 'message' => "Mã số sinh viên **{$mssv}** đã tồn tại."], 409);
                return;
            }

            // THÊM MỚI: Kiểm tra trùng username (giả sử AccountModel có findByUsername)
            if ($this->accountModel->findByUsername($username)) {
                $this->jsonResponse(['success' => false, 'message' => "Username **{$username}** đã tồn tại."], 409);
                return;
            }

            // Kiểm tra trùng email
            if ($this->accountModel->findByEmail($email)) {
                $this->jsonResponse(['success' => false, 'message' => "Email **{$email}** đã tồn tại."], 409);
                return;
            }

            // Lấy thông tin lớp và khoa
            $class = $this->classesModel->findById($class_id);
            if (!$class) {
                $this->jsonResponse(['success' => false, 'message' => 'Lớp học không hợp lệ.'], 400);
                return;
            }

            $facultyId = $class['faculty_id'] ?? 0;
            if ($facultyId <= 0) {
                $this->jsonResponse(['success' => false, 'message' => 'Lớp học không có thông tin Khoa.'], 400);
                return;
            }

            // BẮT ĐẦU TRANSACTION
            $this->pdo->beginTransaction();

            // 2. Tạo Account mới (SỬA: Username từ biến $username)
            $accountId = $this->accountModel->create([
                'username' => $username,  // SỬA Ở ĐÂY
                'email' => $email,
                'password' => password_hash($default_password, PASSWORD_DEFAULT),
                'role' => 'student',
                'status' => $data['status'] ?? 'active'
            ]);

            if (!$accountId) {
                throw new Exception("Không thể tạo tài khoản.");
            }

            // 3. Tạo User mới
            $userData = [
                'account_id' => $accountId,
                'full_name' => $fullname,
                'gender' => $data['gender'] ?? 'Khác',
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'address' => $data['address'] ?? null,
            ];

            $userId = $this->userModel->create($userData);
            if (!$userId) {
                throw new Exception("Không thể tạo thông tin người dùng.");
            }

            // 4. Tạo Student mới - SỬA LẠI Ở ĐÂY
            $studentData = [
                'user_id' => $userId,
                'mssv' => $mssv, // ĐÃ SỬA TỪ 'student_code' THÀNH 'mssv'
                'class_id' => $class_id,
                'faculty_id' => $facultyId,
                'academic_year' => $data['academic_year'] ?? '2021-2025'
            ];

            $studentId = $this->studentModel->create($studentData);
            if (!$studentId) {
                throw new Exception("Không thể tạo thông tin sinh viên. Kiểm tra lại cấu trúc bảng students.");
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
            $this->jsonResponse([
                'success' => false,
                'message' => 'Lỗi Database: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("StudentController::store Logic Error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
        if (empty($mssv)) {
            $this->jsonResponse(['success' => false, 'message' => 'MSSV không được để trống.'], 400);
            return;
        }
        if ($class_id <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng chọn lớp học.'], 400);
            return;
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
            $student['gender_display'] = $this->getGenderDisplay($student['gender'] ?? null);
            $student['gender_icon'] = $this->getGenderIcon($student['gender'] ?? null);
            $this->jsonResponse([
                'success' => true,
                'student' => $student
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Không tìm thấy sinh viên. Có thể chưa có MSSV hoặc lớp/khoa.'
            ], 404);
        }
    }
    // Thêm phương thức helper để hiển thị giới tính
    private function getGenderDisplay(?string $gender): string
    {
        return match ($gender) {
            'Nam' => 'Nam',
            'Nữ' => 'Nữ',
            'Khác' => 'Khác',
            default => 'Chưa cập nhật'
        };
    }
    private function getGenderIcon(?string $gender): string
    {
        return match ($gender) {
            'Nam' => 'bi-gender-male',
            'Nữ' => 'bi-gender-female',
            'Khác' => 'bi-gender-ambiguous',
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
            $student = $this->studentModel->getFullStudent($id);
            if (!$student) {
                $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy sinh viên'], 404);
                return;
            }
            $userId = $student['user_id'] ?? null;
            if (!$userId) {
                $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng'], 404);
                return;
            }
            // Chỉ cập nhật các trường có trong POST và không rỗng
            $userData = [];
            if (isset($_POST['full_name']) && !empty($_POST['full_name'])) $userData['full_name'] = $_POST['full_name'];
            if (isset($_POST['gender']) && !empty($_POST['gender'])) $userData['gender'] = $_POST['gender'];
            if (isset($_POST['date_of_birth']) && !empty($_POST['date_of_birth'])) $userData['date_of_birth'] = $_POST['date_of_birth'];
            if (isset($_POST['phone_number']) && !empty($_POST['phone_number'])) $userData['phone_number'] = $_POST['phone_number'];
            if (isset($_POST['address']) && !empty($_POST['address'])) $userData['address'] = $_POST['address'];
            // Nếu có dữ liệu cần cập nhật
            if (!empty($userData)) {
                $userModel = new UserModel($this->pdo);
                $updated = $userModel->update($userId, $userData);
                if ($updated) {
                    $this->jsonResponse(['success' => true, 'message' => 'Cập nhật thành công']);
                    return;
                }
            } else {
                $this->jsonResponse(['success' => true, 'message' => 'Không có thay đổi']);
                return;
            }
            $this->jsonResponse(['success' => false, 'message' => 'Cập nhật thất bại']);
        }
    }
    public function destroy(int $id): void
    {
        if ($this->studentModel->delete($id)) {
            $this->jsonResponse(['success' => true, 'message' => 'Xóa sinh viên thành công (soft delete)']);
            // Hoặc redirect nếu không dùng AJAX: $this->redirect('students');
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Không thể xóa sinh viên (có thể đã xóa trước đó)'], 500);
        }
    }
    public function available(): void
    {
        $students = $this->studentModel->findAvailableStudents();
        $this->render('students/available', ['students' => $students]);
    }
    // // Thêm các phương thức mới
    // public function export(): void
    // {
    //     // Logic export Excel
    //     $students = $this->studentModel->findAll();
    //     // Implement export logic here
    // }
    // public function downloadTemplate(): void
    // {
    //     // Logic download template Excel
    //     // Implement template download logic here
    // }

    public function restore(int $studentId): bool
    {
        $sql = "UPDATE {$this->pdo} SET deleted_at = NULL WHERE student_id = :id";
        $stmt = $this->pdo->prepare($sql);
        try {
            return $stmt->execute(['id' => $studentId]) && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("StudentModel::restore error: " . $e->getMessage());
            return false;
        }
    }

    public function export(): void
    {
        try {
            // Lấy tất cả sinh viên
            $students = $this->studentModel->getAllStudentsWithRole();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Danh sách Sinh viên');

            // Header
            $headers = ['MSSV', 'Họ và Tên', 'Giới tính', 'Lớp', 'Khoa', 'Email', 'SĐT', 'Ngày sinh', 'Địa chỉ', 'Tình trạng'];
            $sheet->fromArray($headers, null, 'A1');

            // Data
            $row = 2;
            foreach ($students as $student) {
                $data = [
                    $student['mssv'] ?? 'Chưa có',
                    $student['full_name'] ?? 'N/A',
                    $student['gender'] ?? 'Chưa cập nhật',
                    $student['class_name'] ?? 'Chưa có',
                    $student['faculty_name'] ?? 'Chưa có',
                    $student['email'] ?? 'Chưa có',
                    $student['phone_number'] ?? 'Chưa có',
                    $student['date_of_birth'] ? date('d/m/Y', strtotime($student['date_of_birth'])) : 'Chưa có',
                    $student['address'] ?? 'Chưa có',
                    $student['status'] === 'active' ? 'Hoạt động' : 'Khóa'
                ];
                $sheet->fromArray($data, null, 'A' . $row);
                $row++;
            }

            // Auto size columns
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Output file
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="danh_sach_sinh_vien.xlsx"');
            $writer->save('php://output');
            exit;
        } catch (Exception $e) {
            $this->handleError($e, 'export');
        }
    }
    public function import(): void
    {
        // if (!file_exists($file) || !is_readable($file)) {
        //     $this->jsonResponse(['success' => false, 'message' => 'File upload thất bại hoặc không đọc được.'], 400);
        //     return;
        // }

        // if (!$class) {
        //     error_log("Lớp không tồn tại: $class_name");
        //     continue;
        // }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excelFile'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu không hợp lệ'], 400);
            return;
        }

        try {
            $file = $_FILES['excelFile']['tmp_name'];
            $updateExisting = isset($_POST['updateExisting']) && $_POST['updateExisting'] === 'on';

            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            array_shift($rows);  // Bỏ header

            $this->pdo->beginTransaction();
            $countSuccess = 0;

            foreach ($rows as $row) {
                $mssv = trim($row['A'] ?? '');
                $full_name = trim($row['B'] ?? '');
                $gender = trim($row['C'] ?? '');
                $class_name = trim($row['D'] ?? '');
                $email = trim($row['E'] ?? '');
                $phone_number = trim($row['F'] ?? '');

                if (empty($mssv) || empty($full_name) || empty($class_name)) continue;

                $class = $this->classesModel->findByName($class_name);
                if (!$class) continue;
                $class_id = $class['class_id'];
                $faculty_id = $class['faculty_id'];

                $existing = $this->studentModel->findByMssv($mssv);
                if ($existing) {
                    if (!$updateExisting) continue;
                    $userId = $existing['user_id'];
                    $this->userModel->update($userId, ['full_name' => $full_name, 'gender' => $gender, 'phone_number' => $phone_number]);
                    $countSuccess++;
                    continue;
                }

                // Tạo mới
                $accountId = $this->accountModel->create([
                    'username' => $mssv,
                    'email' => $email,
                    'password' => password_hash('123', PASSWORD_DEFAULT),
                    'role' => 'student',
                    'status' => 'active'
                ]);
                $userId = $this->userModel->create([
                    'account_id' => $accountId,
                    'full_name' => $full_name,
                    'gender' => $gender,
                    'phone_number' => $phone_number
                ]);
                $this->studentModel->create([
                    'user_id' => $userId,
                    'mssv' => $mssv,
                    'class_id' => $class_id,
                    'faculty_id' => $faculty_id
                ]);
                $countSuccess++;
            }

            $this->pdo->commit();
            $this->jsonResponse(['success' => true, 'message' => "Nhập thành công $countSuccess sinh viên."]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
    public function downloadTemplate(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['MSSV', 'Họ tên', 'Giới tính', 'Lớp', 'Email', 'Số điện thoại'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray(['SV001', 'Nguyễn Văn A', 'Nam', 'CNTT1', 'vana@example.com', '0123456789'], null, 'A2');

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="mau_nhap_sinh_vien.xlsx"');
        $writer->save('php://output');
        exit;
    }
}
