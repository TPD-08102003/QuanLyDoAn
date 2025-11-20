<?php
// views/project/manage.php

$statusMapping = [
    'ChoDuyet' => ['class' => 'bg-warning text-dark', 'text' => 'Chờ Duyệt'],
    'DaDuyet' => ['class' => 'bg-info text-dark', 'text' => 'Đã Duyệt'],
    'DangThucHien' => ['class' => 'bg-primary', 'text' => 'Đang Thực Hiện'],
    'DaNopBaoCao' => ['class' => 'bg-secondary', 'text' => 'Đã Nộp Báo Cáo'],
    'DaBaoVe' => ['class' => 'bg-success', 'text' => 'Đã Bảo Vệ'],
    'HoanThanh' => ['class' => 'bg-success', 'text' => 'Hoàn Thành'],
    'Huy' => ['class' => 'bg-danger', 'text' => 'Hủy']
];
?>

<h1 class="page-title h2">Quản lý Đồ Án</h1>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-auto me-auto">
                <form method="GET" action="/quanlydoan/Project/manage" class="row g-3 align-items-center">
                    <div class="col-md-auto" style="width: 300px;">
                        <input type="text" class="form-control" name="keyword"
                            placeholder="Tìm kiếm theo Tên đồ án, Mã ĐA, Tên GV"
                            value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Tìm kiếm</button>
                    </div>
                </form>
            </div>
            <div class="col-12 col-md-auto">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEditProjectModal" data-mode="add" id="addProjectBtn"><i class="bi bi-plus-lg"></i> Thêm Đồ án</button>
                <button class="btn btn-info text-dark" data-bs-toggle="modal" data-bs-target="#importProjectModal"><i class="bi bi-cloud-upload"></i> Import</button>
                <a href="/quanlydoan/Project/export" class="btn btn-secondary"><i class="bi bi-cloud-download"></i> Export</a>
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th scope="col">Mã ĐA</th>
                        <th scope="col">Tên Đồ Án</th>
                        <th scope="col">GV Hướng Dẫn</th>
                        <th scope="col">Khoa</th>
                        <th scope="col">Trạng Thái</th>
                        <th scope="col">Ngày Tạo</th>
                        <th scope="col">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['project_id']); ?></td>
                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                <td><?php echo htmlspecialchars($project['lecturer_name'] ?? 'Chưa phân công'); ?></td>
                                <td><?php echo htmlspecialchars($project['faculty_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php
                                    $status = $project['status'];
                                    $badge = $statusMapping[$status] ?? ['class' => 'bg-light text-dark', 'text' => $status];
                                    ?>
                                    <span class="badge <?php echo $badge['class']; ?>"><?php echo $badge['text']; ?></span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($project['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info view-btn"
                                        data-id="<?php echo $project['project_id']; ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addEditProjectModal"
                                        data-mode="view"
                                        title="Xem chi tiết"><i class="bi bi-eye"></i></button>

                                    <button class="btn btn-sm btn-outline-warning edit-btn"
                                        data-id="<?php echo $project['project_id']; ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addEditProjectModal"
                                        data-mode="edit"
                                        title="Chỉnh sửa"><i class="bi bi-pencil"></i></button>

                                    <button class="btn btn-sm btn-outline-danger delete-btn"
                                        data-id="<?php echo $project['project_id']; ?>"
                                        data-title="<?php echo htmlspecialchars($project['title']); ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteProjectModal"
                                        title="Xóa"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <?php if (!empty($keyword)): ?>
                                    Không tìm thấy đồ án nào khớp với từ khóa "<?php echo htmlspecialchars($keyword); ?>".
                                <?php else: ?>
                                    Không tìm thấy đồ án nào.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        // Logic Pagination (giữ nguyên)
        if (isset($totalPages) && $totalPages > 1):
        ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&keyword=<?php echo htmlspecialchars($keyword ?? ''); ?>">Trước</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&keyword=<?php echo htmlspecialchars($keyword ?? ''); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&keyword=<?php echo htmlspecialchars($keyword ?? ''); ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

    </div>
</div>
<div class="modal fade" id="importProjectModal" tabindex="-1" aria-labelledby="importProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="importForm" enctype="multipart/form-data">
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title" id="importProjectModalLabel">Import Đồ án từ Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="excelFile" class="form-label">Chọn file Excel (.xlsx)</label>
                        <input type="file" class="form-control" id="excelFile" name="excelFile" accept=".xlsx,.xls" required>
                        <div class="form-text">
                            File mẫu: <a href="/quanlydoan/Project/downloadTemplate" class="text-decoration-underline">Tải về</a>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="update_existing" id="updateExisting">
                        <label class="form-check-label" for="updateExisting">
                            Cập nhật đồ án nếu tiêu đề đã tồn tại
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-info text-dark" id="importButton">Nhập dữ liệu</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="addEditProjectModal" tabindex="-1" aria-labelledby="addEditProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="projectForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEditProjectModalLabel">Thêm Đồ án mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="project_id" id="projectId">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="title" class="form-label">Tên Đồ Án (*)</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="col-md-6">
                            <label for="faculty_id" class="form-label">Khoa/Ngành (*)</label>
                            <select class="form-select" id="faculty_id" name="faculty_id" required>
                                <option value="">-- Chọn Khoa/Ngành --</option>
                                <?php foreach ($faculties as $faculty): ?>
                                    <option value="<?php echo htmlspecialchars($faculty['faculty_id']); ?>">
                                        <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="lecturer_id" class="form-label">GV Hướng Dẫn (*)</label>
                            <select class="form-select" id="lecturer_id" name="lecturer_id" required disabled>
                                <option value="">-- Chọn Khoa/Ngành trước --</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Trạng Thái (*)</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php foreach ($statusMapping as $key => $status): ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>">
                                        <?php echo htmlspecialchars($status['text']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="max_students" class="form-label">Số SV Tối đa</label>
                            <input type="number" class="form-control" id="max_students" name="max_students" value="1" min="1" max="5">
                        </div>

                        <div class="col-md-12">
                            <label for="description" class="form-label">Mô tả Đồ Án</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="saveProjectBtn">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- <div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteProjectModalLabel">Xác nhận xóa đồ án</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa đồ án <strong id="projectTitleToDelete"></strong> (ID: <span id="projectIdToDelete"></span>) không?<br>
                <small class="text-danger">Hành động này không thể hoàn tác.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteProject">Xóa ngay</button>
            </div>
        </div>
    </div>
</div> -->
<div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white" id="deleteProjectModalLabel">Xác nhận xóa đồ án</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa đồ án <strong id="projectTitleToDelete"></strong> (ID: <span id="projectIdToDelete"></span>) không?<br>
                <small class="text-danger">Hành động này không thể hoàn tác và sẽ xóa luôn các nhóm, báo cáo liên quan.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteProject">Xóa ngay</button>
            </div>
        </div>
    </div>
</div>
<script>
    // Hàm hiển thị thông báo dùng chung (giữ nguyên)
    function showAlert(message, type = 'success') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show fixed-top-alert" role="alert" style="z-index: 2000; top: 10px; right: 10px; width: 350px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('afterbegin', alertHtml);
        setTimeout(() => {
            const alert = document.querySelector('.fixed-top-alert');
            if (alert) {
                bootstrap.Alert.getInstance(alert)?.close();
            }
        }, 5000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const addEditModal = document.getElementById('addEditProjectModal');
        const deleteModal = document.getElementById('deleteProjectModal');
        const importModal = document.getElementById('importProjectModal');
        const projectForm = document.getElementById('projectForm');
        const importForm = document.getElementById('importForm');
        const saveProjectBtn = document.getElementById('saveProjectBtn');
        const importButton = document.getElementById('importButton');
        const confirmDeleteBtn = document.getElementById('confirmDeleteProject');
        let projectIdToDelete = null;

        const facultySelect = document.getElementById('faculty_id');
        const lecturerSelect = document.getElementById('lecturer_id');

        /**
         * **HÀM MỚI:** Fetch danh sách giảng viên theo khoa và cập nhật dropdown
         * @param {string|int} facultyId ID của khoa
         * @param {string|int|null} lecturerToSelect ID của giảng viên cần chọn (dùng cho chế độ edit)
         */
        /**
         
         * Hàm Fetch giảng viên
         * @param {string|int} facultyId ID của khoa
         * @param {string|int|null} lecturerToSelect ID của giảng viên cần chọn
         * @param {boolean} isViewMode Chế độ chỉ xem (sẽ vô hiệu hóa dropdown sau khi tải)
         */
        function fetchLecturers(facultyId, lecturerToSelect = null, isViewMode = false) {
            if (!facultyId || facultyId === '') {
                lecturerSelect.innerHTML = '<option value="">-- Chọn Khoa/Ngành trước --</option>';
                lecturerSelect.disabled = true;
                return;
            }

            lecturerSelect.disabled = true;
            lecturerSelect.innerHTML = '<option value="">-- Đang tải GV... --</option>';

            fetch('/quanlydoan/Project/getLecturersByFaculty/' + facultyId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.lecturers) {
                        lecturerSelect.innerHTML = '<option value="">-- Chọn GVHD --</option>';
                        data.lecturers.forEach(lecturer => {
                            const option = new Option(lecturer.full_name, lecturer.lecturer_id);
                            lecturerSelect.add(option);
                        });

                        if (lecturerToSelect) {
                            lecturerSelect.value = lecturerToSelect;
                        }


                        if (!isViewMode) {
                            lecturerSelect.disabled = false;
                        }
                        // Nếu là view mode, nó sẽ giữ nguyên trạng thái disabled
                    } else {
                        lecturerSelect.innerHTML = '<option value="">-- Lỗi tải GV --</option>';
                        // Vẫn vô hiệu hóa nếu là view mode
                        lecturerSelect.disabled = isViewMode;
                    }
                })
                .catch(err => {
                    lecturerSelect.innerHTML = '<option value="">-- Lỗi kết nối --</option>';
                    lecturerSelect.disabled = isViewMode;
                });
        }


        facultySelect.addEventListener('change', function() {
            const selectedFacultyId = this.value;
            fetchLecturers(selectedFacultyId, null, false); // Khi tự chọn thì không phải view mode
        });



        addEditModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const projectId = button.getAttribute('data-id');
            // *** THAY ĐỔI: Lấy mode từ nút ***
            const mode = button.getAttribute('data-mode'); // 'view', 'edit', hoặc null (cho nút Add)

            const modalTitle = addEditModal.querySelector('#addEditProjectModalLabel');
            const saveProjectBtn = addEditModal.querySelector('#saveProjectBtn');
            const formElements = projectForm.querySelectorAll('input, select, textarea');

            // Reset form
            projectForm.reset();
            document.getElementById('projectId').value = '';


            modalTitle.textContent = 'Thêm Đồ án mới';
            saveProjectBtn.style.display = 'block'; // Hiển thị nút lưu
            formElements.forEach(el => el.disabled = false); // Kích hoạt mọi trường
            lecturerSelect.innerHTML = '<option value="">-- Chọn Khoa/Ngành trước --</option>';
            lecturerSelect.disabled = true;


            if (mode === 'edit' || mode === 'view') {
                // Chế độ Sửa hoặc Xem
                modalTitle.textContent = (mode === 'edit') ? 'Chỉnh sửa Đồ án' : 'Xem Chi tiết Đồ án';

                if (mode === 'view') {

                    saveProjectBtn.style.display = 'none'; // Ẩn nút lưu
                    formElements.forEach(el => el.disabled = true); // Vô hiệu hóa tất cả
                } else {

                    saveProjectBtn.style.display = 'block';

                    lecturerSelect.disabled = true;
                }

                // *** SỬA LỖI: LOGIC NÀY SẼ CHẠY ĐÚNG KHI CÓ HÀM getByIdWithDetails ***
                fetch('/quanlydoan/Project/getProjectDetails/' + projectId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.project) {
                            const project = data.project;
                            document.getElementById('projectId').value = project.project_id;
                            document.getElementById('title').value = project.title;
                            document.getElementById('description').value = project.description || '';
                            document.getElementById('status').value = project.status;
                            document.getElementById('max_students').value = project.max_students || 1;


                            // 1. Điền faculty_id (sẽ hoạt động vì model đã trả về)
                            document.getElementById('faculty_id').value = project.faculty_id || '';

                            const selectedFacultyId = project.faculty_id || '';
                            const originalLecturerId = project.lecturer_id || null;

                            // 2. Tải GV cho khoa đó, chọn sẵn GV, và báo cho hàm biết đây có phải view mode không
                            fetchLecturers(selectedFacultyId, originalLecturerId, (mode === 'view'));

                        } else {
                            showAlert(data.message || 'Không thể tải dữ liệu đồ án.', 'danger');
                            bootstrap.Modal.getInstance(addEditModal).hide();
                        }
                    })
                    .catch(err => {
                        showAlert('Lỗi kết nối khi tải dữ liệu đồ án: ' + err.message, 'danger');
                        bootstrap.Modal.getInstance(addEditModal).hide();
                    });
            }
            // Không cần 'else' cho mode 'add' vì trạng thái reset ban đầu đã là 'add'
        });

        // Xử lý submit form Add/Edit (Không đổi)
        projectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const projectId = document.getElementById('projectId').value;
            const isEditing = !!projectId;
            const url = isEditing ? '/quanlydoan/Project/update/' + projectId : '/quanlydoan/Project/store';

            saveProjectBtn.disabled = true;
            saveProjectBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';

            const formData = new FormData(projectForm);

            fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(addEditModal).hide();
                        window.location.reload();
                    } else {
                        showAlert(data.message || 'Lỗi xử lý dữ liệu.', 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Lỗi kết nối: ' + err.message, 'danger');
                })
                .finally(() => {
                    saveProjectBtn.disabled = false;
                    saveProjectBtn.innerHTML = 'Lưu';
                });
        });

        // 1. Xử lý hiển thị modal xóa
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            projectIdToDelete = button.getAttribute('data-id');
            const projectTitle = button.getAttribute('data-title');

            deleteModal.querySelector('#projectTitleToDelete').textContent = projectTitle;
            deleteModal.querySelector('#projectIdToDelete').textContent = projectIdToDelete;
        });

        // 2. Xử lý xác nhận xóa
        confirmDeleteBtn.onclick = function() {
            if (!projectIdToDelete) return;

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xóa...';

            fetch('/quanlydoan/Project/destroy/' + projectIdToDelete, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Xóa đồ án thành công!', 'success');
                        bootstrap.Modal.getInstance(deleteModal).hide();
                        window.location.reload();
                    } else {
                        showAlert(data.message || 'Lỗi xóa đồ án.', 'danger');
                    }
                })
                .catch(err => {
                    showAlert('Lỗi kết nối: ' + err.message, 'danger');
                })
                .finally(() => {
                    this.disabled = false;
                    this.innerHTML = 'Xóa';
                });
        };


        importForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const fileInput = document.getElementById('excelFile');
            if (!fileInput.files.length) {
                showAlert('Vui lòng chọn tệp Excel.', 'warning');
                return;
            }

            const formData = new FormData(importForm);

            importButton.disabled = true;
            importButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang nhập...';

            fetch('/quanlydoan/Project/import', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        bootstrap.Modal.getInstance(importModal).hide();
                        window.location.reload();
                    } else {
                        showAlert(data.message || 'Lỗi nhập dữ liệu.', 'danger');
                    }
                })
                .catch(error => {
                    showAlert('Lỗi kết nối: ' + error.message, 'danger');
                })
                .finally(() => {
                    importButton.disabled = false;
                    importButton.innerHTML = 'Nhập dữ liệu';
                });
        });
    });
</script>