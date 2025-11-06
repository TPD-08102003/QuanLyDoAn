<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?php echo $title; ?></h1>
            <p class="page-subtitle">Quản lý thông tin các khoa trong hệ thống</p>
        </div>
        <!-- Search and Filter -->
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="text" name="keyword" class="form-control me-2" placeholder="Tìm kiếm theo tên khoa..."
                        value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if (!empty($keyword)): ?>
                        <a href="/quanlydoan/Faculties/manage" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFacultyModal">
                    <i class="bi bi-plus-circle me-2"></i>Thêm Khoa Mới
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">


        <!-- Faculties Table -->
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th width="50">STT</th>
                        <th>Tên Khoa</th>
                        <th>Mô tả</th>
                        <th>Ngày tạo</th>
                        <th width="150" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($faculties)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    Không có dữ liệu khoa nào
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($faculties as $index => $faculty): ?>
                            <tr>
                                <td><?php echo $offset + $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($faculty['faculty_name']); ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($faculty['description'])): ?>
                                        <span class="d-inline-block text-truncate" style="max-width: 300px;"
                                            title="<?php echo htmlspecialchars($faculty['description']); ?>">
                                            <?php echo htmlspecialchars($faculty['description']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có mô tả</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($faculty['created_at'])); ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button"
                                            class="btn btn-outline-info view-faculty"
                                            data-id="<?php echo $faculty['faculty_id']; ?>"
                                            title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-primary edit-faculty"
                                            data-id="<?php echo $faculty['faculty_id']; ?>"
                                            title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-danger delete-faculty"
                                            data-id="<?php echo $faculty['faculty_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($faculty['faculty_name']); ?>"
                                            title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $page - 1; ?>&keyword=<?php echo urlencode($keyword ?? ''); ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $i; ?>&keyword=<?php echo urlencode($keyword ?? ''); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $page + 1; ?>&keyword=<?php echo urlencode($keyword ?? ''); ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

        <!-- Summary -->
        <div class="text-muted text-center">
            Hiển thị <?php echo count($faculties); ?> trên tổng số <?php echo $total; ?> khoa
        </div>
    </div>
</div>

<!-- Create Faculty Modal -->
<div class="modal fade" id="createFacultyModal" tabindex="-1" aria-labelledby="createFacultyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createFacultyModalLabel">Thêm Khoa Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="/quanlydoan/Faculties/store" id="createFacultyForm">
                <div class="modal-body">
                    <div id="createFacultyMessage"></div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="faculty_name" class="form-label">
                                Tên Khoa <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control"
                                id="faculty_name"
                                name="faculty_name"
                                required
                                maxlength="255">
                            <div class="invalid-feedback">Vui lòng nhập tên khoa.</div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control"
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="Nhập mô tả về khoa..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Thêm Khoa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Faculty Modal -->
<div class="modal fade" id="viewFacultyModal" tabindex="-1" aria-labelledby="viewFacultyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewFacultyModalLabel">Thông tin Chi tiết Khoa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tên Khoa</label>
                        <p id="viewFacultyName" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Ngày tạo</label>
                        <p id="viewFacultyCreatedAt" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Mô tả</label>
                        <p id="viewFacultyDescription" class="form-control-plaintext"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Cập nhật lần cuối</label>
                        <p id="viewFacultyUpdatedAt" class="form-control-plaintext"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Faculty Modal -->
<div class="modal fade" id="editFacultyModal" tabindex="-1" aria-labelledby="editFacultyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editFacultyModalLabel">Sửa Thông tin Khoa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editFacultyForm">
                <div class="modal-body">
                    <div id="editFacultyMessage"></div>
                    <input type="hidden" id="editFacultyId" name="faculty_id">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="editFacultyName" class="form-label">
                                Tên Khoa <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control"
                                id="editFacultyName"
                                name="faculty_name"
                                required
                                maxlength="255">
                            <div class="invalid-feedback">Vui lòng nhập tên khoa.</div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="editFacultyDescription" class="form-label">Mô tả</label>
                            <textarea class="form-control"
                                id="editFacultyDescription"
                                name="description"
                                rows="4"
                                placeholder="Nhập mô tả về khoa..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa khoa <strong id="facultyNameToDelete"></strong>?</p>
                <p class="text-danger small">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Hành động này không thể hoàn tác. Tất cả dữ liệu liên quan đến khoa này sẽ bị ảnh hưởng.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    Xóa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // === XEM CHI TIẾT ===
        document.querySelectorAll('.view-faculty').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                fetch(`/quanlydoan/Faculties/get/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const f = data.faculty;
                            document.getElementById('viewFacultyName').textContent = f.faculty_name;
                            document.getElementById('viewFacultyDescription').textContent = f.description || 'Chưa có mô tả';
                            document.getElementById('viewFacultyCreatedAt').textContent = f.created_at;
                            document.getElementById('viewFacultyUpdatedAt').textContent = f.updated_at || '';
                            new bootstrap.Modal('#viewFacultyModal').show();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(err => alert('Lỗi tải dữ liệu: ' + err));
            });
        });

        // === SỬA ===
        document.querySelectorAll('.edit-faculty').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                fetch(`/quanlydoan/Faculties/get/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const f = data.faculty;
                            document.getElementById('editFacultyId').value = f.faculty_id;
                            document.getElementById('editFacultyName').value = f.faculty_name;
                            document.getElementById('editFacultyDescription').value = f.description;
                            new bootstrap.Modal('#editFacultyModal').show();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(err => alert('Lỗi tải dữ liệu: ' + err));
            });
        });

        // Gửi form sửa (AJAX)
        document.getElementById('editFacultyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('editFacultyId').value;
            const formData = new FormData(this);
            fetch(`/quanlydoan/Faculties/update/${id}`, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(err => alert('Lỗi cập nhật: ' + err));
        });

        // === XÓA ===
        let deleteId = null;
        document.querySelectorAll('.delete-faculty').forEach(btn => {
            btn.addEventListener('click', function() {
                deleteId = this.dataset.id;
                const name = this.dataset.name;
                document.getElementById('facultyNameToDelete').textContent = name;
                new bootstrap.Modal('#deleteModal').show();
            });
        });

        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (!deleteId) return;
            fetch(`/quanlydoan/Faculties/delete/${deleteId}`, {
                    method: 'POST'
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(err => alert('Lỗi xóa: ' + err));
        });

        // === THÊM KHOA (AJAX) ===
        document.getElementById('createFacultyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(`/quanlydoan/Faculties/store`, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(err => alert('Lỗi thêm: ' + err));
        });
    });
</script>