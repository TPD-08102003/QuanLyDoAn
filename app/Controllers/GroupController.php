<?php
// controllers/GroupController.php

namespace App\Controllers;

use PDO;
use App\Models\GroupModel;
use App\Models\ProjectModel;
use App\Models\StudentModel;
use App\Models\GroupMemberModel;
use App\Models\GroupMessageModel;
use App\Models\LecturerModel;
use App\Models\UserModel;

class GroupController extends BaseController
{
    private GroupModel $groupModel;
    private ProjectModel $projectModel;
    private StudentModel $studentModel;
    private GroupMemberModel $groupMemberModel;
    private GroupMessageModel $groupMessageModel;
    private LecturerModel $lecturerModel;
    private UserModel $userModel;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->groupModel = new GroupModel($pdo);
        $this->projectModel = new ProjectModel($pdo);
        $this->studentModel = new StudentModel($pdo);
        $this->groupMemberModel = new GroupMemberModel($pdo);
        $this->groupMessageModel = new GroupMessageModel($pdo);
        $this->lecturerModel = new LecturerModel($pdo);
        $this->userModel = new UserModel($pdo);
    }

    /**
     * Router thông minh: Điều hướng dựa trên vai trò
     */
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role = $_SESSION['role'] ?? 'guest';

        if ($role === 'student') {
            $this->myGroup(); // Sinh viên vào trang chat nhóm
        } elseif ($role === 'teacher') {
            $this->teacherGroups(); // Giảng viên xem các nhóm mình hướng dẫn
        } else {
            // Admin hoặc mặc định xem tất cả
            $groups = $this->groupModel->getAllGroupsWithDetails();
            $this->render('groups/index', ['groups' => $groups]);
        }
    }

    /**
     * Dành cho Giảng viên: Xem các nhóm thuộc đồ án mình tạo
     */
    public function teacherGroups(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user = $this->userModel->findByAccountId($_SESSION['account_id']);
        $lecturer = $this->lecturerModel->findByUserIdLecturer($user['user_id']);

        if (!$lecturer) {
            echo "Lỗi: Không tìm thấy thông tin giảng viên.";
            return;
        }

        // Lấy danh sách nhóm thuộc các Project do giảng viên này tạo
        $sql = "SELECT g.*, p.title as project_title, 
                       u_leader.full_name as leader_name, s.mssv as leader_mssv,
                       (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.group_id) as member_count
                FROM groups g
                JOIN projects p ON g.project_id = p.project_id
                JOIN students s ON g.leader_id = s.student_id
                JOIN users u_leader ON s.user_id = u_leader.user_id
                WHERE p.lecturer_id = :lecturer_id
                ORDER BY p.title ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['lecturer_id' => $lecturer['lecturer_id']]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('groups/teacher_index', ['groups' => $groups]);
    }

    /**
     * Dành cho Sinh viên: Xem nhóm và Chat
     */
    public function myGroup(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user = $this->userModel->findByAccountId($_SESSION['account_id']);
        $student = $this->studentModel->findByUserId($user['user_id']);

        if (!$student) {
            header('Location: /quanlydoan');
            exit;
        }

        $sqlGroup = "SELECT g.*, p.title as project_title, p.status as project_status, 
                            u_lect.full_name as lecturer_name, p.max_students
                     FROM group_members gm
                     JOIN groups g ON gm.group_id = g.group_id
                     JOIN projects p ON g.project_id = p.project_id
                     LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
                     LEFT JOIN users u_lect ON l.user_id = u_lect.user_id
                     WHERE gm.student_id = :student_id LIMIT 1";

        $stmt = $this->pdo->prepare($sqlGroup);
        $stmt->execute([':student_id' => $student['student_id']]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$group) {
            $this->render('groups/no_group');
            return;
        }

        // Lấy thành viên
        $members = $this->groupMemberModel->getMembers($group['group_id']);

        // THÊM: Lấy tin nhắn ban đầu (để render server-side lần đầu nếu muốn, hoặc để trống cũng được vì JS sẽ load)
        // Nhưng quan trọng là truyền current_user_id để JS so sánh

        $this->render('groups/my_group', [
            'group' => $group,
            'members' => $members,
            'current_user_id' => $user['user_id'], // Cần biến này để biết tin nhắn nào là của mình
            'current_student_id' => $student['student_id']
        ]);
    }

    /**
     * API Chat: Gửi tin nhắn (AJAX)
     */
    public function sendMessage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->userModel->findByAccountId($_SESSION['account_id']);
        $groupId = $_POST['group_id'] ?? 0;
        $message = trim($_POST['message'] ?? '');

        if ($groupId && $message) {
            $this->groupMessageModel->saveMessage($groupId, $user['user_id'], $message);
            $this->jsonResponse(['success' => true]);
        }
        $this->jsonResponse(['success' => false]);
    }

    public function getMessages(int $groupId): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $user = $this->userModel->findByAccountId($_SESSION['account_id']);

        $messages = $this->groupMessageModel->getMessagesByGroupId($groupId);

        $formatted = array_map(function ($msg) use ($user) {
            return [
                'message_id' => $msg['message_id'], // Thêm ID để xử lý xóa
                'user_id'    => $msg['user_id'],
                'full_name'  => $msg['full_name'],
                'avatar'     => $msg['avatar'] ? '/quanlydoan/assets/images/' . $msg['avatar'] : '/quanlydoan/assets/images/profile.png',
                'message'    => htmlspecialchars($msg['message']),
                'time'       => date('H:i d/m', strtotime($msg['created_at'])),
                'is_me'      => ($msg['user_id'] == $user['user_id']),
                'is_recalled' => (bool)$msg['is_recalled'] // Trạng thái thu hồi
            ];
        }, $messages);

        $this->jsonResponse(['success' => true, 'messages' => $formatted]);
    }

    /**
     * API Chat: Thu hồi tin nhắn
     */
    public function recallMessage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $this->userModel->findByAccountId($_SESSION['account_id']);
        $messageId = (int)($_POST['message_id'] ?? 0);

        if ($messageId > 0) {
            $success = $this->groupMessageModel->recall($messageId, $user['user_id']);
            if ($success) {
                $this->jsonResponse(['success' => true, 'message' => 'Đã thu hồi tin nhắn.']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Không thể thu hồi (Không phải tin nhắn của bạn hoặc lỗi hệ thống).']);
            }
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
        }
    }

    public function create(): void
    {
        $projects = $this->projectModel->findAll();
        $students = $this->studentModel->findAll();
        $this->render('groups/create', ['projects' => $projects, 'students' => $students]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'project_id' => $_POST['project_id'] ?? 0,
                'leader_id' => $_POST['leader_id'] ?? 0
            ];
            $groupId = $this->groupModel->create($data);
            if ($groupId) {
                $this->redirect('groups');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to create group']);
    }

    public function show(int $id): void
    {
        $group = $this->groupModel->getFullGroup($id);
        $this->render('groups/show', ['group' => $group]);
    }

    public function edit(int $id): void
    {
        $group = $this->groupModel->getFullGroup($id);
        $projects = $this->projectModel->findAll();
        $students = $this->studentModel->findAll();
        $this->render('groups/edit', ['group' => $group, 'projects' => $projects, 'students' => $students]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'project_id' => $_POST['project_id'] ?? 0,
                'leader_id' => $_POST['leader_id'] ?? 0
            ];
            if ($this->groupModel->update($id, $data)) {
                $this->redirect('groups');
            }
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to update group']);
    }

    public function destroy(int $id): void
    {
        if ($this->groupModel->delete($id)) {
            $this->redirect('groups');
        }
        $this->jsonResponse(['success' => false, 'message' => 'Failed to delete group']);
    }
    /**
     * Hiển thị trang Nhóm của tôi
     * URL: /quanlydoan/group/myGroup
     */
    // public function myGroup(): void
    // {
    //     if (session_status() === PHP_SESSION_NONE) session_start();

    //     // 1. Kiểm tra đăng nhập
    //     if (!isset($_SESSION['account_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    //         header('Location: /quanlydoan/auth/login');
    //         exit;
    //     }

    //     try {
    //         // 2. Lấy Student ID từ Account ID
    //         $userModel = new \App\Models\UserModel($this->pdo);
    //         $user = $userModel->findByAccountId($_SESSION['account_id']);
    //         $student = $this->studentModel->findByUserId($user['user_id']);

    //         if (!$student) {
    //             // Xử lý lỗi nếu data không khớp
    //             header('Location: /quanlydoan');
    //             exit;
    //         }

    //         // 3. Tìm nhóm mà sinh viên đang tham gia
    //         // Query này join: Group -> Project -> Lecturer -> User (của Lecturer)
    //         $sqlGroup = "SELECT g.*, p.title as project_title, p.status as project_status, 
    //                             u_lect.full_name as lecturer_name, 
    //                             p.max_students
    //                      FROM group_members gm
    //                      JOIN groups g ON gm.group_id = g.group_id
    //                      JOIN projects p ON g.project_id = p.project_id
    //                      LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
    //                      LEFT JOIN users u_lect ON l.user_id = u_lect.user_id
    //                      WHERE gm.student_id = :student_id 
    //                      LIMIT 1";

    //         $stmt = $this->pdo->prepare($sqlGroup);
    //         $stmt->execute([':student_id' => $student['student_id']]);
    //         $group = $stmt->fetch(PDO::FETCH_ASSOC);

    //         // Nếu sinh viên chưa có nhóm
    //         if (!$group) {
    //             $this->render('groups/no_group'); // Tạo view này ở Bước 4
    //             return;
    //         }

    //         // 4. Lấy danh sách thành viên trong nhóm
    //         $sqlMembers = "SELECT s.student_id, s.mssv, u.full_name, u.email, u.phone_number, u.avatar,
    //                               CASE WHEN g.leader_id = s.student_id THEN 1 ELSE 0 END as is_leader
    //                        FROM group_members gm
    //                        JOIN groups g ON gm.group_id = g.group_id
    //                        JOIN students s ON gm.student_id = s.student_id
    //                        JOIN users u ON s.user_id = u.user_id
    //                        WHERE g.group_id = :group_id
    //                        ORDER BY is_leader DESC, u.full_name ASC";

    //         $stmtMem = $this->pdo->prepare($sqlMembers);
    //         $stmtMem->execute([':group_id' => $group['group_id']]);
    //         $members = $stmtMem->fetchAll(PDO::FETCH_ASSOC);

    //         // 5. Render View
    //         $this->render('groups/my_group', [
    //             'group' => $group,
    //             'members' => $members,
    //             'current_student_id' => $student['student_id']
    //         ]);
    //     } catch (\Exception $e) {
    //         $this->handleError($e, 'myGroup');
    //     }
    // }

    /**
     * Xử lý rời nhóm
     * URL: /quanlydoan/group/leave
     */
    public function leave(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi phương thức.'], 405);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $userModel = new \App\Models\UserModel($this->pdo);
            $user = $userModel->findByAccountId($_SESSION['account_id']);
            $student = $this->studentModel->findByUserId($user['user_id']);
            $groupId = (int)($_POST['group_id'] ?? 0);

            // Kiểm tra xem sinh viên có thuộc nhóm này không
            $sqlCheck = "SELECT g.leader_id, p.status 
                         FROM group_members gm
                         JOIN groups g ON gm.group_id = g.group_id
                         JOIN projects p ON g.project_id = p.project_id
                         WHERE gm.group_id = ? AND gm.student_id = ?";
            $stmt = $this->pdo->prepare($sqlCheck);
            $stmt->execute([$groupId, $student['student_id']]);
            $groupInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$groupInfo) {
                $this->jsonResponse(['success' => false, 'message' => 'Bạn không thuộc nhóm này.'], 403);
                return;
            }

            // Logic 1: Trưởng nhóm không được rời (phải chuyển quyền trước)
            if ($groupInfo['leader_id'] == $student['student_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'Bạn là Trưởng nhóm. Vui lòng chuyển quyền trước khi rời.'], 400);
                return;
            }

            // Logic 2: Không được rời khi đồ án đã nộp hoặc hoàn thành
            if (in_array($groupInfo['status'], ['DaNopBaoCao', 'DaBaoVe', 'HoanThanh'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Không thể rời nhóm khi đồ án đã nộp hoặc kết thúc.'], 400);
                return;
            }

            // Thực hiện xóa khỏi bảng group_members
            $this->groupMemberModel->removeMember($groupId, $student['student_id']);

            $this->jsonResponse(['success' => true, 'message' => 'Đã rời nhóm thành công.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // Trong class GroupController

    /**
     * Chức năng Chat dành cho Giảng viên (hoặc Admin truy cập cụ thể)
     * URL: /quanlydoan/group/chat/{id}
     */
    public function chat(int $groupId): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['account_id'])) {
            header('Location: /quanlydoan/auth/login');
            exit;
        }

        $role = $_SESSION['role'] ?? '';
        $user = $this->userModel->findByAccountId($_SESSION['account_id']);
        $currentUserId = $user['user_id'];
        $currentStudentId = null; // Mặc định null nếu là GV

        try {
            // 2. Kiểm tra quyền truy cập
            if ($role === 'teacher') {
                // Nếu là GV: Kiểm tra xem nhóm này có thuộc đồ án do GV này hướng dẫn không
                $lecturer = $this->lecturerModel->findByUserIdLecturer($currentUserId);

                $sqlCheck = "SELECT 1 
                             FROM groups g
                             JOIN projects p ON g.project_id = p.project_id
                             WHERE g.group_id = :group_id AND p.lecturer_id = :lecturer_id";

                $stmt = $this->pdo->prepare($sqlCheck);
                $stmt->execute([
                    ':group_id' => $groupId,
                    ':lecturer_id' => $lecturer['lecturer_id']
                ]);

                if (!$stmt->fetchColumn()) {
                    echo "Bạn không có quyền truy cập nhóm này (Không thuộc đồ án bạn hướng dẫn).";
                    return;
                }
            } elseif ($role === 'student') {
                // Nếu là SV: Kiểm tra xem có thuộc nhóm này không
                $student = $this->studentModel->findByUserId($currentUserId);
                $currentStudentId = $student['student_id'];

                if (!$this->groupMemberModel->isMember($groupId, $currentStudentId)) {
                    echo "Bạn không phải thành viên nhóm này.";
                    return;
                }
            }

            // 3. Lấy thông tin nhóm (Tái sử dụng query)
            $sqlGroup = "SELECT g.*, p.title as project_title, p.status as project_status, 
                                u_lect.full_name as lecturer_name, p.max_students
                         FROM groups g
                         JOIN projects p ON g.project_id = p.project_id
                         LEFT JOIN lecturers l ON p.lecturer_id = l.lecturer_id
                         LEFT JOIN users u_lect ON l.user_id = u_lect.user_id
                         WHERE g.group_id = :group_id LIMIT 1";

            $stmtGroup = $this->pdo->prepare($sqlGroup);
            $stmtGroup->execute([':group_id' => $groupId]);
            $group = $stmtGroup->fetch(PDO::FETCH_ASSOC);

            if (!$group) {
                echo "Nhóm không tồn tại.";
                return;
            }

            // 4. Lấy thành viên và render view
            $members = $this->groupMemberModel->getMembers($groupId);

            // Render lại view my_group nhưng truyền thêm biến role để ẩn hiện nút
            $this->render('groups/my_group', [
                'group' => $group,
                'members' => $members,
                'current_user_id' => $currentUserId,
                'current_student_id' => $currentStudentId, // Null nếu là GV
                'is_teacher' => ($role === 'teacher') // Cờ đánh dấu là GV
            ]);
        } catch (\Exception $e) {
            $this->handleError($e, 'chat');
        }
    }
}
