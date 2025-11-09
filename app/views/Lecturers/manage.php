<h1 class="page-title h2">Quản lý Giảng viên</h1>

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
                <form method="GET" action="/quanlydoan/Lecturer/manage" class="row g-3 align-items-center">
                    <div class="col-md-auto" style="width: 250px;">
                        <input type="text" class="form-control" name="keyword" placeholder="Tìm kiếm theo MSGV hoặc Họ tên" value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Tìm kiếm</button>
                    </div>
                    <div class="col-auto">
                        <?php if (isset($_GET['keyword']) && $_GET['keyword']): ?>
                            <a href="/quanlydoan/Lecturer/manage" class="btn btn-outline-secondary">Xóa tìm kiếm</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="col-12 col-md-auto ms-md-auto">
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addLecturerModal">
                        <i class="bi bi-person-add me-1"></i> Thêm GV
                    </button>
                    <a href="/quanlydoan/Lecturer/export" class="btn btn-info text-white">
                        <i class="bi bi-file-earmark-excel me-1"></i> Xuất Excel
                    </a>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importLecturerModal">
                        <i class="bi bi-upload me-1"></i> Nhập Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">
        <span>Danh sách giảng viên (<?php echo $totalLecturers ?? 0; ?>)</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">MSGV</th>
                        <th scope="col">Họ và Tên</th>
                        <th scope="col">Giới tính</th>
                        <th scope="col">Khoa</th>
                        <th scope="col">Chức vụ</th>
                        <th scope="col">Chuyên ngành</th>
                        <th scope="col">Kinh nghiệm</th>
                        <th scope="col">Tình trạng</th>
                        <th scope="col" class="text-center" width="160">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lecturers) && is_array($lecturers)): ?>
                        <?php foreach ($lecturers as $lecturer): ?>
                            <tr>
                                <td>
                                    <span class="fw-medium text-dark"><?php echo htmlspecialchars($lecturer['lecturer_code'] ?? 'Chưa có MSGV'); ?></span>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?php echo htmlspecialchars($lecturer['full_name'] ?? 'N/A'); ?></div>
                                    <?php if (!empty($lecturer['email'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($lecturer['email']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-dark">
                                        <?php
                                        $gender = $lecturer['gender'] ?? null;
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
                                    <span class="text-dark"><?php echo htmlspecialchars($lecturer['faculty_name'] ?? 'Chưa có khoa'); ?></span>
                                </td>
                                <td>
                                    <span class="text-dark"><?php echo htmlspecialchars($lecturer['position'] ?? 'Chưa có'); ?></span>
                                </td>
                                <td>
                                    <span class="text-dark"><?php echo htmlspecialchars($lecturer['specialization'] ?? 'Chưa có'); ?></span>
                                </td>
                                <td>
                                    <span class="text-dark"><?php echo $lecturer['years_of_experience'] ?? 0; ?> năm</span>
                                </td>
                                <td>
                                    <?php
                                    $status = $lecturer['status'] ?? 'active';
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
                                            data-bs-target="#viewLecturerModal"
                                            data-lecturer-id="<?php echo $lecturer['lecturer_id'] ?? 0; ?>"
                                            onclick="loadLecturerDetails(<?php echo $lecturer['lecturer_id'] ?? 0; ?>)">
                                            <i class="bi bi-eye-fill"></i>
                                            <span class="btn-text">Xem</span>
                                        </button>

                                        <!-- Nút Chỉnh sửa -->
                                        <button type="button"
                                            class="btn btn-warning btn-action text-white"
                                            title="Chỉnh sửa thông tin"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editLecturerModal"
                                            onclick="loadLecturerForEdit(<?php echo $lecturer['lecturer_id'] ?? 0; ?>)">
                                            <i class="bi bi-pencil-fill"></i>
                                            <span class="btn-text">Sửa</span>
                                        </button>

                                        <!-- Nút Xóa -->
                                        <button type="button"
                                            class="btn btn-danger btn-action"
                                            title="Xóa giảng viên"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteLecturerModal"
                                            data-lecturer-id="<?php echo $lecturer['lecturer_id'] ?? 0; ?>"
                                            data-lecturer-name="<?php echo htmlspecialchars($lecturer['full_name'] ?? ''); ?>"
                                            data-lecturer-msgv="<?php echo htmlspecialchars($lecturer['lecturer_code'] ?? ''); ?>">
                                            <i class="bi bi-trash-fill"></i>
                                            <span class="btn-text">Xóa</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-people display-4 d-block mb-2"></i>
                                Không tìm thấy giảng viên nào.
                                <?php if (isset($_GET['keyword']) && $_GET['keyword']): ?>
                                    <div class="mt-2">
                                        <a href="/quanlydoan/Lecturer/manage" class="btn btn-sm btn-outline-primary">Xóa bộ lọc tìm kiếm</a>
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

<!-- Modal xem chi tiết giảng viên -->
<div class="modal fade" id="viewLecturerModal" tabindex="-1" aria-labelledby="viewLecturerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLecturerModalLabel">Thông tin giảng viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="viewLecturerContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-warning text-white" onclick="switchToEditModeLecturer()">Chỉnh sửa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal chỉnh sửa giảng viên -->
<div class="modal fade" id="editLecturerModal" tabindex="-1" aria-labelledby="editLecturerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLecturerModalLabel">Chỉnh sửa thông tin giảng viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLecturerForm" method="POST">
                    <input type="hidden" name="lecturer_id" id="edit_lecturer_id">
                    <div class="mb-3">
                        <label for="edit_lecturer_code" class="form-label">MSGV <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_lecturer_code" name="lecturer_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_gender" class="form-label">Giới tính</label>
                        <select class="form-select" id="edit_gender" name="gender">
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_faculty_id" class="form-label">Khoa <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_faculty_id" name="faculty_id" required>
                            <option value="">Chọn khoa</option>
                            <?php foreach ($faculties as $faculty): ?>
                                <option value="<?php echo $faculty['faculty_id']; ?>"><?php echo htmlspecialchars($faculty['faculty_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_position" class="form-label">Chức vụ</label>
                        <input type="text" class="form-control" id="edit_position" name="position">
                    </div>
                    <div class="mb-3">
                        <label for="edit_specialization" class="form-label">Chuyên ngành</label>
                        <input type="text" class="form-control" id="edit_specialization" name="specialization">
                    </div>
                    <div class="mb-3">
                        <label for="edit_years_of_experience" class="form-label">Kinh nghiệm (năm)</label>
                        <input type="number" class="form-control" id="edit_years_of_experience" name="years_of_experience" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone_number" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="edit_phone_number" name="phone_number">
                    </div>
                    <div class="mb-3">
                        <label for="edit_date_of_birth" class="form-label">Ngày sinh</label>
                        <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth">
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Địa chỉ</label>
                        <input type="text" class="form-control" id="edit_address" name="address">
                    </div>
                    <button type="submit" class="btn btn-primary">Cập nhật giảng viên</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal xóa giảng viên -->
<div class="modal fade" id="deleteLecturerModal" tabindex="-1" aria-labelledby="deleteLecturerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLecturerModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa giảng viên <strong id="deleteLecturerName"></strong> (MSGV: <span id="deleteLecturerMSGV"></span>)?</p>
                <p class="text-muted">Hành động này không thể khôi phục.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteLecturerBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm giảng viên (mẫu đầy đủ) -->
<div class="modal fade" id="addLecturerModal" tabindex="-1" aria-labelledby="addLecturerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLecturerModalLabel">Thêm giảng viên mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addLecturerForm" method="POST" action="/quanlydoan/Lecturer/store">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_lecturer_code" class="form-label">MSGV <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_lecturer_code" name="lecturer_code" required>
                            <div class="invalid-feedback" id="add_lecturer_code_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_username" name="username" required>
                            <div class="invalid-feedback" id="add_username_error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_gender" class="form-label">Giới tính</label>
                            <select class="form-select" id="add_gender" name="gender">
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                                <option value="Khác" selected>Khác</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_faculty_id" class="form-label">Khoa <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_faculty_id" name="faculty_id" required>
                                <option value="">Chọn khoa</option>
                                <?php foreach ($faculties as $faculty): ?>
                                    <option value="<?php echo $faculty['faculty_id']; ?>"><?php echo htmlspecialchars($faculty['faculty_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="add_faculty_id_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_position" class="form-label">Chức vụ</label>
                            <input type="text" class="form-control" id="add_position" name="position" placeholder="Ví dụ: Tiến sĩ, Giảng viên chính">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_specialization" class="form-label">Chuyên ngành</label>
                            <input type="text" class="form-control" id="add_specialization" name="specialization" placeholder="Ví dụ: Trí tuệ nhân tạo">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_years_of_experience" class="form-label">Kinh nghiệm (năm)</label>
                            <input type="number" class="form-control" id="add_years_of_experience" name="years_of_experience" min="0" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="add_email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_phone_number" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="add_phone_number" name="phone_number" placeholder="Ví dụ: 0123456789">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_date_of_birth" class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" id="add_date_of_birth" name="date_of_birth">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_address" class="form-label">Địa chỉ</label>
                            <input type="text" class="form-control" id="add_address" name="address" placeholder="Ví dụ: 123 Đường ABC, Quận 1, TP.HCM">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Thêm giảng viên</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal nhập Excel -->
<div class="modal fade" id="importLecturerModal" tabindex="-1" aria-labelledby="importLecturerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importLecturerModalLabel">Nhập dữ liệu từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importLecturerForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="excelFile" class="form-label">Chọn file Excel</label>
                        <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="updateExistingLecturer" name="updateExisting">
                        <label class="form-check-label" for="updateExistingLecturer">Cập nhật dữ liệu hiện có (nếu trùng MSGV)</label>
                    </div>
                    <a href="/quanlydoan/Lecturer/downloadTemplate" class="btn btn-outline-secondary mb-3">Tải mẫu Excel</a>
                    <button type="button" class="btn btn-primary" id="importLecturerButton">Nhập dữ liệu</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    // Hàm format ngày
    function formatDate(dateStr) {
        if (!dateStr) return 'Chưa có';
        const date = new Date(dateStr);
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    // Load chi tiết giảng viên
    function loadLecturerDetails(id) {
        const contentDiv = document.getElementById('viewLecturerContent');
        contentDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

        fetch(`/quanlydoan/Lecturer/show/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.lecturer) {
                    const lecturer = data.lecturer;
                    contentDiv.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary mb-3">
                                            <i class="bi bi-info-circle me-2"></i>Thông tin cơ bản
                                        </h6>
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td width="120"><strong>MSGV:</strong></td>
                                                <td>${lecturer.lecturer_code || 'Chưa có'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Giới tính:</strong></td>
                                                <td>${lecturer.gender || 'Chưa cập nhật'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Khoa:</strong></td>
                                                <td>${lecturer.faculty_name || 'Chưa có khoa'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Chức vụ:</strong></td>
                                                <td>${lecturer.position || 'Chưa có'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Chuyên ngành:</strong></td>
                                                <td>${lecturer.specialization || 'Chưa có'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Kinh nghiệm:</strong></td>
                                                <td>${lecturer.years_of_experience || 0} năm</td>
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
                                                <td width="120"><strong>Email:</strong></td>
                                                <td>${lecturer.email || 'Chưa có'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>SĐT:</strong></td>
                                                <td>${lecturer.phone_number || 'Chưa có'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Địa chỉ:</strong></td>
                                                <td>${lecturer.address || 'Chưa có'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Ngày sinh:</strong></td>
                                                <td>${lecturer.date_of_birth ? formatDate(lecturer.date_of_birth) : 'Chưa có'}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Trạng thái:</strong></td>
                                                <td>
                                                    <span class="badge ${lecturer.status === 'active' ? 'bg-success' : 'bg-danger'}">
                                                        ${lecturer.status === 'active' ? 'Hoạt động' : 'Khóa'}
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
                            <p>Không thể tải thông tin giảng viên.</p>
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

    // Chuyển sang edit mode
    function switchToEditModeLecturer() {
        const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewLecturerModal'));
        const editModal = new bootstrap.Modal(document.getElementById('editLecturerModal'));

        viewModal.hide();
        const lecturerId = document.querySelector('#viewLecturerModal [data-lecturer-id]')?.getAttribute('data-lecturer-id');
        if (lecturerId) {
            setTimeout(() => {
                loadLecturerForEdit(lecturerId);
                editModal.show();
            }, 500);
        }
    }

    // Load dữ liệu cho edit
    function loadLecturerForEdit(id) {
        fetch(`/quanlydoan/Lecturer/show/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.lecturer) {
                    const lecturer = data.lecturer;
                    document.getElementById('edit_lecturer_id').value = lecturer.lecturer_id;
                    document.getElementById('edit_lecturer_code').value = lecturer.lecturer_code || '';
                    document.getElementById('edit_full_name').value = lecturer.full_name || '';
                    document.getElementById('edit_gender').value = lecturer.gender || 'Khác';
                    document.getElementById('edit_faculty_id').value = lecturer.faculty_id || '';
                    document.getElementById('edit_position').value = lecturer.position || '';
                    document.getElementById('edit_specialization').value = lecturer.specialization || '';
                    document.getElementById('edit_years_of_experience').value = lecturer.years_of_experience || 0;
                    document.getElementById('edit_email').value = lecturer.email || '';
                    document.getElementById('edit_phone_number').value = lecturer.phone_number || '';
                    document.getElementById('edit_date_of_birth').value = lecturer.date_of_birth || '';
                    document.getElementById('edit_address').value = lecturer.address || '';
                } else {
                    alert('Không thể tải dữ liệu giảng viên.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi kết nối máy chủ.');
            });
    }

    // Submit edit form
    document.getElementById('editLecturerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const lecturerId = formData.get('lecturer_id');

        fetch(`/quanlydoan/Lecturer/update/${lecturerId}`, {
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

    // Add form validation
    const addLecturerForm = document.getElementById('addLecturerForm');
    addLecturerForm.addEventListener('submit', function(e) {
        let valid = true;
        const lecturerCode = document.getElementById('add_lecturer_code');
        const username = document.getElementById('add_username');
        const facultyId = document.getElementById('add_faculty_id');

        if (!lecturerCode.value.trim()) {
            valid = false;
            lecturerCode.classList.add('is-invalid');
            document.getElementById('add_lecturer_code_error').textContent = 'MSGV không được để trống.';
        } else {
            lecturerCode.classList.remove('is-invalid');
        }

        if (!username.value.trim()) {
            valid = false;
            username.classList.add('is-invalid');
            document.getElementById('add_username_error').textContent = 'Username không được để trống.';
        } else {
            username.classList.remove('is-invalid');
        }

        if (!facultyId.value) {
            valid = false;
            facultyId.classList.add('is-invalid');
            document.getElementById('add_faculty_id_error').textContent = 'Vui lòng chọn khoa.';
        } else {
            facultyId.classList.remove('is-invalid');
        }

        if (!valid) {
            e.preventDefault();
        }
    });

    // Auto fill username from lecturer_code
    document.getElementById('add_lecturer_code').addEventListener('input', function() {
        const usernameInput = document.getElementById('add_username');
        if (!usernameInput.value.trim()) {
            usernameInput.value = this.value;
        }
    });

    // Delete modal
    document.getElementById('deleteLecturerModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const lecturerId = button.getAttribute('data-lecturer-id');
        const lecturerName = button.getAttribute('data-lecturer-name');
        const lecturerMSGV = button.getAttribute('data-lecturer-msgv');

        document.getElementById('deleteLecturerName').textContent = lecturerName;
        document.getElementById('deleteLecturerMSGV').textContent = lecturerMSGV;

        const confirmBtn = document.getElementById('confirmDeleteLecturerBtn');
        confirmBtn.onclick = function() {
            fetch(`/quanlydoan/Lecturer/destroy/${lecturerId}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message || 'Xóa thất bại');
                    }
                })
                .catch(err => {
                    alert('Lỗi kết nối: ' + err.message);
                })
                .finally(() => {
                    bootstrap.Modal.getInstance(document.getElementById('deleteLecturerModal')).hide();
                });
        };
    });

    // Import button
    document.getElementById('importLecturerButton').addEventListener('click', function() {
        const form = document.getElementById('importLecturerForm');
        const formData = new FormData(form);
        const button = this;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang nhập...';

        fetch('/quanlydoan/Lecturer/import', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
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
                bootstrap.Modal.getInstance(document.getElementById('importLecturerModal')).hide();
            });
    });
    document.getElementById('addLecturerForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Ngăn submit form thông thường để tránh reload trang

        // Kiểm tra validation thủ công (nếu cần, vì đã có ở trên)
        let valid = true;
        const lecturerCode = document.getElementById('add_lecturer_code');
        const username = document.getElementById('add_username');
        const facultyId = document.getElementById('add_faculty_id');
        const fullName = document.getElementById('add_full_name');
        const email = document.getElementById('add_email');

        // Reset lỗi
        lecturerCode.classList.remove('is-invalid');
        username.classList.remove('is-invalid');
        facultyId.classList.remove('is-invalid');
        fullName.classList.remove('is-invalid');
        email.classList.remove('is-invalid');

        if (!lecturerCode.value.trim()) {
            valid = false;
            lecturerCode.classList.add('is-invalid');
            document.getElementById('add_lecturer_code_error').textContent = 'MSGV không được để trống.';
        }
        if (!username.value.trim()) {
            valid = false;
            username.classList.add('is-invalid');
            document.getElementById('add_username_error').textContent = 'Username không được để trống.';
        }
        if (!facultyId.value) {
            valid = false;
            facultyId.classList.add('is-invalid');
            document.getElementById('add_faculty_id_error').textContent = 'Vui lòng chọn khoa.';
        }
        if (!fullName.value.trim()) {
            valid = false;
            fullName.classList.add('is-invalid');
            // Thêm invalid-feedback cho full_name nếu chưa có
            // (Bạn có thể thêm <div class="invalid-feedback" id="add_full_name_error"></div> vào HTML form nếu cần)
        }
        if (!email.value.trim() || !email.checkValidity()) {
            valid = false;
            email.classList.add('is-invalid');
            // Tương tự, thêm invalid-feedback nếu cần
        }

        if (!valid) {
            return;
        }

        // Gửi AJAX
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Đang thêm...';

        fetch('/quanlydoan/Lecturer/store', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('addLecturerModal')).hide();
                    location.reload(); // Reload trang để cập nhật danh sách giảng viên
                } else {
                    alert(data.message || 'Thêm thất bại. Vui lòng kiểm tra dữ liệu.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi kết nối máy chủ. Vui lòng thử lại.');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Thêm giảng viên';
            });
    });
</script>