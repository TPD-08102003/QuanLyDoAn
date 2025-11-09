<?php
// controllers/LecturerController.php

namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use PDO;
use Exception;
use App\Models\LecturerModel;
use App\Models\UserModel;
use App\Models\AccountModel;
use App\Models\FacultiesModel;

class LecturerController extends BaseController
{
    private LecturerModel $lecturerModel;
    private UserModel $userModel;
    private AccountModel $accountModel;
    private FacultiesModel $facultiesModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->lecturerModel = new LecturerModel($pdo);
        $this->userModel = new UserModel($pdo);
        $this->accountModel = new AccountModel($pdo);
        $this->facultiesModel = new FacultiesModel($pdo);
    }

    public function manage(): void
    {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $keyword = trim($_GET['keyword'] ?? '');
            $limit = 10;
            $offset = ($page - 1) * $limit;

            if (empty($keyword)) {
                $allLecturers = $this->lecturerModel->getAllLecturersWithRole();
                $totalLecturers = count($allLecturers);
                $lecturers = array_slice($allLecturers, $offset, $limit);
                $totalPages = ceil($totalLecturers / $limit);
            } else {
                $result = $this->lecturerModel->getLecturersWithPagination($limit, $offset, $keyword);
                $lecturers = $result['lecturers'];
                $totalLecturers = $result['total'];
                $totalPages = ceil($totalLecturers / $limit);
            }

            $faculties = $this->facultiesModel->findAll();

            $this->render('lecturers/manage', [
                'lecturers' => $lecturers,
                'totalLecturers' => $totalLecturers,
                'totalPages' => $totalPages,
                'page' => $page,
                'keyword' => $keyword,
                'faculties' => $faculties,
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'manage');
        }
    }
    public function show(int $id): void
    {
        try {
            $lecturer = $this->lecturerModel->getFullLecturer($id);
            if (!$lecturer) {
                $this->jsonResponse(['success' => false, 'message' => 'Giảng viên không tồn tại.'], 404);
                return;
            }
            $this->jsonResponse(['success' => true, 'lecturer' => $lecturer]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi server.'], 500);
        }
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        try {
            $data = $_POST;
            $msgv = trim($data['lecturer_code'] ?? '');
            $username = trim($data['username'] ?? $msgv); // Fallback to msgv if username empty
            $fullname = trim($data['full_name'] ?? '');
            $email = trim($data['email'] ?? '');
            $faculty_id = (int) ($data['faculty_id'] ?? 0);
            $default_password = '123';

            if (empty($msgv) || empty($fullname) || empty($email) || $faculty_id <= 0) {
                $this->jsonResponse(['success' => false, 'message' => 'Vui lòng điền đủ các trường bắt buộc.'], 400);
                return;
            }

            if ($this->lecturerModel->isLecturerCodeExists($msgv)) {
                $this->jsonResponse(['success' => false, 'message' => "Mã giảng viên **{$msgv}** đã tồn tại."], 409);
                return;
            }

            if ($this->accountModel->findByUsername($username)) {
                $this->jsonResponse(['success' => false, 'message' => "Username **{$username}** đã tồn tại."], 409);
                return;
            }

            if ($this->accountModel->findByEmail($email)) {
                $this->jsonResponse(['success' => false, 'message' => "Email **{$email}** đã tồn tại."], 409);
                return;
            }

            $this->pdo->beginTransaction();

            $accountId = $this->accountModel->create([
                'username' => $username,
                'email' => $email,
                'password' => password_hash($default_password, PASSWORD_DEFAULT),
                'role' => 'teacher',
                'status' => $data['status'] ?? 'active'
            ]);

            $userData = [
                'account_id' => $accountId,
                'full_name' => $fullname,
                'gender' => $data['gender'] ?? 'Khác',
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'address' => $data['address'] ?? null,
            ];
            $userId = $this->userModel->create($userData);

            $lecturerData = [
                'user_id' => $userId,
                'lecturer_code' => $msgv,
                'faculty_id' => $faculty_id,
                'position' => $data['position'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'years_of_experience' => (int)($data['years_of_experience'] ?? 0)
            ];
            $lecturerId = $this->lecturerModel->create($lecturerData);

            $this->pdo->commit();

            $this->jsonResponse([
                'success' => true,
                'message' => "Thêm giảng viên **{$fullname}** thành công! (MSGV: {$msgv})",
            ], 201);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("LecturerController::store error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
            return;
        }

        try {
            $lecturer = $this->lecturerModel->getFullLecturer($id);
            if (!$lecturer) {
                $this->jsonResponse(['success' => false, 'message' => 'Giảng viên không tồn tại.'], 404);
                return;
            }

            $data = $_POST;
            $lecturerData = [];
            $userData = [];

            if (isset($data['lecturer_code']) && $data['lecturer_code'] !== $lecturer['lecturer_code']) {
                if ($this->lecturerModel->isLecturerCodeExists($data['lecturer_code'])) {
                    $this->jsonResponse(['success' => false, 'message' => 'Mã giảng viên đã tồn tại.'], 409);
                    return;
                }
                $lecturerData['lecturer_code'] = $data['lecturer_code'];
            }

            if (isset($data['faculty_id'])) $lecturerData['faculty_id'] = (int)$data['faculty_id'];
            if (isset($data['position'])) $lecturerData['position'] = $data['position'];
            if (isset($data['specialization'])) $lecturerData['specialization'] = $data['specialization'];
            if (isset($data['years_of_experience'])) $lecturerData['years_of_experience'] = (int)$data['years_of_experience'];

            if (isset($data['full_name'])) $userData['full_name'] = $data['full_name'];
            if (isset($data['gender'])) $userData['gender'] = $data['gender'];
            if (isset($data['date_of_birth'])) $userData['date_of_birth'] = $data['date_of_birth'];
            if (isset($data['phone_number'])) $userData['phone_number'] = $data['phone_number'];
            if (isset($data['address'])) $userData['address'] = $data['address'];

            $this->pdo->beginTransaction();

            if (!empty($lecturerData)) {
                $this->lecturerModel->update($id, $lecturerData);
            }
            if (!empty($userData)) {
                $this->userModel->update($lecturer['user_id'], $userData);
            }

            $this->pdo->commit();
            $this->jsonResponse(['success' => true, 'message' => 'Cập nhật thành công.']);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($this->lecturerModel->delete($id)) {
            $this->jsonResponse(['success' => true, 'message' => 'Xóa giảng viên thành công (soft delete).']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Không thể xóa.'], 500);
        }
    }

    public function export(): void
    {
        try {
            $lecturers = $this->lecturerModel->getAllLecturersWithRole();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Danh sách Giảng viên');

            $headers = ['MSGV', 'Họ và Tên', 'Giới tính', 'Khoa', 'Chức vụ', 'Chuyên ngành', 'Kinh nghiệm (năm)', 'Email', 'SĐT', 'Ngày sinh', 'Địa chỉ', 'Tình trạng'];
            $sheet->fromArray($headers, null, 'A1');

            $row = 2;
            foreach ($lecturers as $lecturer) {
                $data = [
                    $lecturer['lecturer_code'] ?? 'Chưa có',
                    $lecturer['full_name'] ?? 'N/A',
                    $lecturer['gender'] ?? 'Chưa cập nhật',
                    $lecturer['faculty_name'] ?? 'Chưa có',
                    $lecturer['position'] ?? 'Chưa có',
                    $lecturer['specialization'] ?? 'Chưa có',
                    $lecturer['years_of_experience'] ?? 0,
                    $lecturer['email'] ?? 'Chưa có',
                    $lecturer['phone_number'] ?? 'Chưa có',
                    $lecturer['date_of_birth'] ? date('d/m/Y', strtotime($lecturer['date_of_birth'])) : 'Chưa có',
                    $lecturer['address'] ?? 'Chưa có',
                    $lecturer['status'] === 'active' ? 'Hoạt động' : 'Khóa'
                ];
                $sheet->fromArray($data, null, 'A' . $row);
                $row++;
            }

            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="danh_sach_giang_vien.xlsx"');
            $writer->save('php://output');
            exit;
        } catch (Exception $e) {
            $this->handleError($e, 'export');
        }
    }

    public function import(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excelFile'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu không hợp lệ'], 400);
            return;
        }

        try {
            $file = $_FILES['excelFile']['tmp_name'];
            if (!file_exists($file) || !is_readable($file)) {
                throw new Exception('Không thể đọc file upload.');
            }

            $updateExisting = isset($_POST['updateExisting']) && $_POST['updateExisting'] === 'on';

            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            array_shift($rows);  // Bỏ header

            $this->pdo->beginTransaction();
            $countSuccess = 0;
            $errors = [];  // To collect row-specific errors

            foreach ($rows as $rowNumber => $row) {
                $msgv = trim($row['A'] ?? '');
                $full_name = trim($row['B'] ?? '');
                $gender = trim($row['C'] ?? '');
                $faculty_name = trim($row['D'] ?? '');
                $position = trim($row['E'] ?? '');
                $specialization = trim($row['F'] ?? '');
                $years_of_experience = (int)(trim($row['G'] ?? 0));
                $email = trim($row['H'] ?? '');
                $phone_number = trim($row['I'] ?? '');

                if (empty($msgv) || empty($full_name) || empty($faculty_name) || empty($email)) {
                    $errors[] = "Dòng " . ($rowNumber + 1) . ": Thiếu dữ liệu bắt buộc (MSGV, Họ tên, Khoa, Email).";
                    continue;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Dòng " . ($rowNumber + 1) . ": Email không hợp lệ.";
                    continue;
                }

                $faculty = $this->facultiesModel->findByName($faculty_name);
                if (!$faculty) {
                    $errors[] = "Dòng " . ($rowNumber + 1) . ": Khoa '$faculty_name' không tồn tại.";
                    continue;
                }
                $faculty_id = $faculty['faculty_id'];

                $existing = $this->lecturerModel->findByCode($msgv);
                if ($existing) {
                    if (!$updateExisting) {
                        $errors[] = "Dòng " . ($rowNumber + 1) . ": MSGV '$msgv' đã tồn tại (không cập nhật).";
                        continue;
                    }
                    // Update existing
                    $userId = $existing['user_id'];
                    $this->userModel->update($userId, [
                        'full_name' => $full_name,
                        'gender' => $gender,
                        'phone_number' => $phone_number
                    ]);
                    $this->lecturerModel->update($existing['lecturer_id'], [
                        'faculty_id' => $faculty_id,  // Add this if you want to allow faculty updates
                        'position' => $position,
                        'specialization' => $specialization,
                        'years_of_experience' => $years_of_experience
                    ]);
                    $countSuccess++;
                    continue;
                }

                // Check duplicates for new
                if ($this->accountModel->findByUsername($msgv)) {
                    $errors[] = "Dòng " . ($rowNumber + 1) . ": Username '$msgv' đã tồn tại.";
                    continue;
                }
                if ($this->accountModel->findByEmail($email)) {
                    $errors[] = "Dòng " . ($rowNumber + 1) . ": Email '$email' đã tồn tại.";
                    continue;
                }

                // Create new
                $accountId = $this->accountModel->create([
                    'username' => $msgv,
                    'email' => $email,
                    'password' => password_hash('123', PASSWORD_DEFAULT),
                    'role' => 'teacher',
                    'status' => 'active'
                ]);
                $userId = $this->userModel->create([
                    'account_id' => $accountId,
                    'full_name' => $full_name,
                    'gender' => $gender,
                    'phone_number' => $phone_number
                ]);
                $this->lecturerModel->create([
                    'user_id' => $userId,
                    'lecturer_code' => $msgv,
                    'faculty_id' => $faculty_id,
                    'position' => $position,
                    'specialization' => $specialization,
                    'years_of_experience' => $years_of_experience
                ]);
                $countSuccess++;
            }

            $this->pdo->commit();
            $message = "Nhập thành công $countSuccess giảng viên.";
            if (!empty($errors)) {
                $message .= " Có lỗi: " . implode(' ', $errors);
            }
            $this->jsonResponse(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("LecturerController::import error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi nhập file: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplate(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['MSGV', 'Họ tên', 'Giới tính', 'Khoa', 'Chức vụ', 'Chuyên ngành', 'Kinh nghiệm (năm)', 'Email', 'Số điện thoại'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray(['GV001', 'Nguyễn Văn B', 'Nam', 'Công nghệ thông tin', 'Tiến sĩ', 'AI', '10', 'vanb@example.com', '0987654321'], null, 'A2');

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="mau_nhap_giang_vien.xlsx"');
        $writer->save('php://output');
        exit;
    }
}
