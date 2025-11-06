<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?php echo $title; ?></h1>
            <p class="page-subtitle">Quản lý thông tin các khoa trong hệ thống</p>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFacultyModal">
                <i class="bi bi-plus-circle me-2"></i>Thêm Khoa Mới
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
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
        </div>

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
    // Đảm bảo jQuery đã được load trước khi chạy code
    function initializeFacultyManagement() {
        let facultyToDelete = null;

        // Create Faculty Form
        $('#createFacultyForm').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const spinner = submitBtn.find('.spinner-border');
            const messageDiv = $('#createFacultyMessage');

            // Validation
            if (!form[0].checkValidity()) {
                form.addClass('was-validated');
                return;
            }

            // Show loading
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');

            // Submit form via AJAX
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        messageDiv.html('<div class="alert alert-success">' + response.message + '</div>');
                        form[0].reset();
                        form.removeClass('was-validated');

                        // Reload page after 2 seconds
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        messageDiv.html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    messageDiv.html('<div class="alert alert-danger">Có lỗi xảy ra khi thêm khoa. Vui lòng thử lại.</div>');
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            });
        });

        // View Faculty
        $('.view-faculty').on('click', function() {
            const facultyId = $(this).data('id');
            console.log('View faculty ID:', facultyId);

            // Show loading
            $('#viewFacultyModal .modal-body').html('<div class="text-center"><div class="spinner-border" role="status"></div><p class="mt-2">Đang tải thông tin...</p></div>');
            $('#viewFacultyModal').modal('show');

            // Fetch faculty details
            $.ajax({
                url: '/quanlydoan/Faculties/get/' + facultyId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('View response:', response);
                    if (response.success && response.faculty) {
                        const faculty = response.faculty;
                        $('#viewFacultyName').text(faculty.faculty_name);
                        $('#viewFacultyDescription').text(faculty.description || 'Chưa có mô tả');
                        $('#viewFacultyCreatedAt').text(formatDate(faculty.created_at));
                        // Ẩn hoặc xóa phần updated_at vì bảng không có cột này
                        $('#viewFacultyUpdatedAt').closest('.mb-3').hide();
                    } else {
                        $('#viewFacultyModal .modal-body').html(
                            '<div class="alert alert-danger">' + (response.message || 'Không thể tải thông tin khoa') + '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error('View AJAX Error:', status, error);
                    console.error('XHR:', xhr);
                    let errorMessage = 'Lỗi kết nối server';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    $('#viewFacultyModal .modal-body').html(
                        '<div class="alert alert-danger">' + errorMessage + '</div>'
                    );
                }
            });
        });

        // Edit Faculty - Load data
        $('.edit-faculty').on('click', function() {
            const facultyId = $(this).data('id');
            console.log('Edit faculty ID:', facultyId);

            // Show loading
            $('#editFacultyModal .modal-body').html('<div class="text-center"><div class="spinner-border" role="status"></div><p class="mt-2">Đang tải thông tin...</p></div>');
            $('#editFacultyModal').modal('show');

            // Fetch faculty details
            $.ajax({
                url: '/quanlydoan/Faculties/get/' + facultyId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Edit response:', response);
                    if (response.success && response.faculty) {
                        const faculty = response.faculty;
                        $('#editFacultyId').val(faculty.faculty_id);
                        $('#editFacultyName').val(faculty.faculty_name);
                        $('#editFacultyDescription').val(faculty.description || '');
                        $('#editFacultyMessage').empty();
                    } else {
                        $('#editFacultyModal .modal-body').html(
                            '<div class="alert alert-danger">' + (response.message || 'Không thể tải thông tin khoa') + '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Edit AJAX Error:', status, error);
                    console.error('XHR:', xhr);
                    let errorMessage = 'Lỗi kết nối server';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    $('#editFacultyModal .modal-body').html(
                        '<div class="alert alert-danger">' + errorMessage + '</div>'
                    );
                }
            });
        });

        // Edit Faculty Form
        $('#editFacultyForm').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const facultyId = $('#editFacultyId').val();
            const submitBtn = form.find('button[type="submit"]');
            const spinner = submitBtn.find('.spinner-border');
            const messageDiv = $('#editFacultyMessage');

            // Validation
            if (!form[0].checkValidity()) {
                form.addClass('was-validated');
                return;
            }

            // Show loading
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');

            // Submit form via AJAX
            $.ajax({
                url: '/quanlydoan/Faculties/update/' + facultyId,
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        messageDiv.html('<div class="alert alert-success">' + response.message + '</div>');

                        // Reload page after 2 seconds
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        messageDiv.html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    messageDiv.html('<div class="alert alert-danger">Có lỗi xảy ra khi cập nhật khoa. Vui lòng thử lại.</div>');
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            });
        });

        // Delete Faculty
        $('.delete-faculty').on('click', function() {
            const facultyId = $(this).data('id');
            const facultyName = $(this).data('name');

            facultyToDelete = facultyId;
            $('#facultyNameToDelete').text(facultyName);
            $('#deleteModal').modal('show');
        });

        // Confirm Delete
        $('#confirmDelete').on('click', function() {
            if (!facultyToDelete) return;

            const $btn = $(this);
            const spinner = $btn.find('.spinner-border');

            // Show loading
            $btn.prop('disabled', true);
            spinner.removeClass('d-none');

            // Send AJAX request
            $.ajax({
                url: '/quanlydoan/Faculties/delete/' + facultyToDelete,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        showAlert('success', response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert('danger', response.message);
                        $('#deleteModal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    showAlert('danger', 'Có lỗi xảy ra khi xóa khoa. Vui lòng thử lại.');
                    $('#deleteModal').modal('hide');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                    spinner.addClass('d-none');
                    facultyToDelete = null;
                }
            });
        });

        // Reset modals when closed
        $('.modal').on('hidden.bs.modal', function() {
            $(this).find('form').removeClass('was-validated')[0]?.reset();
            $(this).find('.alert').remove();
        });

        // Helper functions
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
        }

        function showAlert(type, message) {
            const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
            $('.container-fluid').prepend(alertHtml);

            // Auto remove alert after 5 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
    }

    // Chờ cho đến khi DOM và jQuery đã sẵn sàng
    if (typeof jQuery === 'undefined') {
        // Nếu jQuery chưa được load, thử load lại sau
        setTimeout(function() {
            if (typeof jQuery !== 'undefined') {
                initializeFacultyManagement();
            } else {
                console.error('jQuery vẫn chưa được load. Kiểm tra lại thư viện jQuery.');
            }
        }, 100);
    } else {
        // jQuery đã sẵn sàng, khởi tạo ngay
        $(document).ready(function() {
            initializeFacultyManagement();
        });
    }
</script>