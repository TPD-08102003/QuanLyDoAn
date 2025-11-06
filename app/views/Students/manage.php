<h1 class="page-title h2">Quản lý Sinh viên</h1>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['message_type']); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title">Tìm kiếm & Lọc</h5>
        <div class="row g-3 align-items-center">

            <div class="col-12 col-md-auto me-auto">
                <form method="GET" action="/quanlydoan/Student/manage" class="row g-3 align-items-center">
                    <div class="col-md-auto" style="width: 250px;">
                        <input type="text" class="form-control" name="keyword" placeholder="Tìm kiếm theo MSSV hoặc Họ tên" value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-teal text-white"><i class="bi bi-search me-1"></i> Tìm kiếm</button>
                    </div>

                    <div class="col-auto">
                        <?php if (isset($_GET['keyword']) && $_GET['keyword']): ?>
                            <a href="/quanlydoan/Student/manage" class="btn btn-outline-secondary">Xóa tìm kiếm</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="col-12 col-md-auto ms-md-auto">
                <div class="d-flex gap-2 justify-content-end">

                    <button type="button" class="btn btn-purple text-white" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="bi bi-person-add me-1"></i> Thêm SV
                    </button>
                    <!-- <a href="/quanlydoan/Student/create" class="btn btn-purple text-white">
                        <i class="bi bi-person-add me-1"></i> Thêm SV
                    </a> -->

                    <a href="/quanlydoan/Student/export" class="btn btn-success" title="Xuất file Excel">
                        <i class="bi bi-file-earmark-excel me-1"></i> Xuất Excel
                    </a>

                    <button class="btn btn-orange text-white" data-bs-toggle="modal" data-bs-target="#importStudentModal">
                        <i class="bi bi-upload me-1"></i> Nhập Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span>Danh sách sinh viên (<?php echo $totalStudents ?? 0; ?>)</span>
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="toggleCompactView">
            <label class="form-check-label" for="toggleCompactView">Chế độ xem gọn</label>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">MSSV</th>
                        <th scope="col">Họ và Tên</th>
                        <th scope="col">Giới tính</th>
                        <th scope="col">Lớp</th>
                        <th scope="col">Khoa</th>
                        <th scope="col">Tình trạng</th>
                        <th scope="col" class="text-center" width="180">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="student-avatar-small me-2">
                                            <?php
                                            $nameParts = explode(' ', $student['full_name'] ?? '');
                                            $initials = '';
                                            if (count($nameParts) > 0) {
                                                $initials .= substr($nameParts[0], 0, 1);
                                                if (count($nameParts) > 1) {
                                                    $initials .= substr($nameParts[count($nameParts) - 1], 0, 1);
                                                }
                                            }
                                            echo strtoupper($initials);
                                            ?>
                                        </div>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($student['mssv'] ?? 'N/A'); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium"><?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></div>
                                    <?php if (!empty($student['email'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $gender = $student['gender'] ?? null;
                                    $genderDisplay = match ($gender) {
                                        'male' => ['text' => 'Nam', 'icon' => 'bi-gender-male', 'color' => 'primary'],
                                        'female' => ['text' => 'Nữ', 'icon' => 'bi-gender-female', 'color' => 'danger'],
                                        'other' => ['text' => 'Khác', 'icon' => 'bi-gender-ambiguous', 'color' => 'success'],
                                        default => ['text' => 'Chưa cập nhật', 'icon' => 'bi-gender-trans', 'color' => 'secondary']
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $genderDisplay['color']; ?>">
                                        <i class="bi <?php echo $genderDisplay['icon']; ?> me-1"></i>
                                        <?php echo $genderDisplay['text']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($student['faculty_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status = $student['status'] ?? 'active';
                                    $badge_class = $status === 'active' ? 'bg-success' : 'bg-danger';
                                    $status_text = $status === 'active' ? 'Hoạt động' : 'Khóa';
                                    echo '<span class="badge ' . $badge_class . '">' . $status_text . '</span>';
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Nút Xem chi tiết -->
                                        <button type="button"
                                            class="btn btn-primary btn-action"
                                            title="Xem chi tiết"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewStudentModal"
                                            data-student-id="<?php echo $student['student_id']; ?>"
                                            onclick="loadStudentDetails(<?php echo $student['student_id']; ?>)">
                                            <i class="bi bi-eye-fill"></i>
                                            <span class="btn-text">Xem</span>
                                        </button>

                                        <!-- Nút Chỉnh sửa -->
                                        <button type="button"
                                            class="btn btn-warning btn-action text-white"
                                            title="Chỉnh sửa thông tin"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editStudentModal"
                                            onclick="loadStudentForEdit(<?php echo $student['student_id']; ?>)">
                                            <i class="bi bi-pencil-fill"></i>
                                            <span class="btn-text">Sửa</span>
                                        </button>


                                        <!-- Nút Xóa -->
                                        <button type="button"
                                            class="btn btn-danger btn-action"
                                            title="Xóa sinh viên"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteStudentModal"
                                            data-student-id="<?php echo $student['student_id']; ?>"
                                            data-student-name="<?php echo htmlspecialchars($student['full_name'] ?? ''); ?>"
                                            data-student-mssv="<?php echo htmlspecialchars($student['mssv'] ?? ''); ?>">
                                            <i class="bi bi-trash-fill"></i>
                                            <span class="btn-text">Xóa</span>
                                        </button>
                                    </div>

                                    <!-- Nút hành động nhanh (chỉ hiện trên mobile) -->
                                    <div class="dropdown d-md-none mt-2">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100"
                                            type="button"
                                            data-bs-toggle="dropdown">
                                            Hành động
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button class="dropdown-item"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewStudentModal"
                                                    onclick="loadStudentDetails(<?php echo $student['student_id']; ?>)">
                                                    <i class="bi bi-eye-fill me-2 text-primary"></i>Xem chi tiết
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button"
                                                    class="btn btn-warning btn-action text-white"
                                                    title="Chỉnh sửa thông tin"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editStudentModal"
                                                    onclick="loadStudentForEdit(<?php echo $student['student_id']; ?>)">
                                                    <i class="bi bi-pencil-fill"></i>
                                                    <span class="btn-text">Sửa</span>
                                                </button>

                                            </li>

                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <button class="dropdown-item text-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteStudentModal"
                                                    data-student-id="<?php echo $student['student_id']; ?>"
                                                    data-student-name="<?php echo htmlspecialchars($student['full_name'] ?? ''); ?>"
                                                    data-student-mssv="<?php echo htmlspecialchars($student['mssv'] ?? ''); ?>">
                                                    <i class="bi bi-trash-fill me-2"></i>Xóa
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-people display-4 d-block mb-2"></i>
                                Không tìm thấy sinh viên nào.
                                <?php if (isset($_GET['keyword']) && $_GET['keyword']): ?>
                                    <div class="mt-2">
                                        <a href="/quanlydoan/Student/manage" class="btn btn-sm btn-outline-primary">Xóa bộ lọc tìm kiếm</a>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết sinh viên -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewStudentModalLabel">Thông tin chi tiết sinh viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="studentDetailsContent">
                <!-- Nội dung sẽ được load bằng AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Đang tải thông tin...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <a href="#" class="btn btn-warning text-white" id="editStudentBtn">Chỉnh sửa</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa sinh viên -->
<div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteStudentModalLabel">Xác nhận xóa sinh viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Cảnh báo:</strong> Hành động này không thể hoàn tác!
                </div>
                <p>Bạn có chắc chắn muốn xóa sinh viên <strong id="deleteStudentName"></strong> (<span id="deleteStudentMSSV"></span>)?</p>
                <p class="text-muted small">Tất cả thông tin liên quan đến sinh viên này sẽ bị xóa vĩnh viễn.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <a href="#" class="btn btn-danger" id="confirmDeleteBtn">Xác nhận xóa</a>
            </div>
        </div>
    </div>
</div>
<!-- Modal chỉnh sửa sinh viên -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editStudentModalLabel">Chỉnh sửa thông tin sinh viên</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStudentForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="student_id" id="edit_student_id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">MSSV</label>
                            <input type="text" class="form-control" id="edit_mssv" name="mssv" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select class="form-select" id="edit_gender" name="gender">
                                <option value="">-- Chọn giới tính --</option>
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="edit_phone_number" name="phone_number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" class="form-control" id="edit_address" name="address">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lớp</label>
                            <input type="text" class="form-control" id="edit_class_name" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Khoa</label>
                            <input type="text" class="form-control" id="edit_faculty_name" disabled>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning text-white">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal nhập Excel -->
<div class="modal fade" id="importStudentModal" tabindex="-1" aria-labelledby="importStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-orange text-white">
                <h5 class="modal-title" id="importStudentModalLabel">Nhập sinh viên từ Excel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="excelFile" class="form-label">Chọn file Excel</label>
                        <input class="form-control" type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="updateExisting" name="updateExisting">
                            <label class="form-check-label" for="updateExisting">
                                Cập nhật sinh viên đã tồn tại
                            </label>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            File Excel cần có các cột: MSSV, Họ tên, Giới tính, Lớp, Email, Số điện thoại.
                            <a href="/quanlydoan/Student/downloadTemplate" class="alert-link">Tải file mẫu</a>
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="importButton">Nhập dữ liệu</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-purple text-white">
                <h5 class="modal-title" id="addStudentModalLabel">Thêm Sinh viên mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStudentForm" class="needs-validation" novalidate>
                <div class="modal-body row g-3">

                    <div class="col-md-6">
                        <label class="form-label">MSSV <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_mssv" name="mssv" required placeholder="Nhập mã số sinh viên">
                        <div class="invalid-feedback" id="add_mssv_error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_full_name" name="full_name" required placeholder="Nhập họ và tên">
                        <div class="invalid-feedback" id="add_full_name_error"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="add_email" name="email" required placeholder="Nhập email">
                        <div class="invalid-feedback" id="add_email_error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giới tính <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_gender" name="gender" required>
                            <option value="">-- Chọn giới tính --</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                        <div class="invalid-feedback" id="add_gender_error"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Ngày sinh</label>
                        <input type="date" class="form-control" id="add_date_of_birth" name="date_of_birth">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="add_phone_number" name="phone_number" placeholder="Nhập số điện thoại">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" class="form-control" id="add_address" name="address" placeholder="Nhập địa chỉ">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Lớp <span class="text-danger">*</span></label>

                        <select class="form-select" id="add_class_id" name="class_id" required>
                            <option value="">-- Chọn lớp --</option>
                            <?php
                            // KIỂM TRA ĐIỀU KIỆN IF NÀY: 
                            // $classes phải là mảng và không rỗng
                            if (isset($classes) && is_array($classes)):
                                foreach ($classes as $class):
                            ?>
                                    <option
                                        value="<?php echo $class['class_id']; ?>"
                                        data-faculty-name="<?php echo htmlspecialchars($class['faculty_name'] ?? 'N/A'); ?>">
                                        <?php
                                        echo htmlspecialchars($class['class_name']);
                                        // Hiển thị tên khoa nếu có
                                        if (!empty($class['faculty_name'])) {
                                            echo ' (' . htmlspecialchars($class['faculty_name']) . ')';
                                        }
                                        ?>
                                    </option>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                        <div class="invalid-feedback" id="add_class_id_error"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Khoa</label>
                        <input type="text" class="form-control" id="add_faculty_name" disabled placeholder="Sẽ tự động hiển thị khi chọn lớp">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_status" name="status" required>
                            <option value="active" selected>Hoạt động</option>
                            <option value="inactive">Khóa</option>
                        </select>
                        <div class="invalid-feedback" id="add_status_error"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Năm học</label>
                        <input type="text" class="form-control" id="add_academic_year" name="academic_year" placeholder="Ví dụ: 2023-2024">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-purple text-white" id="submitAddStudentBtn">
                        <i class="bi bi-floppy-fill me-1"></i> Thêm Sinh viên
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($totalPages) && $totalPages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php
            $currentPage = $page ?? 1;
            $keyword = htmlspecialchars($_GET['keyword'] ?? '');
            ?>
            <li class="page-item <?php echo $currentPage == 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="/quanlydoan/Student/manage?page=<?php echo $currentPage - 1; ?>&keyword=<?php echo $keyword; ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                    <a class="page-link" href="/quanlydoan/Student/manage?page=<?php echo $i; ?>&keyword=<?php echo $keyword; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $currentPage == $totalPages ? 'disabled' : ''; ?>">
                <a class="page-link" href="/quanlydoan/Student/manage?page=<?php echo $currentPage + 1; ?>&keyword=<?php echo $keyword; ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<style>
    .student-avatar-small {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-action {
        position: relative;
        transition: all 0.3s ease;
        border-radius: 6px;
        margin: 0 2px;
        border: none;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-action .btn-text {
        margin-left: 4px;
    }

    /* Màu sắc mới cho các nút */
    .btn-teal {
        background-color: #20c997;
        border-color: #20c997;
    }

    .btn-teal:hover {
        background-color: #199d76;
        border-color: #199d76;
    }

    .btn-purple {
        background-color: #6f42c1;
        border-color: #6f42c1;
    }

    .btn-purple:hover {
        background-color: #5a359c;
        border-color: #5a359c;
    }

    .btn-orange {
        background-color: #fd7e14;
        border-color: #fd7e14;
    }

    .btn-orange:hover {
        background-color: #dc6502;
        border-color: #dc6502;
    }

    .bg-orange {
        background-color: #fd7e14 !important;
    }

    /* Ẩn text trên mobile */
    @media (max-width: 768px) {
        .btn-action .btn-text {
            display: none;
        }

        .btn-action {
            padding: 6px 8px;
        }
    }

    /* Chế độ xem gọn */
    .compact-view .student-avatar-small {
        width: 28px;
        height: 28px;
        font-size: 0.7rem;
    }

    .compact-view .btn-action .btn-text {
        display: none;
    }

    .compact-view table {
        font-size: 0.875rem;
    }

    /* Modal styles */
    .modal-header {
        border-bottom: none;
    }

    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .student-details-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Khởi tạo tooltip và các chức năng khác (Không sửa)
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        const toggleCompactView = document.getElementById('toggleCompactView');
        const tableContainer = document.querySelector('.table-responsive');

        if (toggleCompactView) {
            toggleCompactView.addEventListener('change', function() {
                if (this.checked) {
                    tableContainer.classList.add('compact-view');
                } else {
                    tableContainer.classList.remove('compact-view');
                }
            });
        }

        // --- BẮT ĐẦU LOGIC XỬ LÝ FORM AJAX ---

        const form = document.getElementById('addStudentForm');
        const modal = document.getElementById('addStudentModal');
        // Sử dụng Bootstrap Modal instance để điều khiển
        const modalInstance = modal ? new bootstrap.Modal(modal) : null;

        // Vị trí hiển thị alert trong modal
        const alertPlaceholder = document.createElement('div');
        alertPlaceholder.className = 'mb-3'; // Thêm khoảng cách dưới

        // CHÚ Ý: Phải kiểm tra form tồn tại trước khi dùng prepend
        if (form) {
            form.prepend(alertPlaceholder);

            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Rất quan trọng: Ngăn chặn submit form truyền thống

                // Reset alert
                alertPlaceholder.innerHTML = '';

                const formData = new FormData(form);
                const submitButton = form.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.textContent;

                // Hiển thị trạng thái loading
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';

                // Gửi AJAX
                fetch('/quanlydoan/Student/store', {
                        // Đảm bảo đúng URL endpoint
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // Xử lý lỗi HTTP (400, 405, 409, 500)
                        if (!response.ok) {
                            return response.json().then(errorData => {
                                throw new Error(errorData.message || 'Lỗi server không xác định.');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (modalInstance) {
                                modalInstance.hide();
                            }

                            // Hiển thị alert thành công ngay trong trang chính
                            showAlert(data.message, 'success');

                            // Tải lại trang để cập nhật bảng dữ liệu
                            window.location.reload();
                        } else {
                            // Xử lý thất bại (lỗi validation, trùng lặp...)
                            showAlertInForm(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        // Xử lý lỗi mạng/hệ thống
                        showAlertInForm(`Lỗi hệ thống hoặc kết nối: ${error.message}`, 'danger');
                        console.error('Error submitting form:', error);
                    })
                    .finally(() => {
                        // Phục hồi nút submit
                        submitButton.disabled = false;
                        submitButton.textContent = originalButtonText;
                    });
            });
        }

        // Hàm hiển thị thông báo ngay trong modal
        function showAlertInForm(message, type) {
            alertPlaceholder.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        }

        // Hàm hiển thị thông báo trên trang chính
        function showAlert(message, type) {
            const mainPlaceholder = document.querySelector('.page-title');
            if (mainPlaceholder) {
                // Xóa alert cũ nếu có
                document.querySelectorAll('.alert').forEach(alert => alert.remove());

                const newAlert = document.createElement('div');
                newAlert.className = `alert alert-${type} alert-dismissible fade show`;
                newAlert.role = 'alert';
                newAlert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
                // Chèn alert ngay sau tiêu đề trang (Giả định .page-title tồn tại)
                mainPlaceholder.after(newAlert);

                // Tự động đóng alert sau 5s (ví dụ)
                // setTimeout(() => newAlert.classList.remove('show'), 5000); 
            }
        }

        // Xử lý nhập file Excel
        document.getElementById('importButton').addEventListener('click', function() {
            const fileInput = document.getElementById('excelFile');
            if (!fileInput.files.length) {
                alert('Vui lòng chọn file Excel để nhập!');
                return;
            }
            alert('Tính năng nhập Excel sẽ được tích hợp với backend!');
        });

        // Xử lý modal xóa
        const deleteStudentModal = document.getElementById('deleteStudentModal');
        if (deleteStudentModal) {
            deleteStudentModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const studentId = button.getAttribute('data-student-id');
                const studentName = button.getAttribute('data-student-name');
                const studentMSSV = button.getAttribute('data-student-mssv');

                document.getElementById('deleteStudentName').textContent = studentName;
                document.getElementById('deleteStudentMSSV').textContent = studentMSSV;
                document.getElementById('confirmDeleteBtn').href = `/quanlydoan/Student/destroy/${studentId}`;
            });
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('add_class_id');
        const facultyInput = document.getElementById('add_faculty_name');

        function updateFacultyField() {
            // Lấy option đang được chọn
            const selectedOption = classSelect.options[classSelect.selectedIndex];

            // Kiểm tra nếu có option được chọn (value không rỗng)
            if (selectedOption && selectedOption.value) {
                // Đọc giá trị từ data-faculty-name đã được PHP thêm vào
                const facultyName = selectedOption.getAttribute('data-faculty-name');
                facultyInput.value = facultyName || 'Không rõ khoa';
            } else {
                // Đặt lại giá trị mặc định nếu chọn "-- Chọn lớp --"
                facultyInput.value = 'Sẽ tự động hiển thị khi chọn lớp';
            }
        }

        // 1. Lắng nghe sự kiện thay đổi trên dropdown Lớp
        classSelect.addEventListener('change', updateFacultyField);

        // 2. Chạy lần đầu để thiết lập giá trị ban đầu (tránh trường hợp F5 vẫn giữ giá trị)
        updateFacultyField();
    });

    // Hàm load thông tin sinh viên để chỉnh sửa
    function loadStudentForEdit(studentId) {
        fetch(`/quanlydoan/Student/getStudentDetails/${studentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const s = data.student;
                    document.getElementById('edit_student_id').value = s.student_id;
                    document.getElementById('edit_mssv').value = s.mssv;
                    document.getElementById('edit_full_name').value = s.full_name;
                    document.getElementById('edit_gender').value = s.gender || '';
                    document.getElementById('edit_date_of_birth').value = s.date_of_birth || '';
                    document.getElementById('edit_phone_number').value = s.phone_number || '';
                    document.getElementById('edit_address').value = s.address || '';
                    document.getElementById('edit_class_name').value = s.class_name || '';
                    document.getElementById('edit_faculty_name').value = s.faculty_name || '';
                }
            });
    }

    // Gửi form cập nhật
    document.getElementById('editStudentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const studentId = formData.get('student_id');

        fetch(`/quanlydoan/Student/update/${studentId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cập nhật thành công!');
                    location.reload();
                } else {
                    alert('Cập nhật thất bại: ' + (data.message || 'Lỗi không xác định'));
                }
            })
            .catch(err => {
                alert('Lỗi kết nối máy chủ');
                console.error(err);
            });
    });


    // Hàm load chi tiết sinh viên bằng AJAX
    function loadStudentDetails(studentId) {
        const contentDiv = document.getElementById('studentDetailsContent');
        const editBtn = document.getElementById('editStudentBtn');

        // Hiển thị loading
        contentDiv.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Đang tải thông tin...</p>
            </div>
        `;

        // Cập nhật link chỉnh sửa
        editBtn.href = `/quanlydoan/Student/edit/${studentId}`;

        // Gọi AJAX để lấy thông tin chi tiết
        fetch(`/quanlydoan/Student/getStudentDetails/${studentId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const student = data.student;

                    // Tạo avatar từ tên
                    const nameParts = student.full_name.split(' ');
                    let initials = '';
                    if (nameParts.length > 0) {
                        initials += nameParts[0].charAt(0);
                        if (nameParts.length > 1) {
                            initials += nameParts[nameParts.length - 1].charAt(0);
                        }
                    }

                    // Format ngày tháng
                    const formatDate = (dateString) => {
                        if (!dateString) return 'N/A';
                        return new Date(dateString).toLocaleDateString('vi-VN');
                    };

                    // Format giới tính với icon
                    const getGenderDisplay = (gender) => {
                        const genderMap = {
                            'male': {
                                text: 'Nam',
                                icon: 'bi-gender-male',
                                color: 'primary'
                            },
                            'female': {
                                text: 'Nữ',
                                icon: 'bi-gender-female',
                                color: 'danger'
                            },
                            'other': {
                                text: 'Khác',
                                icon: 'bi-gender-ambiguous',
                                color: 'success'
                            },
                            'default': {
                                text: 'Chưa cập nhật',
                                icon: 'bi-gender-trans',
                                color: 'secondary'
                            }
                        };
                        return genderMap[gender] || genderMap['default'];
                    };

                    const genderInfo = getGenderDisplay(student.gender);

                    contentDiv.innerHTML = `
                        <div class="text-center mb-4">
                            <div class="student-details-avatar">${initials.toUpperCase()}</div>
                            <h4 class="mb-1">${student.full_name}</h4>
                            <span class="badge bg-secondary fs-6">${student.mssv}</span>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary mb-3">
                                            <i class="bi bi-info-circle me-2"></i>Thông tin cơ bản
                                        </h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td width="120"><strong>Giới tính:</strong></td>
                                                <td>
                                                    <span class="badge bg-${genderInfo.color}">
                                                        <i class="bi ${genderInfo.icon} me-1"></i>
                                                        ${genderInfo.text}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Lớp:</strong></td>
                                                <td><span class="badge bg-light text-dark">${student.class_name || 'Chưa có'}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Khoa:</strong></td>
                                                <td><span class="badge bg-info">${student.faculty_name || 'Chưa có'}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td>${student.email || 'Chưa có'}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary mb-3">
                                            <i class="bi bi-telephone me-2"></i>Liên hệ & Khác
                                        </h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td width="120"><strong>SĐT:</strong></td>
                                                <td>${student.phone_number || 'Chưa có'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Địa chỉ:</strong></td>
                                                <td>${student.address || 'Chưa có'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Ngày sinh:</strong></td>
                                                <td>${student.date_of_birth ? formatDate(student.date_of_birth) : 'Chưa có'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Trạng thái:</strong></td>
                                                <td>
                                                    <span class="badge ${student.status === 'active' ? 'bg-success' : 'bg-danger'}">
                                                        ${student.status === 'active' ? 'Hoạt động' : 'Khóa'}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary mb-3">
                                            <i class="bi bi-calendar-event me-2"></i>Thông tin hệ thống
                                        </h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td width="120"><strong>Ngày tạo:</strong></td>
                                                <td>${formatDate(student.created_at)}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Cập nhật:</strong></td>
                                                <td>${formatDate(student.updated_at)}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    contentDiv.innerHTML = `
                        <div class="text-center text-danger py-4">
                            <i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>
                            <p>Không thể tải thông tin sinh viên.</p>
                            <small class="text-muted">${data.message || 'Đã xảy ra lỗi'}</small>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                contentDiv.innerHTML = `
                    <div class="text-center text-danger py-4">
                        <i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>
                        <p>Lỗi kết nối máy chủ.</p>
                        <small class="text-muted">Vui lòng thử lại sau.</small>
                    </div>
                `;
            });
    }
</script>