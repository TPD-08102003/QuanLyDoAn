<?php
// helpers/status_helper.php (Hoặc định nghĩa trực tiếp ở đây vì nó private trong Controller)
// Bạn nên tạo một file helper chung và include vào, nhưng để đơn giản, tôi định nghĩa ở đây.

if (!function_exists('getStatusBadgeClass')) {
    function getStatusBadgeClass(string $status): string
    {
        $classes = [
            'ChoDuyet' => 'bg-warning text-dark',
            'DaDuyet' => 'bg-info text-dark',
            'DangThucHien' => 'bg-primary',
            'DaNopBaoCao' => 'bg-secondary',
            'DaBaoVe' => 'bg-success',
            'HoanThanh' => 'bg-success',
            'Huy' => 'bg-danger'
        ];
        return $classes[$status] ?? 'bg-secondary';
    }
}

if (!function_exists('getStatusText')) {
    function getStatusText(string $status): string
    {
        $texts = [
            'ChoDuyet' => 'Chờ duyệt',
            'DaDuyet' => 'Đã duyệt',
            'DangThucHien' => 'Đang thực hiện',
            'DaNopBaoCao' => 'Đã nộp báo cáo',
            'DaBaoVe' => 'Đã bảo vệ',
            'HoanThanh' => 'Hoàn thành',
            'Huy' => 'Hủy'
        ];
        return $texts[$status] ?? $status;
    }
}

$statusOptions = [
    'ChoDuyet' => 'Chờ duyệt',
    'DaDuyet' => 'Đã duyệt',
    'DangThucHien' => 'Đang thực hiện',
    'DaNopBaoCao' => 'Đã nộp báo cáo',
    'DaBaoVe' => 'Đã bảo vệ',
    'HoanThanh' => 'Hoàn thành',
    'Huy' => 'Hủy'
];

