<?php
// views/classes/manage.php
?>

<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-lg-7">
                            <form method="GET" action="/quanlydoan/classes/manage" class="row g-3 align-items-end">
                                <input type="hidden" name="page" value="1">
                                <div class="col-md-6 col-lg-8"> <label for="keyword" class="form-label">Tìm kiếm lớp học</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="keyword" name="keyword"
                                            value="<?php echo htmlspecialchars($keyword ?? ''); ?>"
                                            placeholder="Nhập tên lớp học...">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search me-1"></i>Tìm kiếm
                                        </button>
                                    </div>
                                </div>
                                <?php if (!empty($keyword)): ?>
                                    <div class="col-12 mt-2">
                                        <a href="/quanlydoan/classes/manage" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>

                        <div class="col-lg-5">
                            <div class="d-flex gap-2 justify-content-lg-end mt-3 mt-lg-0">
                                <button type="button" class="btn btn-success flex-fill flex-lg-grow-0" data-bs-toggle="modal" data-bs-target="#addClassModal">
                                    <i class="bi bi-plus-circle me-1"></i>Thêm lớp học
                                </button>
                                <button type="button" class="btn btn-outline-success flex-fill flex-lg-grow-0"
                                    onclick="exportToExcel()" <?php echo empty($classes) ? 'disabled' : ''; ?>>
                                    <i class="bi bi-cloud-download"></i> Xuất Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Danh sách Lớp học</h5>
                    <span class="badge bg-primary"><?php echo $total ?? 0; ?> lớp học</span>
                </div>
                <div class="card-body">
                    <?php if (empty($classes)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">Không có lớp học nào</h5>
                            <p class="text-muted mb-4">Hãy thêm lớp học đầu tiên để bắt đầu quản lý.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="classesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="20%">Tên lớp</th>
                                        <th width="15%">Khoa</th>
                                        <th width="30%">Mô tả</th>
                                        <th width="15%">Ngày tạo</th>
                                        <th width="15%" class="text-center">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $index => $class): ?>
                                        <tr>
                                            <td><?php echo (($page - 1) * $limit) + $index + 1; ?></td>
                                            <td>
                                                <span><?php echo htmlspecialchars($class['class_name'] ?? ''); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-dark"><?php echo htmlspecialchars($class['faculty_name'] ?? 'Chưa xác định'); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-truncate-2" title="<?php echo htmlspecialchars($class['description'] ?? 'Không có mô tả'); ?>">
                                                    <?php
                                                    $description = $class['description'] ?? 'Không có mô tả';
                                                    echo strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description;
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($class['created_at'] ?? 'now')); ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary view-class-btn"
                                                        data-class-id="<?php echo $class['class_id']; ?>"
                                                        title="Chi tiết"
                                                        data-bs-toggle="tooltip">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-warning edit-class-btn"
                                                        data-class-id="<?php echo $class['class_id']; ?>"
                                                        title="Sửa"
                                                        data-bs-toggle="tooltip">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmDelete(<?php echo $class['class_id']; ?>, '<?php echo htmlspecialchars(addslashes($class['class_name'])); ?>')"
                                                        title="Xóa"
                                                        data-bs-toggle="tooltip">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Hiển thị <?php echo (($page - 1) * $limit) + 1; ?> -
                        <?php echo min($page * $limit, $total); ?> của <?php echo $total; ?> lớp học
                    </div>
                    <nav aria-label="Phân trang lớp học">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&keyword=<?php echo urlencode($keyword ?? ''); ?>&limit=<?php echo $limit; ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            if ($startPage > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=1&keyword=<?php echo urlencode($keyword ?? ''); ?>&limit=<?php echo $limit; ?>">1</a></li>
                                <?php if ($startPage > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&keyword=<?php echo urlencode($keyword ?? ''); ?>&limit=<?php echo $limit; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $totalPages; ?>&keyword=<?php echo urlencode($keyword ?? ''); ?>&limit=<?php echo $limit; ?>"><?php echo $totalPages; ?></a></li>
                            <?php endif; ?>

                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&keyword=<?php echo urlencode($keyword ?? ''); ?>&limit=<?php echo $limit; ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="modal fade" id="addClassModal" tabindex="-1" aria-labelledby="addClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addClassModalLabel">Thêm Lớp Học Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addClassForm" method="POST" action="/quanlydoan/classes/store">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="class_name" class="form-label">Tên lớp học <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="class_name" name="class_name" required>
                                <div class="invalid-feedback" id="class_name_error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="faculty_id" class="form-label">Khoa <span class="text-danger">*</span></label>
                                <select class="form-select" id="faculty_id" name="faculty_id" required>
                                    <option value="">Chọn khoa</option>
                                    <?php foreach ($faculties ?? [] as $faculty): ?>
                                        <option value="<?php echo $faculty['faculty_id']; ?>">
                                            <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback" id="faculty_id_error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                            placeholder="Nhập mô tả về lớp học..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>Thêm lớp học
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="viewClassModal" tabindex="-1" aria-labelledby="viewClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewClassModalLabel">Chi Tiết Lớp Học</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên lớp học:</label>
                            <p class="text-primary" id="view_class_name"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Khoa:</label>
                            <p class="text-dark" id="view_faculty_name"></p>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả:</label>
                    <p id="view_description" class="text-muted"></p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ngày tạo:</label>
                            <p id="view_created_at" class="text-muted"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cập nhật lần cuối:</label>
                            <p id="view_updated_at" class="text-muted"></p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h6 class="fw-bold border-bottom pb-2">Danh sách sinh viên</h6>
                    <div id="studentList" class="mt-2">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editClassModalLabel">Chỉnh Sửa Lớp Học</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editClassForm" method="POST">
                <input type="hidden" id="edit_class_id" name="class_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_class_name" class="form-label">Tên lớp học <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_class_name" name="class_name" required>
                                <div class="invalid-feedback" id="edit_class_name_error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_faculty_id" class="form-label">Khoa <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_faculty_id" name="faculty_id" required>
                                    <option value="">Chọn khoa</option>
                                    <?php foreach ($faculties ?? [] as $faculty): ?>
                                        <option value="<?php echo $faculty['faculty_id']; ?>">
                                            <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback" id="edit_faculty_id_error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle me-1"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa lớp học</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa lớp học <strong id="className"></strong>?</p>
                <p class="text-danger mb-0"><small>Hành động này không thể hoàn tác và có thể ảnh hưởng đến dữ liệu liên quan (như sinh viên).</small></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-danger delete-btn"
                    data-id="<?= $class['class_id'] ?>"
                    data-name="<?= $class['class_name'] ?>"
                    onclick="confirmDelete('<?= $class['class_id'] ?>', '<?= htmlspecialchars($class['class_name']) ?>')">Xóa
                </button>
            </div>
        </div>
    </div>
</div>
<style>
    /* ... (CSS gốc) ... */
    .text-truncate-2 {
        display: -webkit-box;
        overflow: hidden;
    }

    .table th {
        border-top: none;
        font-weight: 600;
    }

    .btn {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Modal styles */
    .modal-header {
        border-bottom: 1px solid #dee2e6;
    }

    .modal-footer {
        border-top: 1px solid #dee2e6;
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .col-lg-4 .d-flex {
            flex-direction: column;
        }

        .col-lg-4 .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }

    /* Loading spinner */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    /* Thêm CSS tùy chỉnh để đảm bảo chữ trong bảng là màu đen và không in đậm, nếu cần */
    .table tbody td {
        color: #212529 !important;
        /* Màu đen của Bootstrap */
        font-weight: normal !important;
        /* Không in đậm */
    }
</style>

<script src="../assets/js/class_manage.js"></script>