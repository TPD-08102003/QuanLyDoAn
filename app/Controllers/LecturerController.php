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
            // ⭐️ SỬA LỖI: Đọc dữ liệu JSON thay vì $_POST
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Fallback nếu không phải JSON (mặc dù JS của bạn là JSON)
                $data = $_POST;
            }
            // ⭐️ KẾT THÚC SỬA

            $msgv = trim($data['lecturer_code'] ?? '');
            $username = trim($data['username'] ?? $msgv);
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

            // Sửa: Sử dụng truy vấn trực tiếp để tránh thêm 'updated_at'
            $sql = "INSERT INTO accounts (username, email, password, role, status) 
                    VALUES (:username, :email, :password, :role, :status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => password_hash($default_password, PASSWORD_DEFAULT),
                'role' => 'teacher',
                'status' => $data['status'] ?? 'active'
            ]);
            $accountId = $this->pdo->lastInsertId();

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
            // ⭐️ SỬA LỖI: Đọc dữ liệu JSON thay vì $_POST
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data = $_POST;
            }
            // ⭐️ KẾT THÚC SỬA

            $lecturer = $this->lecturerModel->getFullLecturer($id);

            if (!$lecturer) {
                $this->jsonResponse(['success' => false, 'message' => 'Giảng viên không tồn tại.'], 404);
                return;
            }

            $msgv = trim($data['lecturer_code'] ?? $lecturer['lecturer_code']);
            $fullname = trim($data['full_name'] ?? $lecturer['full_name']);
            $email = trim($data['email'] ?? $lecturer['email']);
            $faculty_id = (int) ($data['faculty_id'] ?? $lecturer['faculty_id']);

            if (empty($msgv) || empty($fullname) || empty($email) || $faculty_id <= 0) {
                $this->jsonResponse(['success' => false, 'message' => 'Vui lòng điền đủ các trường bắt buộc.'], 400);
                return;
            }

            if ($msgv !== $lecturer['lecturer_code'] && $this->lecturerModel->isLecturerCodeExists($msgv)) {
                $this->jsonResponse(['success' => false, 'message' => "Mã giảng viên **{$msgv}** đã tồn tại."], 409);
                return;
            }

            if ($email !== $lecturer['email'] && $this->accountModel->findByEmail($email)) {
                $this->jsonResponse(['success' => false, 'message' => "Email **{$email}** đã tồn tại."], 409);
                return;
            }

            $this->pdo->beginTransaction();

            // Cập nhật account (nếu cần, sử dụng query trực tiếp nếu có updated_at ở model)
            $accountData = [];
            if ($email !== $lecturer['email']) $accountData['email'] = $email;
            if (isset($data['status']) && $data['status'] !== $lecturer['status']) $accountData['status'] = $data['status'];

            if (!empty($accountData)) {
                $fields = [];
                $params = [];
                foreach ($accountData as $key => $value) {
                    $fields[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
                $params[':id'] = $lecturer['account_id'];
                $sql = "UPDATE accounts SET " . implode(', ', $fields) . " WHERE account_id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }

            // Cập nhật user
            $userData = [];
            if ($fullname !== $lecturer['full_name']) $userData['full_name'] = $fullname;
            if (isset($data['gender']) && $data['gender'] !== $lecturer['gender']) $userData['gender'] = $data['gender'];
            if (isset($data['date_of_birth']) && $data['date_of_birth'] !== $lecturer['date_of_birth']) $userData['date_of_birth'] = $data['date_of_birth'];
            if (isset($data['phone_number']) && $data['phone_number'] !== $lecturer['phone_number']) $userData['phone_number'] = $data['phone_number'];
            if (isset($data['address']) && $data['address'] !== $lecturer['address']) $userData['address'] = $data['address'];

            if (!empty($userData)) {
                $this->userModel->update($lecturer['user_id'], $userData);
            }

            // Cập nhật lecturer
            $lecturerData = [];
            if ($msgv !== $lecturer['lecturer_code']) $lecturerData['lecturer_code'] = $msgv;
            if ($faculty_id !== $lecturer['faculty_id']) $lecturerData['faculty_id'] = $faculty_id;
            if (isset($data['position']) && $data['position'] !== $lecturer['position']) $lecturerData['position'] = $data['position'];
            if (isset($data['specialization']) && $data['specialization'] !== $lecturer['specialization']) $lecturerData['specialization'] = $data['specialization'];
            if (isset($data['years_of_experience']) && (int)$data['years_of_experience'] !== (int)$lecturer['years_of_experience']) $lecturerData['years_of_experience'] = (int)$data['years_of_experience'];

            if (!empty($lecturerData)) {
                $this->lecturerModel->update($id, $lecturerData);
            }

            $this->pdo->commit();

            $this->jsonResponse([
                'success' => true,
                'message' => "Cập nhật giảng viên **{$fullname}** thành công!",
            ]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("LecturerController::update error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): void
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

            $this->pdo->beginTransaction();

            // Soft delete lecturer
            $this->lecturerModel->delete($id);

            // Optional: Cập nhật status account nếu cần
            $sql = "UPDATE accounts SET status = 'inactive' WHERE account_id = :account_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['account_id' => $lecturer['account_id']]);

            $this->pdo->commit();

            $this->jsonResponse(['success' => true, 'message' => 'Xóa giảng viên thành công!']);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("LecturerController::destroy error: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function export(): void
    {
        try {
            $lecturers = $this->lecturerModel->getAllLecturersWithRole();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Danh sách Giảng viên');

            // Header
            $headers = ['MSGV', 'Họ và Tên', 'Giới tính', 'Khoa', 'Chức vụ', 'Chuyên ngành', 'Kinh nghiệm', 'Email', 'SĐT', 'Tình trạng'];
            $sheet->fromArray($headers, null, 'A1');

            // Data
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
                    $lecturer['status'] === 'active' ? 'Hoạt động' : 'Khóa'
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
            header('Content-Disposition: attachment; filename="danh_sach_giang_vien.xlsx"');
            $writer->save('php://output');
            exit;
        } catch (Exception $e) {
            $this->handleError($e, 'export');
        }
    }
    // public function import(): void
    // {
    //     // 1. Kiểm tra yêu cầu và file upload
    //     if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excelFile'])) {
    //         $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu không hợp lệ'], 400);
    //         return;
    //     }

    //     try {
    //         $file = $_FILES['excelFile']['tmp_name'];
    //         if (!file_exists($file) || !is_readable($file)) {
    //             throw new Exception('Không thể đọc file upload.');
    //         }

    //         // Lấy thông tin cờ update
    //         $updateExisting = isset($_POST['updateExisting']) && $_POST['updateExisting'] === 'on';

    //         // 2. Tải và đọc file Excel
    //         // Tương tự StudentController, sử dụng IOFactory để đọc file
    //         $spreadsheet = IOFactory::load($file);
    //         $sheet = $spreadsheet->getActiveSheet();
    //         $rows = $sheet->toArray(null, true, true, true);


    //         // THÊM CÁC DÒNG DEBUG NÀY TẠM THỜI
    //         error_log("Total rows read: " . count($rows));
    //         error_log("First data row (row 2): " . print_r($rows[1], true));
    //         // Sau đó, XÓA DÒNG DEBUG này đi sau khi kiểm tra xong!

    //         // 3. Chuẩn bị dữ liệu cần thiết (Map Khoa)
    //         // Cần map tên Khoa -> ID để tra cứu nhanh, tương tự như tra cứu Lớp/Khoa trong StudentController
    //         $faculties = $this->facultiesModel->getActiveFaculties(); // Giả định FacultiesModel có phương thức này
    //         $facultyMap = [];
    //         foreach ($faculties as $faculty) {
    //             $facultyMap[mb_strtoupper(trim($faculty['faculty_name']))] = $faculty['faculty_id'];
    //         }

    //         // 4. Khởi tạo và bắt đầu Transaction
    //         $this->pdo->beginTransaction();
    //         $countSuccess = 0;
    //         $errors = [];
    //         $default_password = '123';

    //         // Lặp qua từng dòng dữ liệu trong file Excel
    //         foreach ($rows as $rowNumber => $row) {
    //             // Row number thực tế sau khi bỏ header bắt đầu từ 2
    //             if ($rowNumber < 2) continue;

    //             // 4.1. Trích xuất và làm sạch dữ liệu
    //             // Căn cứ vào cấu trúc file template của giảng viên (MSGV, Họ tên, Giới tính, Khoa, Chức vụ, Chuyên ngành, Kinh nghiệm, Email, SĐT)
    //             $msgv = trim($row[1] ?? ''); // MSGV (lecturer_code)
    //             $fullname = trim($row[2] ?? '');
    //             $gender = trim($row[3] ?? 'Khác');
    //             $faculty_name = mb_strtoupper(trim($row[4] ?? '')); // Tên Khoa (dùng uppercase để so sánh)
    //             $position = trim($row[5] ?? null);
    //             $specialization = trim($row[6] ?? null);
    //             $years_of_experience = (int) ($row[7] ?? 0);
    //             $email = trim($row[8] ?? '');
    //             $phone_number = trim($row[9] ?? null);
    //             $username = empty($msgv) ? '' : $msgv; // Username mặc định là MSGV

    //             // BỎ QUA dòng trống
    //             if (empty($msgv) && empty($fullname) && empty($email) && empty($faculty_name)) {
    //                 continue;
    //             }

    //             // 4.2. Validation cơ bản
    //             if (empty($msgv) || empty($fullname) || empty($email) || empty($faculty_name)) {
    //                 $errors[] = "Dòng $rowNumber: Thiếu trường bắt buộc (MSGV, Họ tên, Email, Khoa).";
    //                 continue;
    //             }

    //             // 4.3. Tìm faculty_id
    //             $faculty_id = $facultyMap[$faculty_name] ?? 0;
    //             if ($faculty_id <= 0) {
    //                 $errors[] = "Dòng $rowNumber: Khoa **{$faculty_name}** không tồn tại trong hệ thống.";
    //                 continue;
    //             }

    //             // 4.4. Kiểm tra trùng lặp và tồn tại
    //             $existingLecturer = $this->lecturerModel->getFullLecturerByCode($msgv); // Giả định LecturerModel có phương thức này
    //             $isUpdate = $existingLecturer && $updateExisting;

    //             if ($existingLecturer) {
    //                 // Đã tồn tại MSGV
    //                 if (!$updateExisting) {
    //                     $errors[] = "Dòng $rowNumber: Mã giảng viên **{$msgv}** đã tồn tại và không được phép cập nhật.";
    //                     continue;
    //                 }
    //                 // Nếu là update, kiểm tra trùng email khác tài khoản hiện tại
    //                 $existingAccountId = $existingLecturer['account_id'];
    //                 $existingEmail = $this->accountModel->findByEmail($email); // Giả định AccountModel có findByEmail
    //                 if ($existingEmail && (int)$existingEmail['account_id'] !== (int)$existingAccountId) {
    //                     $errors[] = "Dòng $rowNumber: Email **{$email}** đã tồn tại và thuộc về một tài khoản khác.";
    //                     continue;
    //                 }
    //             } else {
    //                 // Là tạo mới, kiểm tra trùng username và email
    //                 if ($this->accountModel->findByUsername($username)) { // Giả định AccountModel có findByUsername
    //                     $errors[] = "Dòng $rowNumber: Username **{$username}** đã tồn tại.";
    //                     continue;
    //                 }
    //                 if ($this->accountModel->findByEmail($email)) {
    //                     $errors[] = "Dòng $rowNumber: Email **{$email}** đã tồn tại.";
    //                     continue;
    //                 }
    //             }

    //             // 4.5. Thực hiện Lưu/Cập nhật
    //             try {
    //                 if ($isUpdate) {
    //                     // ** CẬP NHẬT GIẢNG VIÊN ĐÃ TỒN TẠI **
    //                     $accountId = $existingLecturer['account_id'];
    //                     $userId = $existingLecturer['user_id'];
    //                     $lecturerId = $existingLecturer['lecturer_id'];

    //                     // 1. Cập nhật Account (chỉ email)
    //                     $this->accountModel->update($accountId, ['email' => $email]); // Giả định AccountModel có update()

    //                     // 2. Cập nhật User
    //                     $userData = [
    //                         'full_name' => $fullname,
    //                         'gender' => $gender,
    //                         'phone_number' => $phone_number,
    //                     ];
    //                     $this->userModel->update($userId, $userData); // Giả định UserModel có update()

    //                     // 3. Cập nhật Lecturer
    //                     $lecturerData = [
    //                         'faculty_id' => $faculty_id,
    //                         'position' => $position,
    //                         'specialization' => $specialization,
    //                         'years_of_experience' => $years_of_experience
    //                     ];
    //                     $this->lecturerModel->update($lecturerId, $lecturerData); // Giả định LecturerModel có update()

    //                 } else {
    //                     // ** TẠO GIẢNG VIÊN MỚI **
    //                     // 1. Tạo Account (Role là 'teacher')
    //                     $accountId = $this->accountModel->create([
    //                         'username' => $username,
    //                         'email' => $email,
    //                         'password' => password_hash($default_password, PASSWORD_DEFAULT),
    //                         'role' => 'teacher',
    //                         'status' => 'active'
    //                     ]);
    //                     if (!$accountId) {
    //                         throw new Exception("Không thể tạo tài khoản cho MSGV {$msgv}.");
    //                     }

    //                     // 2. Tạo User
    //                     $userId = $this->userModel->create([
    //                         'account_id' => $accountId,
    //                         'full_name' => $fullname,
    //                         'gender' => $gender,
    //                         'phone_number' => $phone_number,
    //                     ]);
    //                     if (!$userId) {
    //                         throw new Exception("Không thể tạo thông tin người dùng cho MSGV {$msgv}.");
    //                     }

    //                     // 3. Tạo Lecturer
    //                     $lecturerId = $this->lecturerModel->create([
    //                         'user_id' => $userId,
    //                         'lecturer_code' => $msgv,
    //                         'faculty_id' => $faculty_id,
    //                         'position' => $position,
    //                         'specialization' => $specialization,
    //                         'years_of_experience' => $years_of_experience
    //                     ]);
    //                     if (!$lecturerId) {
    //                         throw new Exception("Không thể tạo thông tin giảng viên cho MSGV {$msgv}.");
    //                     }
    //                 }
    //                 $countSuccess++;
    //             } catch (\Exception $e) {
    //                 // Bắt lỗi trong quá trình CRUD
    //                 $errors[] = "Dòng $rowNumber: Lỗi lưu DB. " . $e->getMessage();
    //             }
    //         }

    //         // 5. Hoàn tất Transaction và Trả về kết quả
    //         $this->pdo->commit();
    //         $message = "Nhập thành công $countSuccess giảng viên.";
    //         if (!empty($errors)) {
    //             $message .= " Có " . count($errors) . " lỗi. Vui lòng xem chi tiết lỗi.";
    //         }

    //         $this->jsonResponse(['success' => true, 'message' => $message, 'errors' => $errors]);
    //     } catch (\PDOException $e) {
    //         $this->pdo->rollBack();
    //         error_log("LecturerController::import PDO Error: " . $e->getMessage());
    //         $this->jsonResponse(['success' => false, 'message' => 'Lỗi Database: ' . $e->getMessage()], 500);
    //     } catch (\Exception $e) {
    //         $this->pdo->rollBack();
    //         error_log("LecturerController::import Logic Error: " . $e->getMessage());
    //         $this->jsonResponse(['success' => false, 'message' => 'Lỗi nhập file: ' . $e->getMessage()], 500);
    //     }
    // }
    // tập tin: controllers/LecturerController.php

    // ... (các phương thức khác) ...

    public function import(): void
    {
        // 1. Kiểm tra yêu cầu và file tải lên
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu không hợp lệ.'], 400);
            return;
        }

        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi tải lên file (Mã: ' . $file['error'] . ').'], 500);
            return;
        }

        try {
            // Khởi tạo và đọc file
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);
            $countSuccess = 0;
            $errors = [];

            // Bắt đầu Transaction
            $this->pdo->beginTransaction();

            foreach ($data as $rowNum => $row) {
                if ($rowNum === 1) continue; // Bỏ qua hàng tiêu đề

                $msgv = trim($row['A'] ?? '');
                $full_name = trim($row['B'] ?? '');
                $gender = trim($row['C'] ?? 'Khác');
                $faculty_name = trim($row['D'] ?? '');
                $position = trim($row['E'] ?? '');
                $specialization = trim($row['F'] ?? '');
                $years_of_experience = (int) ($row['G'] ?? 0);
                $email = trim($row['H'] ?? '');
                $phone_number = trim($row['I'] ?? '');

                if (empty($msgv) && empty($full_name) && empty($email)) {
                    continue; // Bỏ qua dòng trống
                }

                $errorMsg = "Lỗi dòng $rowNum ($msgv - $full_name): ";
                $skipRow = false;

                // 2. Kiểm tra dữ liệu bắt buộc
                if (empty($msgv) || empty($full_name) || empty($email) || empty($faculty_name)) {
                    $errors[] = $errorMsg . "Thiếu dữ liệu bắt buộc (MSGV, Họ tên, Email, Khoa).";
                    $skipRow = true;
                }

                // 3. Tìm ID Khoa
                $faculty_id = 0;
                if (!$skipRow) {
                    $faculty = $this->facultiesModel->findByName($faculty_name);
                    if (!$faculty) {
                        $errors[] = $errorMsg . "Tên Khoa '{$faculty_name}' không tồn tại.";
                        $skipRow = true;
                    } else {
                        $faculty_id = $faculty['faculty_id'];
                    }
                }

                // 4. Kiểm tra trùng lặp
                if (!$skipRow) {
                    if ($this->lecturerModel->isLecturerCodeExists($msgv)) {
                        $errors[] = $errorMsg . "MSGV '{$msgv}' đã tồn tại.";
                        $skipRow = true;
                    }
                }

                if (!$skipRow) {
                    if ($this->accountModel->findByUsername($msgv)) {
                        $errors[] = $errorMsg . "Tên đăng nhập '{$msgv}' đã tồn tại.";
                        $skipRow = true;
                    }
                }

                if (!$skipRow) {
                    if ($this->accountModel->findByEmail($email)) {
                        $errors[] = $errorMsg . "Email '{$email}' đã tồn tại.";
                        $skipRow = true;
                    }
                }

                // 5. Thực hiện nhập dữ liệu
                if (!$skipRow) {
                    try {
                        // ==========================================================
                        // ⭐️ BẮT ĐẦU SỬA LỖI 'updated_at' ⭐️
                        // ==========================================================

                        // 5.1. Tạo Account (Sử dụng SQL trực tiếp)
                        $default_password = '123';
                        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

                        $sqlAcc = "INSERT INTO accounts (username, email, password, role, status) 
                                   VALUES (:username, :email, :password, :role, :status)";
                        $stmtAcc = $this->pdo->prepare($sqlAcc);
                        $stmtAcc->execute([
                            'username' => $msgv,
                            'email' => $email,
                            'password' => $hashed_password,
                            'role' => 'teacher',
                            'status' => 'active'
                        ]);
                        $accountId = $this->pdo->lastInsertId();

                        if (!$accountId) {
                            throw new Exception("Không thể tạo Account");
                        }

                        // 5.2. Tạo User (Sử dụng SQL trực tiếp)
                        $sqlUser = "INSERT INTO users (account_id, full_name, gender, phone_number) 
                                    VALUES (:account_id, :full_name, :gender, :phone_number)";
                        $stmtUser = $this->pdo->prepare($sqlUser);
                        $stmtUser->execute([
                            'account_id' => $accountId,
                            'full_name' => $full_name,
                            'gender' => $gender,
                            'phone_number' => $phone_number
                        ]);
                        $userId = $this->pdo->lastInsertId();

                        if (!$userId) {
                            throw new Exception("Không thể tạo User");
                        }

                        // ==========================================================
                        // ⭐️ KẾT THÚC SỬA LỖI ⭐️
                        // ==========================================================


                        // 5.3. Tạo Giảng viên (Phương thức này đã an toàn vì được ghi đè trong LecturerModel)
                        $lecturerData = [
                            'user_id' => $userId,
                            'lecturer_code' => $msgv,
                            'faculty_id' => $faculty_id,
                            'position' => $position,
                            'specialization' => $specialization,
                            'years_of_experience' => $years_of_experience
                        ];

                        $lecturerId = $this->lecturerModel->create($lecturerData);

                        if (!$lecturerId) {
                            throw new Exception("Không thể tạo Lecturer");
                        }

                        $countSuccess++;
                    } catch (Exception $e) {
                        $errors[] = $errorMsg . "Lỗi khi tạo dữ liệu: " . $e->getMessage();
                        error_log("Error creating lecturer $msgv: " . $e->getMessage());
                    }
                }
            }

            // Commit transaction
            $this->pdo->commit();

            $message = "Nhập thành công {$countSuccess} giảng viên.";
            if (!empty($errors)) {
                $message .= " Có " . count($errors) . " lỗi xảy ra.";
            }

            $this->jsonResponse([
                'success' => true,
                'message' => $message,
                'details' => $errors
            ]);
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("LecturerController::import error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
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
