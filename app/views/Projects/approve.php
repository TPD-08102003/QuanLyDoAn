<?php
// views/projects/approve.php

// Định nghĩa trạng thái đồ án (Đảm bảo đồng bộ với manage.php)
$statusMapping = [
    'ChoDuyet' => ['class' => 'bg-warning text-dark', 'text' => 'Chờ Duyệt'],
    'DaDuyet' => ['class' => 'bg-info text-dark', 'text' => 'Đã Duyệt'],
    'DangThucHien' => ['class' => 'bg-primary', 'text' => 'Đang Thực Hiện'],
    'DaNopBaoCao' => ['class' => 'bg-secondary', 'text' => 'Đã Nộp Báo Cáo'],
    'DaBaoVe' => ['class' => 'bg-success', 'text' => 'Đã Bảo Vệ'],
    'HoanThanh' => ['class' => 'bg-success', 'text' => 'Hoàn Thành'],
    'Huy' => ['class' => 'bg-danger', 'text' => 'Hủy']
];

// Khởi tạo biến nếu chưa tồn tại (giá trị mặc định)
$projects = $projects ?? [];
$keyword = $keyword ?? '';
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalProjects = $totalProjects ?? 0;

// Đường dẫn cơ sở cho phân trang và tìm kiếm
$baseUrl = '/quanlydoan/Project/approve';
$query = !empty($keyword) ? "&keyword=" . urlencode($keyword) : "";
?>

