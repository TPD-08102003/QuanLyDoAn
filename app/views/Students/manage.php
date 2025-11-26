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
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-auto me-auto">
                <form method="GET" action="/quanlydoan/Student/manage" class="row g-3 align-items-center">
                    <div class="col-md-auto" style="width: 250px;">
                        <input type="text" class="form-control" name="keyword" placeholder="Tìm kiếm theo MSSV hoặc Họ tên" value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Tìm kiếm</button>
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
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="bi bi-person-add me-1"></i> Thêm SV
                    </button>
                    <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#exportStudentModal">
                        <i class="bi bi-cloud-download"></i> Xuất Excel
                    </button>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importStudentModal">
                        <i class="bi bi-cloud-upload"></i> Nhập Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">
        <span>Danh sách sinh viên (<?php echo $totalStudents ?? 0; ?>)</span>
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
                    <?php if (!empty($students) && is_array($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <span class="fw-medium text-dark"><?php echo htmlspecialchars($student['mssv'] ?? 'Chưa có MSSV'); ?></span>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></div>
                                    <?php if (!empty($student['email'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-dark">
                                        <?php
                                        $gender = $student['gender'] ?? null;
                                        echo match ($gender) {
                                            'Nam' => 'Nam',
                                            'Nữ' => 'Nữ',
                                            'Khác' => 'Khác',
                                            default => 'Chưa cập nhật'
                                        };
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark"><?php echo htmlspecialchars($student['class_name'] ?? 'Chưa có lớp'); ?></span>
                                </td>
                                <td>
                                    <span class="text-dark"><?php echo htmlspecialchars($student['faculty_name'] ?? 'Chưa có khoa'); ?></span>
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
                                            data-student-id="<?php echo $student['student_id'] ?? $student['id'] ?? 0; ?>"
                                            onclick="loadStudentDetails(<?php echo $student['student_id'] ?? $student['id'] ?? 0; ?>)">
                                            <i class="bi bi-eye-fill"></i>
                                            <span class="btn-text">Xem</span>
                                        </button>


                                        <!-- Nút Chỉnh sửa -->
                                        <button type="button"
                                            class="btn btn-warning btn-action text-white"
                                            title="Chỉnh sửa thông tin"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editStudentModal"
                                            onclick="loadStudentForEdit(<?php echo $student['student_id'] ?? $student['id'] ?? 0; ?>)">
                                            <i class="bi bi-pencil-fill"></i>
                                            <span class="btn-text">Sửa</span>
                                        </button>

                                        <!-- Nút Xóa -->
                                        <button type="button"
                                            class="btn btn-danger btn-action"
                                            title="Xóa sinh viên"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteStudentModal"
                                            data-student-id="<?php echo $student['student_id'] ?? $student['id'] ?? 0; ?>"
                                            data-student-name="<?php echo htmlspecialchars($student['full_name'] ?? ''); ?>"
                                            data-student-mssv="<?php echo htmlspecialchars($student['mssv'] ?? ''); ?>">
                                            <i class="bi bi-trash-fill"></i>
                                            <span class="btn-text">Xóa</span>
                                        </button>
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
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Đang tải thông tin...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-warning text-white" onclick="switchToEditMode()">Chỉnh sửa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal chỉnh sửa sinh viên -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
                            <input type="text" class="form-control" id="edit_mssv" name="mssv" readonly style="background-color: #f8f9fa;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select class="form-select" id="edit_gender" name="gender">
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác">Khác</option>
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
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" class="form-control" id="edit_address" name="address">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lớp</label>
                            <input type="text" class="form-control" id="edit_class_name" readonly style="background-color: #f8f9fa;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Khoa</label>
                            <input type="text" class="form-control" id="edit_faculty_name" readonly style="background-color: #f8f9fa;">
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
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xác nhận xóa</button> <!-- Thay a href thành button -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Nhập Excel -->
<div class="modal fade" id="importStudentModal" tabindex="-1" aria-labelledby="importStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importStudentModalLabel">Nhập Sinh viên từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importForm">
                    <div class="mb-3">
                        <label for="excelFile" class="form-label">Chọn file Excel (.xlsx)</label>
                        <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                        <small class="text-muted">File phải theo định dạng mẫu: MSSV, Họ tên, Giới tính, Lớp, Email, SĐT.</small>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="updateExisting" name="updateExisting">
                        <label class="form-check-label" for="updateExisting">Cập nhật thông tin nếu sinh viên đã tồn tại (dựa trên MSSV)</label>
                    </div>
                    <a href="/quanlydoan/Student/downloadTemplate" class="btn btn-outline-info mb-3" target="_blank">
                        <i class="bi bi-download me-1"></i> Tải file mẫu
                    </a>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="importButton">Nhập dữ liệu</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm sinh viên -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
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
                    <div class="col-md-6"> <!-- THÊM MỚI: Trường username -->
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_username" name="username" required placeholder="Nhập username (mặc định: MSSV)">
                        <div class="invalid-feedback" id="add_username_error"></div>
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
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Khác">Khác</option>
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
                            <?php if (isset($classes) && is_array($classes)): ?>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['class_id']; ?>" data-faculty-name="<?php echo htmlspecialchars($class['faculty_name'] ?? 'N/A'); ?>">
                                        <?php
                                        echo htmlspecialchars($class['class_name']);
                                        if (!empty($class['faculty_name'])) {
                                            echo ' (' . htmlspecialchars($class['faculty_name']) . ')';
                                        }
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
                    <button type="submit" class="btn btn-success text-white" id="submitAddStudentBtn">
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
    .btn-action {
        border-radius: 4px;
        margin: 0 2px;
        border: 1px solid #dee2e6;
    }

    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-action .btn-text {
        margin-left: 4px;
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

    .table td {
        color: #000;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Khởi tạo tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Xử lý form thêm sinh viên
        const addStudentForm = document.getElementById('addStudentForm');
        const addStudentModal = document.getElementById('addStudentModal');
        const modalInstance = addStudentModal ? new bootstrap.Modal(addStudentModal) : null;

        const alertPlaceholder = document.createElement('div');
        alertPlaceholder.className = 'mb-3';

        if (addStudentForm) {
            addStudentForm.prepend(alertPlaceholder);

            addStudentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                alertPlaceholder.innerHTML = '';

                const formData = new FormData(addStudentForm);
                const submitButton = addStudentForm.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.textContent;

                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';

                fetch('/quanlydoan/Student/store', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
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
                            showAlert(data.message, 'success');
                            window.location.reload();
                        } else {
                            showAlertInForm(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        showAlertInForm(`Lỗi hệ thống hoặc kết nối: ${error.message}`, 'danger');
                        console.error('Error submitting form:', error);
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.textContent = originalButtonText;
                    });
            });
        }

        // Xử lý chọn lớp -> hiển thị khoa
        const classSelect = document.getElementById('add_class_id');
        const facultyInput = document.getElementById('add_faculty_name');

        function updateFacultyField() {
            const selectedOption = classSelect.options[classSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const facultyName = selectedOption.getAttribute('data-faculty-name');
                facultyInput.value = facultyName || 'Không rõ khoa';
            } else {
                facultyInput.value = 'Sẽ tự động hiển thị khi chọn lớp';
            }
        }

        if (classSelect) {
            classSelect.addEventListener('change', updateFacultyField);
            updateFacultyField();
        }

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

        // Xử lý nhập Excel
        document.getElementById('importButton').addEventListener('click', function() {
            const fileInput = document.getElementById('excelFile');
            if (!fileInput.files.length) {
                alert('Vui lòng chọn file Excel để nhập!');
                return;
            }
            alert('Tính năng nhập Excel sẽ được tích hợp với backend!');
        });
    });

    // Hàm hiển thị thông báo trong form
    function showAlertInForm(message, type) {
        const alertPlaceholder = document.querySelector('#addStudentForm .mb-3');
        if (alertPlaceholder) {
            alertPlaceholder.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }
    }

    // Hàm hiển thị thông báo trên trang chính
    function showAlert(message, type) {
        const mainPlaceholder = document.querySelector('.page-title');
        if (mainPlaceholder) {
            document.querySelectorAll('.alert').forEach(alert => alert.remove());

            const newAlert = document.createElement('div');
            newAlert.className = `alert alert-${type} alert-dismissible fade show`;
            newAlert.role = 'alert';
            newAlert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            mainPlaceholder.after(newAlert);
        }
    }

    // Hàm load thông tin sinh viên để chỉnh sửa
    function loadStudentForEdit(studentId) {
        if (!studentId || studentId === 0) {
            alert('Không tìm thấy ID sinh viên');
            return;
        }

        fetch(`/quanlydoan/Student/getStudentDetails/${studentId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Không thể tải thông tin sinh viên');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const s = data.student;
                    document.getElementById('edit_student_id').value = s.student_id || '';
                    document.getElementById('edit_mssv').value = s.mssv || 'Chưa có MSSV';
                    document.getElementById('edit_full_name').value = s.full_name || '';
                    document.getElementById('edit_gender').value = s.gender || '';
                    document.getElementById('edit_date_of_birth').value = s.date_of_birth || '';
                    document.getElementById('edit_phone_number').value = s.phone_number || '';
                    document.getElementById('edit_email').value = s.email || '';
                    document.getElementById('edit_address').value = s.address || '';
                    document.getElementById('edit_class_name').value = s.class_name || 'Chưa có lớp';
                    document.getElementById('edit_faculty_name').value = s.faculty_name || 'Chưa có khoa';
                } else {
                    alert('Không thể tải thông tin sinh viên: ' + (data.message || 'Lỗi không xác định'));
                }
            })
            .catch(err => {
                console.error('Error loading student for edit:', err);
                alert('Lỗi kết nối máy chủ khi tải thông tin sinh viên');
            });
    }

    // Hàm load chi tiết sinh viên
    function loadStudentDetails(studentId) {
        if (!studentId || studentId === 0) {
            alert('Không tìm thấy ID sinh viên');
            return;
        }

        const contentDiv = document.getElementById('studentDetailsContent');
        contentDiv.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Đang tải thông tin...</p>
            </div>
        `;

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

                    const formatDate = (dateString) => {
                        if (!dateString) return 'Chưa cập nhật';
                        return new Date(dateString).toLocaleDateString('vi-VN');
                    };

                    contentDiv.innerHTML = `
                        <div class="text-center mb-4">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-person-fill" style="font-size: 2rem;"></i>
                            </div>
                            <h4 class="mb-1 mt-3">${student.full_name || 'Chưa có tên'}</h4>
                            <span class="badge bg-secondary fs-6">${student.mssv || 'Chưa có MSSV'}</span>
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
                                                <td>${student.gender || 'Chưa cập nhật'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Lớp:</strong></td>
                                                <td>${student.class_name || 'Chưa có lớp'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Khoa:</strong></td>
                                                <td>${student.faculty_name || 'Chưa có khoa'}</td>
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

    // Hàm chuyển từ chế độ xem sang chế độ chỉnh sửa
    function switchToEditMode() {
        const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewStudentModal'));
        const editModal = new bootstrap.Modal(document.getElementById('editStudentModal'));

        viewModal.hide();
        const studentId = document.querySelector('#viewStudentModal [data-student-id]')?.getAttribute('data-student-id');
        if (studentId) {
            setTimeout(() => {
                loadStudentForEdit(studentId);
                editModal.show();
            }, 500);
        }
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
    addStudentForm.addEventListener('submit', function(e) {
        if (!add_mssv.value.trim()) {
            e.preventDefault();
            add_mssv_error.textContent = 'MSSV không được để trống.';
            add_mssv.classList.add('is-invalid');
            return;
        }
        if (!add_class_id.value) {
            e.preventDefault();
            add_class_id_error.textContent = 'Vui lòng chọn lớp.';
            add_class_id.classList.add('is-invalid');
            return;
        }
        const usernameInput = document.getElementById('add_username');
        if (!usernameInput.value.trim()) {
            e.preventDefault();
            document.getElementById('add_username_error').textContent = 'Username không được để trống.';
            usernameInput.classList.add('is-invalid');
            return;
        }
    });
    document.getElementById('add_mssv').addEventListener('input', function() {
        const usernameInput = document.getElementById('add_username');
        if (!usernameInput.value.trim()) { // Chỉ điền nếu username còn rỗng
            usernameInput.value = this.value;
        }
    });
    document.getElementById('deleteStudentModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const studentId = button.getAttribute('data-student-id');
        const studentName = button.getAttribute('data-student-name');
        const studentMSSV = button.getAttribute('data-student-mssv');

        document.getElementById('deleteStudentName').textContent = studentName;
        document.getElementById('deleteStudentMSSV').textContent = studentMSSV;

        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.onclick = function() {
            fetch(`/quanlydoan/Student/destroy/${studentId}`, {
                    method: 'POST' // Hoặc GET nếu controller hỗ trợ
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        window.location.reload(); // Reload trang để cập nhật danh sách
                    } else {
                        showAlert(data.message || 'Xóa thất bại', 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Lỗi kết nối: ' + err.message, 'danger');
                })
                .finally(() => {
                    bootstrap.Modal.getInstance(document.getElementById('deleteStudentModal')).hide();
                });
        };
    });
    document.getElementById('importButton').addEventListener('click', function() {
        const form = document.getElementById('importForm');
        const formData = new FormData(form);
        const button = this;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang nhập...';

        fetch('/quanlydoan/Student/import', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Lỗi nhập dữ liệu');
                }
            })
            .catch(error => {
                alert('Lỗi kết nối: ' + error.message);
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = 'Nhập dữ liệu';
                bootstrap.Modal.getInstance(document.getElementById('importStudentModal')).hide();
            });
    });
</script>