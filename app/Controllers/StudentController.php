<?php
// controllers/StudentController.php

namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
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
        date_default_timezone_set('Asia/Ho_Chi_Minh');
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
            $limit = 5;
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

    // public function export(): void
    // {
    //     try {
    //         // Lấy tất cả sinh viên
    //         $students = $this->studentModel->getAllStudentsWithRole();

    //         $spreadsheet = new Spreadsheet();
    //         $sheet = $spreadsheet->getActiveSheet();
    //         $sheet->setTitle('Danh sách Sinh viên');

    //         // Header
    //         $headers = ['MSSV', 'Họ và Tên', 'Giới tính', 'Lớp', 'Khoa', 'Email', 'SĐT', 'Ngày sinh', 'Địa chỉ', 'Tình trạng'];
    //         $sheet->fromArray($headers, null, 'A1');

    //         // Data
    //         $row = 2;
    //         foreach ($students as $student) {
    //             $data = [
    //                 $student['mssv'] ?? 'Chưa có',
    //                 $student['full_name'] ?? 'N/A',
    //                 $student['gender'] ?? 'Chưa cập nhật',
    //                 $student['class_name'] ?? 'Chưa có',
    //                 $student['faculty_name'] ?? 'Chưa có',
    //                 $student['email'] ?? 'Chưa có',
    //                 $student['phone_number'] ?? 'Chưa có',
    //                 $student['date_of_birth'] ? date('d/m/Y', strtotime($student['date_of_birth'])) : 'Chưa có',
    //                 $student['address'] ?? 'Chưa có',
    //                 $student['status'] === 'active' ? 'Hoạt động' : 'Khóa'
    //             ];
    //             $sheet->fromArray($data, null, 'A' . $row);
    //             $row++;
    //         }

    //         // Auto size columns
    //         foreach (range('A', 'J') as $col) {
    //             $sheet->getColumnDimension($col)->setAutoSize(true);
    //         }

    //         // Output file
    //         $writer = new Xlsx($spreadsheet);
    //         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //         header('Content-Disposition: attachment; filename="danh_sach_sinh_vien.xlsx"');
    //         $writer->save('php://output');
    //         exit;
    //     } catch (Exception $e) {
    //         $this->handleError($e, 'export');
    //     }
    // }
    public function export(): void
    {
        try {
            // 1. Lấy tham số và dữ liệu
            $exportType = $_GET['export_type'] ?? 'all';
            $classId = !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null;
            $classNameDisplay = "Tất cả"; // Mặc định hiển thị tên lớp

            if ($exportType === 'class' && $classId) {
                $students = $this->studentModel->getStudentsForExport($classId);
                $fileNameSuffix = "_lop_" . $classId;

                // Lấy tên lớp để hiển thị trong file Excel
                $classInfo = $this->classesModel->findById($classId);
                if ($classInfo) {
                    $classNameDisplay = $classInfo['class_name'];
                }
            } else {
                $students = $this->studentModel->getStudentsForExport(null);
                $fileNameSuffix = "_tat_ca";
            }

            if (empty($students)) {
                $_SESSION['message'] = "Không có dữ liệu sinh viên nào để xuất.";
                $_SESSION['message_type'] = "warning";
                header('Location: /quanlydoan/Student/manage');
                exit;
            }

            // 2. Khởi tạo Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Danh sách Sinh viên');

            // --- CẤU HÌNH STYLE CHUNG (Times New Roman) ---
            $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
            $spreadsheet->getDefaultStyle()->getFont()->setSize(12);

            // --- PHẦN TIÊU ĐỀ TRANG (HEADINGS) ---

            // Dòng 1: Tiêu đề lớn
            $sheet->setCellValue('A1', 'DANH SÁCH SINH VIÊN');
            $sheet->mergeCells('A1:J1'); // Gộp từ cột A đến J (10 cột)
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Dòng 2: Tên lớp
            $sheet->setCellValue('A2', 'Lớp: ' . $classNameDisplay);
            $sheet->getStyle('A2')->getFont()->setBold(true);

            // Dòng 3: Ngày xuất
            $sheet->setCellValue('A3', 'Ngày xuất: ' . date('d/m/Y H:i'));
            $sheet->getStyle('A3')->getFont()->setItalic(true);

            // --- PHẦN BẢNG DỮ LIỆU ---

            // Dòng 5: Tiêu đề cột
            $headerRow = 5;
            $headers = ['MSSV', 'Họ và Tên', 'Giới tính', 'Lớp', 'Khoa', 'Email', 'SĐT', 'Ngày sinh', 'Địa chỉ', 'Tình trạng'];
            $sheet->fromArray($headers, null, 'A' . $headerRow);

            // Style cho dòng tiêu đề cột (Nền xám, in đậm, căn giữa, có viền)
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFEFEFEF']
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ];
            $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->applyFromArray($headerStyle);

            // Dữ liệu: Bắt đầu từ dòng 6
            $row = $headerRow + 1;
            foreach ($students as $student) {
                $data = [
                    $student['mssv'] ?? '',
                    $student['full_name'] ?? '',
                    $student['gender'] ?? '',
                    $student['class_name'] ?? '',
                    $student['faculty_name'] ?? '',
                    $student['email'] ?? '',
                    $student['phone_number'] ?? '',
                    $student['date_of_birth'] ? date('d/m/Y', strtotime($student['date_of_birth'])) : '',
                    $student['address'] ?? '',
                    $student['status'] === 'active' ? 'Hoạt động' : 'Khóa'
                ];
                $sheet->fromArray($data, null, 'A' . $row);
                $row++;
            }

            // --- ĐỊNH DẠNG CUỐI CÙNG ---

            // 1. Kẻ bảng (Border) cho toàn bộ vùng dữ liệu
            $lastRow = $row - 1;
            $dataRange = 'A' . $headerRow . ':J' . $lastRow; // Từ dòng tiêu đề đến dòng cuối
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // 2. Auto size columns (Tự động chỉnh độ rộng cột)
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // 3. Xuất file
            $fileName = "danh_sach_sinh_vien" . $fileNameSuffix . "_" . date('Y-m-d_H-i') . ".xlsx";
            $writer = new Xlsx($spreadsheet);

            // Xóa buffer để tránh lỗi file bị hỏng do khoảng trắng thừa
            if (ob_get_length()) ob_clean();

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (Exception $e) {
            $this->handleError($e, 'export');
        }
    }

    // Trong StudentController.php
    public function import(): void
    {
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

            // Bỏ dòng tiêu đề
            array_shift($rows);

            $this->pdo->beginTransaction();
            $countSuccess = 0;
            $failedRows = [];

            foreach ($rows as $index => $row) {
                $rowIndex = $index + 2; // +2 vì index bắt đầu từ 0 và đã bỏ header

                // Lấy dữ liệu thô
                $mssv          = trim($row['A'] ?? '');
                $full_name     = trim($row['B'] ?? '');
                $gender        = trim($row['C'] ?? '');
                $raw_dob       = trim($row['D'] ?? ''); // <--- Lấy dữ liệu thô ngày sinh
                $class_name    = trim($row['E'] ?? '');
                // Cột F là Khoa (bỏ qua vì lấy theo lớp)
                $academic_year = trim($row['G'] ?? '2021-2025');
                $email         = trim($row['H'] ?? '');
                $phone_number  = trim($row['I'] ?? '');
                $address       = trim($row['J'] ?? '');

                // --- XỬ LÝ NGÀY SINH ---
                $dob = $this->processDate($raw_dob); // <--- Gọi hàm xử lý ngày sinh

                // 1. Kiểm tra dữ liệu bắt buộc
                if (empty($mssv) || empty($full_name) || empty($class_name)) {
                    $failedRows[] = "Dòng $rowIndex: Thiếu MSSV, Tên hoặc Lớp.";
                    continue;
                }

                // 2. Kiểm tra lớp học
                $class = $this->classesModel->findByName($class_name);
                if (!$class) {
                    $failedRows[] = "Dòng $rowIndex: Lớp '$class_name' không tồn tại.";
                    continue;
                }

                $class_id = $class['class_id'];
                $faculty_id = $class['faculty_id'];

                // 3. Xử lý Sinh viên đã tồn tại
                $existing = $this->studentModel->findByMssv($mssv);
                if ($existing) {
                    if (!$updateExisting) {
                        $failedRows[] = "Dòng $rowIndex: MSSV $mssv đã tồn tại.";
                        continue;
                    }

                    // Cập nhật User
                    $userId = $existing['user_id'];
                    $updateData = [
                        'full_name' => $full_name,
                        'gender' => $gender,
                        'phone_number' => $phone_number,
                        'address' => $address
                    ];
                    if (!empty($dob)) {
                        $updateData['date_of_birth'] = $dob;
                    }

                    $this->userModel->update($userId, $updateData);
                    $countSuccess++;
                    continue;
                }

                // 4. Tạo mới 
                if (empty($email)) {
                    $email = strtolower($mssv) . '@student.ctu.edu.vn';
                }

                if ($this->accountModel->findByEmail($email)) {
                    $failedRows[] = "Dòng $rowIndex: Email $email trùng lặp.";
                    continue;
                }

                // Tạo Account
                $accountId = $this->accountModel->create([
                    'username' => $mssv,
                    'email' => $email,
                    'password' => password_hash('123', PASSWORD_DEFAULT),
                    'role' => 'student',
                    'status' => 'active'
                ]);

                if (!$accountId) {
                    $failedRows[] = "Dòng $rowIndex: Lỗi tạo tài khoản.";
                    continue;
                }

                // Tạo User
                $userData = [
                    'account_id' => $accountId,
                    'full_name' => $full_name,
                    'gender' => $gender,
                    'phone_number' => $phone_number,
                    'address' => $address,
                    'date_of_birth' => $dob // <--- Sử dụng ngày sinh đã convert
                ];

                $userId = $this->userModel->create($userData);

                // Tạo Student
                $this->studentModel->create([
                    'user_id' => $userId,
                    'mssv' => $mssv,
                    'class_id' => $class_id,
                    'faculty_id' => $faculty_id,
                    'academic_year' => $academic_year
                ]);

                $countSuccess++;
            }

            $this->pdo->commit();

            $msg = "Đã nhập thành công $countSuccess sinh viên.";
            if (count($failedRows) > 0) {
                $msg .= " Có " . count($failedRows) . " dòng lỗi.";
            }

            $this->jsonResponse([
                'success' => true,
                'message' => $msg,
                'errors' => $failedRows
            ]);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hàm hỗ trợ chuyển đổi ngày từ Excel sang định dạng Y-m-d cho MySQL
     */
    private function processDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Trường hợp 1: Excel trả về số (Excel Serial Date)
            if (is_numeric($value)) {
                // Chuyển từ số Excel sang PHP DateTime object
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            }

            // Trường hợp 2: Excel trả về chuỗi text (vd: "30/01/2003" hoặc "30-01-2003")
            $value = str_replace('/', '-', $value); // Đổi dấu / thành -
            $date = date_create($value);
            if ($date) {
                return date_format($date, 'Y-m-d');
            }
        } catch (\Exception $e) {
            // Nếu lỗi format thì trả về null hoặc để nguyên
            return null;
        }

        return null;
    }

    public function downloadTemplate(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // SỬA: Thêm cột "Địa chỉ" vào cuối mảng headers
        $headers = ['MSSV', 'Họ tên', 'Giới tính', 'Ngày sinh', 'Lớp', 'Khoa', 'Niên khóa', 'Email', 'SĐT', 'Địa chỉ'];

        $sheet->fromArray($headers, null, 'A1');

        // SỬA: Thêm dữ liệu mẫu cho cột địa chỉ
        $sampleData = [
            'B2111xxx',
            'Nguyễn Văn A',
            'Nam',
            '2003-01-01',
            'ĐHCNTT21A',
            'Công nghệ thông tin',
            '2021-2025',
            'nva@student.ctu.edu.vn',
            '0123456789',
            'Cần Thơ' // Dữ liệu mẫu cho cột Địa chỉ
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        // Tự động chỉnh độ rộng cột cho đẹp
        foreach (range('A', 'J') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        // Clean buffer để tránh lỗi file bị corrupt
        if (ob_get_length()) ob_clean();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="mau_nhap_sinh_vien.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