?>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="page-title">Quản lý Đồ án</h2>
        <p class="page-subtitle">Quản lý, thêm, sửa, xóa và nhập/xuất danh sách đồ án.</p>
    </div>
    <div class="btn-toolbar gap-2">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProjectModal">
            <i class="bi bi-plus-lg me-2"></i> Thêm Đồ án
        </button>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear me-2"></i> Tùy chọn
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importProjectModal">
                        <i class="bi bi-upload me-2"></i> Nhập từ Excel
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="/quanlydoan/Project/export">
                        <i class="bi bi-download me-2"></i> Xuất ra Excel
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="/quanlydoan/Project/downloadTemplate">
                        <i class="bi bi-file-earmark-arrow-down me-2"></i> Tải file mẫu
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-0">
        <form method="GET" action="/quanlydoan/Project/manage" class="d-flex">
            <input type="text" class="form-control me-2" name="keyword" placeholder="Tìm kiếm theo tiêu đề, giảng viên..." value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Tiêu đề</th>
                        <th scope="col">Giảng viên</th>
                        <th scope="col">Khoa</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col">Ngày tạo</th>
                        <th scope="col">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Không tìm thấy đồ án nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo $project['project_id']; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($project['title']); ?></td>
                                <td><?php echo htmlspecialchars($project['lecturer_name'] ?? 'Chưa phân công'); ?></td>
                                <td><?php echo htmlspecialchars($project['faculty_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge <?php echo getStatusBadgeClass($project['status']); ?>">
                                        <?php echo getStatusText($project['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($project['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-edit" data-id="<?php echo $project['project_id']; ?>" title="Sửa">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $project['project_id']; ?>" title="Xóa">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&keyword=<?php echo urlencode($keyword); ?>">Trước</a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&keyword=<?php echo urlencode($keyword); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&keyword=<?php echo urlencode($keyword); ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="addProjectForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProjectModalLabel">Thêm Đồ án mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="addProjectAlert" class="alert alert-danger d-none" role="alert"></div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề Đồ án <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="lecturer_id" class="form-label">Giảng viên hướng dẫn <span class="text-danger">*</span></label>
                            <select class="form-select select2-add" id="lecturer_id" name="lecturer_id" style="width: 100%;" required>
                                <option value="">-- Chọn giảng viên --</option>
                                <?php foreach ($lecturers as $lecturer): ?>
                                    <option value="<?php echo $lecturer['lecturer_id']; ?>">
                                        <?php echo htmlspecialchars($lecturer['full_name'] . ' (' . $lecturer['email'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <?php foreach ($statusOptions as $value => $text): ?>
                                    <option value="<?php echo $value; ?>" <?php echo ($value === 'ChoDuyet') ? 'selected' : ''; ?>>
                                        <?php echo $text; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="addProjectSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="editProjectForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProjectModalLabel">Cập nhật Đồ án</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_project_id" name="project_id">
                    <div id="editProjectAlert" class="alert alert-danger d-none" role="alert"></div>

                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Tiêu đề Đồ án <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_lecturer_id" class="form-label">Giảng viên hướng dẫn <span class="text-danger">*</span></label>
                            <select class="form-select select2-edit" id="edit_lecturer_id" name="lecturer_id" style="width: 100%;" required>
                                <option value="">-- Chọn giảng viên --</option>
                                <?php foreach ($lecturers as $lecturer): ?>
                                    <option value="<?php echo $lecturer['lecturer_id']; ?>">
                                        <?php echo htmlspecialchars($lecturer['full_name'] . ' (' . $lecturer['email'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="edit_status" name="status">
                                <?php foreach ($statusOptions as $value => $text): ?>
                                    <option value="<?php echo $value; ?>"><?php echo $text; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="editProjectSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProjectModalLabel">Xác nhận Xóa Đồ án</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa đồ án này? Hành động này không thể hoàn tác.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importProjectModal" tabindex="-1" aria-labelledby="importProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="importProjectForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="importProjectModalLabel">Nhập Đồ án từ Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="importProjectAlert" class="alert alert-danger d-none" role="alert"></div>
                    <div class="mb-3">
                        <label for="excelFile" class="form-label">Chọn file (.xlsx, .xls) <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="on" id="updateExisting" name="updateExisting">
                        <label class="form-check-label" for="updateExisting">
                            Cập nhật đồ án nếu đã tồn tại (dựa theo tiêu đề)
                        </label>
                    </div>
                    <div class="mt-3">
                        <a href="/quanlydoan/Project/downloadTemplate">Tải file mẫu tại đây</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="importProjectSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Nhập
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Khởi tạo Select2 cho các modal
        // Phải chỉ định dropdownParent là modal chứa nó
        $('.select2-add').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addProjectModal')
        });

        $('.select2-edit').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#editProjectModal')
        });

        // Hàm hiển thị spinner
        function toggleSpinner(btnId, show) {
            const btn = document.getElementById(btnId);
            if (!btn) return;
            const spinner = btn.querySelector('.spinner-border');
            if (show) {
                btn.disabled = true;
                spinner.classList.remove('d-none');
            } else {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        // Hàm hiển thị thông báo lỗi
        function showAlert(alertId, message) {
            const alertBox = document.getElementById(alertId);
            if (!alertBox) return;
            alertBox.innerHTML = message;
            alertBox.classList.remove('d-none');
        }

        // Hàm ẩn thông báo lỗi
        function hideAlert(alertId) {
            const alertBox = document.getElementById(alertId);
            if (alertBox) {
                alertBox.classList.add('d-none');
            }
        }

        // 1. Xử lý Thêm Đồ án (Add Project)
        const addForm = document.getElementById('addProjectForm');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                toggleSpinner('addProjectSubmitBtn', true);
                hideAlert('addProjectAlert');

                fetch('/quanlydoan/Project/store', {
                        method: 'POST',
                        body: new FormData(addForm)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Đóng modal và tải lại trang để cập nhật
                            const modal = bootstrap.Modal.getInstance(document.getElementById('addProjectModal'));
                            modal.hide();
                            location.reload();
                        } else {
                            showAlert('addProjectAlert', data.message || 'Đã xảy ra lỗi.');
                        }
                    })
                    .catch(error => {
                        showAlert('addProjectAlert', 'Lỗi kết nối. Vui lòng thử lại.');
                    })
                    .finally(() => {
                        toggleSpinner('addProjectSubmitBtn', false);
                    });
            });
        }

        // 2. Xử lý Sửa Đồ án (Edit Project)

        // 2a. Lấy thông tin khi nhấn nút "Sửa"
        const editModal = new bootstrap.Modal(document.getElementById('editProjectModal'));
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const projectId = this.getAttribute('data-id');
                hideAlert('editProjectAlert');

                fetch('/quanlydoan/Project/getProjectDetails/' + projectId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const project = data.project;
                            document.getElementById('edit_project_id').value = project.project_id;
                            document.getElementById('edit_title').value = project.title;
                            document.getElementById('edit_description').value = project.description;
                            document.getElementById('edit_status').value = project.status;

                            // Cập nhật Select2
                            $('#edit_lecturer_id').val(project.lecturer_id).trigger('change');

                            editModal.show();
                        } else {
                            alert(data.message || 'Không thể tải dữ liệu đồ án.');
                        }
                    })
                    .catch(() => {
                        alert('Lỗi kết nối. Không thể tải dữ liệu.');
                    });
            });
        });

        // 2b. Gửi form cập nhật
        const editForm = document.getElementById('editProjectForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                toggleSpinner('editProjectSubmitBtn', true);
                hideAlert('editProjectAlert');

                const projectId = document.getElementById('edit_project_id').value;

                fetch('/quanlydoan/Project/update/' + projectId, {
                        method: 'POST',
                        body: new FormData(editForm)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            editModal.hide();
                            location.reload();
                        } else {
                            showAlert('editProjectAlert', data.message || 'Cập nhật thất bại.');
                        }
                    })
                    .catch(error => {
                        showAlert('editProjectAlert', 'Lỗi kết nối. Vui lòng thử lại.');
                    })
                    .finally(() => {
                        toggleSpinner('editProjectSubmitBtn', false);
                    });
            });
        }

        // 3. Xử lý Xóa Đồ án (Delete Project)
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteProjectModal'));
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        let projectIdToDelete = null;

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                projectIdToDelete = this.getAttribute('data-id');
                deleteModal.show();
            });
        });

        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                if (projectIdToDelete) {
                    fetch('/quanlydoan/Project/destroy/' + projectIdToDelete, {
                            method: 'POST' // Hoặc 'DELETE' nếu server hỗ trợ
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                deleteModal.hide();
                                location.reload();
                            } else {
                                alert(data.message || 'Xóa thất bại.');
                            }
                        })
                        .catch(() => {
                            alert('Lỗi kết nối. Không thể xóa.');
                        });
                }
            });
        }

        // 4. Xử lý Nhập (Import Project)
        const importForm = document.getElementById('importProjectForm');
        if (importForm) {
            importForm.addEventListener('submit', function(e) {
                e.preventDefault();
                toggleSpinner('importProjectSubmitBtn', true);
                hideAlert('importProjectAlert');

                const formData = new FormData(importForm);

                fetch('/quanlydoan/Project/import', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('importProjectModal'));
                            modal.hide();
                            location.reload();
                            // Bạn có thể dùng một alert session (như trong layout) để thông báo thành công
                        } else {
                            showAlert('importProjectAlert', data.message || 'Nhập file thất bại.');
                        }
                    })
                    .catch(error => {
                        showAlert('importProjectAlert', 'Lỗi kết nối hoặc file quá lớn. Vui lòng thử lại.');
                    })
                    .finally(() => {
                        toggleSpinner('importProjectSubmitBtn', false);
                    });
            });
        }
    });
</script>