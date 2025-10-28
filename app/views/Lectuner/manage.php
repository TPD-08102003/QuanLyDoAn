<?php
$title = 'Quản lý Giảng viên';
$page_title = 'Quản lý Giảng viên';
$page_subtitle = 'Danh sách và quản lý thông tin giảng viên';
$breadcrumb = ['Quản lý Giảng viên'];

// Giả lập dữ liệu - trong thực tế sẽ được truyền từ controller
$lecturers = isset($lecturers) ? $lecturers : [];
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh sách Giảng viên</h6>
        <a href="/quanlydoan/Lecturer/create" class="btn btn-primary btn-sm">
            <i class="bi bi-person-add me-1"></i>Thêm Giảng viên
        </a>
    </div>
    <div class="card-body">
        <!-- Search and Filter -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm giảng viên...">
                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="departmentFilter">
                    <option value="">Tất cả khoa</option>
                    <option value="CNTT">Công nghệ Thông tin</option>
                    <option value="DTVT">Điện tử Viễn thông</option>
                    <option value="KT">Kế toán</option>
                    <option value="QTKD">Quản trị Kinh doanh</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active">Đang hoạt động</option>
                    <option value="inactive">Ngừng hoạt động</option>
                </select>
            </div>
        </div>

        <!-- Lecturers Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="lecturersTable" width="100%" cellspacing="0">
                <thead class="table-dark">
                    <tr>
                        <th width="50">#</th>
                        <th>Thông tin Giảng viên</th>
                        <th>Khoa</th>
                        <th>Số đồ án</th>
                        <th>Trạng thái</th>
                        <th width="120">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lecturers)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-person-x fa-2x mb-2"></i>
                                    <p>Chưa có dữ liệu giảng viên</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lecturers as $index => $lecturer): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($lecturer['avatar'] ?? '/quanlydoan/assets/images/profile.png'); ?>"
                                            alt="Avatar" class="rounded-circle me-3"
                                            style="width: 40px; height: 40px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($lecturer['full_name'] ?? 'N/A'); ?></div>
                                            <small class="text-muted">
                                                <i class="bi bi-envelope me-1"></i>
                                                <?php echo htmlspecialchars($lecturer['email'] ?? 'N/A'); ?>
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-telephone me-1"></i>
                                                <?php echo htmlspecialchars($lecturer['phone'] ?? 'N/A'); ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($lecturer['department'] ?? 'Chưa cập nhật'); ?></span>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo $lecturer['project_count'] ?? 0; ?> đồ án
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($lecturer['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ($lecturer['status'] ?? 'active') === 'active' ? 'Đang hoạt động' : 'Ngừng hoạt động'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="/quanlydoan/Lecturer/show/<?php echo $lecturer['lecturer_id'] ?? $lecturer['id']; ?>"
                                            class="btn btn-info" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/quanlydoan/Lecturer/edit/<?php echo $lecturer['lecturer_id'] ?? $lecturer['id']; ?>"
                                            class="btn btn-warning" title="Sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger delete-lecturer"
                                            data-id="<?php echo $lecturer['lecturer_id'] ?? $lecturer['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($lecturer['full_name'] ?? ''); ?>"
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
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Hiển thị <span id="showingCount"><?php echo count($lecturers); ?></span> trên tổng số
                <span id="totalCount"><?php echo count($lecturers); ?></span> giảng viên
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#">Trước</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Tiếp</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Tổng Giảng viên
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalLecturers"><?php echo count($lecturers); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Đang hoạt động
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="activeLecturers">
                            <?php
                            $activeCount = array_filter($lecturers, function ($lecturer) {
                                return ($lecturer['status'] ?? 'active') === 'active';
                            });
                            echo count($activeCount);
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Tổng đồ án
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalProjects">
                            <?php
                            $totalProjects = array_sum(array_column($lecturers, 'project_count'));
                            echo $totalProjects;
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-folder fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Chưa phân công
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="unassignedLecturers">
                            <?php
                            $unassignedCount = array_filter($lecturers, function ($lecturer) {
                                return ($lecturer['project_count'] ?? 0) === 0;
                            });
                            echo count($unassignedCount);
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-x fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
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
                <p>Bạn có chắc chắn muốn xóa giảng viên <strong id="deleteLecturerName"></strong>?</p>
                <p class="text-danger"><small>Hành động này không thể hoàn tác. Tất cả dữ liệu liên quan đến giảng viên này sẽ bị xóa.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Xóa</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        const table = $('#lecturersTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
            },
            responsive: true,
            columnDefs: [{
                    orderable: false,
                    targets: [5]
                } // Disable sorting for actions column
            ]
        });

        // Search functionality
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
            updateCounts();
        });

        $('#searchButton').on('click', function() {
            table.search($('#searchInput').val()).draw();
            updateCounts();
        });

        // Filter functionality
        $('#departmentFilter').on('change', function() {
            table.column(2).search(this.value).draw();
            updateCounts();
        });

        $('#statusFilter').on('change', function() {
            const statusText = this.value === 'active' ? 'Đang hoạt động' :
                this.value === 'inactive' ? 'Ngừng hoạt động' : '';
            table.column(4).search(statusText).draw();
            updateCounts();
        });

        // Update counts
        function updateCounts() {
            const filteredCount = table.rows({
                search: 'applied'
            }).count();
            $('#showingCount').text(filteredCount);
        }

        // Delete lecturer functionality
        let deleteLecturerId = null;

        $('.delete-lecturer').on('click', function() {
            deleteLecturerId = $(this).data('id');
            const lecturerName = $(this).data('name');

            $('#deleteLecturerName').text(lecturerName);
            $('#deleteModal').modal('show');
        });

        $('#confirmDelete').on('click', function() {
            if (deleteLecturerId) {
                // Send delete request
                fetch(`/quanlydoan/Lecturer/destroy/${deleteLecturerId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload page or remove row
                            location.reload();
                        } else {
                            alert('Có lỗi xảy ra khi xóa giảng viên: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi xóa giảng viên');
                    })
                    .finally(() => {
                        $('#deleteModal').modal('hide');
                    });
            }
        });

        // Update counts on table draw
        table.on('draw', function() {
            updateCounts();
        });

        // Export functionality
        $('#exportBtn').on('click', function() {
            // Implement export functionality here
            alert('Tính năng export đang được phát triển');
        });
    });

    // Quick actions
    function quickAction(action, lecturerId) {
        switch (action) {
            case 'assignProject':
                window.location.href = `/quanlydoan/Project/create?lecturer_id=${lecturerId}`;
                break;
            case 'viewProjects':
                window.location.href = `/quanlydoan/Project/manage?lecturer_id=${lecturerId}`;
                break;
            case 'sendMessage':
                // Implement send message functionality
                alert('Tính năng gửi tin nhắn đang được phát triển');
                break;
        }
    }
</script>

<style>
    .table th {
        border-top: none;
        font-weight: 600;
    }

    .btn-group-sm>.btn {
        padding: 0.25rem 0.5rem;
    }

    .badge {
        font-size: 0.75em;
    }

    .card {
        border: none;
        border-radius: 0.5rem;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e3e6f0;
    }

    .dataTables_wrapper {
        margin-top: 1rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .btn-group {
            display: flex;
            flex-direction: column;
        }

        .btn-group .btn {
            margin-bottom: 0.25rem;
            border-radius: 0.25rem !important;
        }

        .table-responsive {
            font-size: 0.875rem;
        }
    }
</style>