<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h2 page-title">Duyệt Đồ án</h1>
            <div class="page-subtitle text-muted">Danh sách các đồ án đang ở trạng thái **Chờ Duyệt** (<?php echo $totalProjects; ?> đồ án).</div>
        </div>
        <div class="col-auto">
            <button id="approveBatchBtn" class="btn btn-success" disabled>
                <i class="bi bi-check-all me-2"></i>Duyệt (0) mục đã chọn
            </button>
            <button id="rejectBatchBtn" class="btn btn-danger" disabled>
                <i class="bi bi-x-circle me-2"></i>Từ chối (0) mục đã chọn
            </button>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= $baseUrl ?>" class="row g-3 align-items-center">
            <div class="col-12 col-md-8 col-lg-6">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm theo tiêu đề, giảng viên..." value="<?= htmlspecialchars($keyword) ?>">
            </div>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-2"></i>Tìm kiếm</button>
                <a href="<?= $baseUrl ?>" class="btn btn-outline-secondary"><i class="bi bi-x me-2"></i>Xóa lọc</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">

        <?php if (empty($projects)): ?>
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle me-2"></i>Không tìm thấy đồ án nào đang chờ duyệt.
            </div>
        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="width: 30px;"><input type="checkbox" id="selectAll"></th>
                            <th style="width: 50px;">ID</th>
                            <th>Tiêu đề Đồ án</th>
                            <th>Giảng viên HD</th>
                            <th style="width: 150px;">Ngày đăng ký</th>
                            <th style="width: 100px;">Trạng thái</th>
                            <th style="width: 220px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr data-id="<?= $project['project_id'] ?>">
                                <td><input type="checkbox" class="project-checkbox" value="<?= $project['project_id'] ?>"></td>
                                <td><?= htmlspecialchars($project['project_id'] ?? 'N/A'); ?></td>
                                <td><?= htmlspecialchars($project['title'] ?? 'N/A'); ?></td>
                                <td><?= htmlspecialchars($project['lecturer_name'] ?? 'Chưa phân công'); ?></td>
                                <td><?= date('d/m/Y', strtotime($project['created_at'] ?? date('Y-m-d'))); ?></td>
                                <td>
                                    <?php
                                    $status = $project['status'] ?? 'N/A';
                                    $statusInfo = $statusMapping[$status] ?? ['class' => 'bg-secondary', 'text' => 'Không rõ'];
                                    ?>
                                    <span class="badge <?= $statusInfo['class']; ?>"><?= $statusInfo['text']; ?></span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info detail-btn me-1" data-id="<?= $project['project_id'] ?>" data-bs-toggle="modal" data-bs-target="#detailModal">
                                        <i class="bi bi-eye"></i> Chi tiết
                                    </button>

                                    <?php if ($status === 'ChoDuyet'): ?>
                                        <button type="button" class="btn btn-sm btn-success approve-btn me-1" data-id="<?= $project['project_id'] ?>">
                                            <i class="bi bi-check-lg"></i> Duyệt
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger reject-btn" data-id="<?= $project['project_id'] ?>">
                                            <i class="bi bi-x-lg"></i> Từ chối
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">Hiển thị <?= count($projects) ?> / <?= $totalProjects ?> đồ án</small>
                <nav>
                    <ul class="pagination mb-0">
                        <?php if ($totalPages > 1): ?>
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars("{$baseUrl}?page=" . max(1, $page - 1) . $query) ?>">Trước</a>
                            </li>

                            <?php
                            // Hiển thị tối đa 5 nút trang (hoặc tùy chỉnh)
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);

                            if ($startPage > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . htmlspecialchars("{$baseUrl}?page=1" . $query) . '">1</a></li>';
                                if ($startPage > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }

                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= htmlspecialchars("{$baseUrl}?page=" . $i . $query) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor;

                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . htmlspecialchars("{$baseUrl}?page=" . $totalPages . $query) . '">' . $totalPages . '</a></li>';
                            }
                            ?>

                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars("{$baseUrl}?page=" . min($totalPages, $page + 1) . $query) ?>">Sau</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>

        <?php endif; ?>

    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Chi tiết Đồ án</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center py-5">Đang tải...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Khai báo biến DOM và dữ liệu ---
        const approveBatchBtn = document.getElementById('approveBatchBtn');
        const rejectBatchBtn = document.getElementById('rejectBatchBtn');
        const selectAllCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.project-checkbox');
        const tableBody = document.querySelector('tbody');
        const detailContent = document.getElementById('detailContent');

        // Lấy statusMapping từ PHP (đã được định nghĩa ở đầu file)
        const statusMapping = <?= json_encode($statusMapping) ?>;

        /**
         * Chức năng gửi yêu cầu AJAX sử dụng fetch API
         * @param {string} url - Đường dẫn API (Controller endpoint)
         * @param {object} data - Dữ liệu gửi đi (POST body)
         * @param {string} actionText - Tên hành động (Duyệt/Từ chối)
         */
        function sendAjaxRequest(url, data, actionText) {
            const formData = new URLSearchParams(data);

            fetch(url, {
                    method: 'POST',
                    headers: {
                        // Cần gửi header này vì PHP Controller của bạn dùng $_POST
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        alert(`${actionText} thành công!`);
                        window.location.reload(); // Tải lại trang sau khi thành công
                    } else {
                        alert(result.message || `${actionText} thất bại!`);
                    }
                })
                .catch(error => {
                    console.error('Lỗi AJAX:', error);
                    alert(`Đã xảy ra lỗi kết nối hoặc xử lý phía máy chủ: ${error.message}`);
                });
        }

        /**
         * Cập nhật trạng thái và số lượng mục đã chọn cho nút duyệt hàng loạt
         */
        function updateBatchButtons() {
            const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);
            const count = checkedBoxes.length;

            approveBatchBtn.disabled = count === 0;
            rejectBatchBtn.disabled = count === 0;

            approveBatchBtn.innerHTML = `<i class="bi bi-check-all me-2"></i>Duyệt (${count}) mục đã chọn`;
            rejectBatchBtn.innerHTML = `<i class="bi bi-x-circle me-2"></i>Từ chối (${count}) mục đã chọn`;

            // Cập nhật trạng thái checkbox "Chọn tất cả"
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = (count > 0 && count === checkboxes.length);
            }
        }

        /**
         * Hàm điều phối việc thay đổi trạng thái
         */
        function changeProjectStatus(id, status) {
            const isBatch = Array.isArray(id);
            const url = isBatch ? '/quanlydoan/Project/changeStatusBatch' : `/quanlydoan/Project/changeStatus/${id}`;

            // Controller của bạn nhận POST data nên phải tạo object tương ứng
            const data = isBatch ? {
                ids: id,
                status: status
            } : {
                status: status
            };

            const actionText = status === 'DaDuyet' ? 'Duyệt' : 'Từ chối';
            const confirmationMsg = `Bạn có chắc chắn muốn ${actionText} ${isBatch ? id.length + ' đồ án này?' : 'đồ án ID ' + id + '?'}`;

            if (confirm(confirmationMsg)) {
                sendAjaxRequest(url, data, actionText);
            }
        }

        // --- 2. Sự kiện Checkbox ---
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateBatchButtons();
            });
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateBatchButtons));

        // --- 3. Sự kiện Duyệt/Từ chối hàng loạt ---
        approveBatchBtn.addEventListener('click', function() {
            const checkedIds = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            changeProjectStatus(checkedIds, 'DaDuyet');
        });

        rejectBatchBtn.addEventListener('click', function() {
            const checkedIds = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            changeProjectStatus(checkedIds, 'Huy'); // Sử dụng 'Huy' cho trạng thái Từ chối
        });

        // Khởi tạo trạng thái ban đầu
        updateBatchButtons();

        // --- 4. Sự kiện Duyệt/Từ chối từng mục và Chi tiết (Sử dụng Event Delegation) ---
        tableBody.addEventListener('click', function(e) {
            const target = e.target.closest('.approve-btn, .reject-btn, .detail-btn');
            if (!target) return;

            const id = target.getAttribute('data-id');

            if (target.classList.contains('approve-btn')) {
                changeProjectStatus(id, 'DaDuyet');
            } else if (target.classList.contains('reject-btn')) {
                changeProjectStatus(id, 'Huy');
            } else if (target.classList.contains('detail-btn')) {
                // Hiển thị loading state
                detailContent.innerHTML = `<div class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Đang tải...</div>`;

                // Tải chi tiết đồ án vào Modal
                fetch(`/quanlydoan/Project/getProjectDetails/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.project) {
                            const project = data.project;
                            const statusInfo = statusMapping[project.status] || {
                                class: 'bg-secondary',
                                text: 'Không rõ'
                            };

                            const html = `
                                <h6>Mã Đồ án: <span class="text-primary">${project.project_id}</span></h6>
                                <p><strong>Tiêu đề:</strong> ${project.title}</p>
                                <p><strong>Mô tả:</strong> ${project.description || 'Không có mô tả chi tiết.'}</p>
                                <p><strong>Giảng viên Hướng dẫn:</strong> ${project.lecturer_name || 'Chưa phân công'}</p>
                                <p><strong>Khoa:</strong> ${project.faculty_name || 'N/A'}</p>
                                <p><strong>Ngày đăng ký:</strong> ${new Date(project.created_at).toLocaleDateString('vi-VN')}</p>
                                <p><strong>Trạng thái:</strong> <span class="badge ${statusInfo.class}">${statusInfo.text}</span></p>
                            `;
                            detailContent.innerHTML = html;
                        } else {
                            detailContent.innerHTML = `<div class="alert alert-danger">${data.message || 'Không tìm thấy chi tiết đồ án.'}</div>`;
                        }
                    })
                    .catch(error => {
                        detailContent.innerHTML = `<div class="alert alert-danger">Lỗi kết nối: ${error.message}</div>`;
                    });
            }
        });
    });
</script>