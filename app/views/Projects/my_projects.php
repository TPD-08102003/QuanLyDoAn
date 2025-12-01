<?php
// views/Projects/my_projects.php

// Định nghĩa màu sắc và tên hiển thị cho trạng thái
$statusConfig = [
    'ChoDuyet'      => ['label' => 'Chờ duyệt', 'class' => 'bg-warning text-dark', 'icon' => 'bi-hourglass-split'],
    'DaDuyet'       => ['label' => 'Đã duyệt', 'class' => 'bg-info text-dark', 'icon' => 'bi-check-circle'],
    'DangThucHien'  => ['label' => 'Đang thực hiện', 'class' => 'bg-primary', 'icon' => 'bi-play-circle'],
    'DaNopBaoCao'   => ['label' => 'Đã nộp báo cáo', 'class' => 'bg-secondary', 'icon' => 'bi-file-earmark-text'],
    'DaBaoVe'       => ['label' => 'Đã bảo vệ', 'class' => 'bg-success', 'icon' => 'bi-shield-check'],
    'HoanThanh'     => ['label' => 'Hoàn thành', 'class' => 'bg-success', 'icon' => 'bi-trophy'],
    'Huy'           => ['label' => 'Đã hủy', 'class' => 'bg-danger', 'icon' => 'bi-x-circle'],
];
?>

<div class="container py-4">
    <div class="card mb-4 border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h2 class="fw-bold mb-0">Quản lý đồ án</h2>
            </div>

            <hr class="my-4 opacity-10">

            <form method="GET" action="/quanlydoan/project/myProjects" class="row g-3 align-items-center">
                <div class="col-md-4 col-lg-3">
                    <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <?php foreach ($statusConfig as $key => $val): ?>
                            <option value="<?= $key ?>" <?= (isset($selectedStatus) && $selectedStatus === $key) ? 'selected' : '' ?>>
                                <?= $val['label'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Lọc</button>
                </div>

                <div class="col-md-6 col-lg-7 d-flex justify-content-md-end gap-2">
                    <a href="/quanlydoan/project/createByLecturer" class="btn btn-primary rounded-pill">
                        <i class="bi bi-plus-lg me-2"></i>Thêm mới
                    </a>

                    <a href="/quanlydoan/project/exportByLecturer" class="btn btn-success rounded-pill">
                        <i class="bi bi-download me-2"></i>Xuất Excel
                    </a>

                    <button type="button" class="btn btn-info text-white rounded-pill" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload me-2"></i>Nhập Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($projects)): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden table-view">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 80px;">ID</th>
                            <th style="min-width: 250px;">Tên Đồ án</th>
                            <th>Khoa</th>
                            <th>Số SV</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project):
                            $statusKey = $project['status'];
                            $status = $statusConfig[$statusKey] ?? ['label' => $statusKey, 'class' => 'bg-secondary', 'icon' => 'bi-circle'];
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-muted">#<?= htmlspecialchars($project['project_id']); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3 text-primary">
                                            <i class="bi bi-book"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 300px;">
                                                <?= htmlspecialchars($project['title']); ?>
                                            </h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= htmlspecialchars($project['faculty_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        <?= $project['current_students'] ?? 0; ?>/<?= $project['max_students'] ?? 0; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill <?= $status['class']; ?> px-3 py-2 fw-normal">
                                        <?= $status['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= isset($project['created_at']) ? date('d/m/Y', strtotime($project['created_at'])) : 'N/A'; ?></small>
                                </td>

                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary border-0 rounded-circle me-1 view-project"
                                            data-id="<?= $project['project_id']; ?>"
                                            title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <?php if ($role === 'teacher'): ?>
                                            <a href="/quanlydoan/project/edit/<?= $project['project_id']; ?>"
                                                class="btn btn-sm btn-outline-warning border-0 rounded-circle me-1"
                                                title="Chỉnh sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="/quanlydoan/score/manage/<?= $project['project_id']; ?>"
                                            class="btn btn-sm btn-outline-success border-0 rounded-circle me-1"
                                            title="Quản lý điểm số">
                                            <i class="bi bi-calculator"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (isset($totalPages) && $totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php
                    // Tạo query string giữ lại các tham số tìm kiếm và lọc hiện tại
                    // Đã loại bỏ 'faculty_id' vì Giảng viên không cần dùng
                    $queryParams = http_build_query([
                        'keyword' => $keyword ?? '',
                        'status' => $selectedStatus ?? ''
                    ]);
                    ?>

                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link rounded-pill px-3 me-1" href="?page=<?php echo $page - 1; ?>&<?php echo $queryParams; ?>">
                            <i class="bi bi-chevron-left"></i> Trước
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link rounded-pill px-3 me-1" href="?page=<?php echo $i; ?>&<?php echo $queryParams; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link rounded-pill px-3" href="?page=<?php echo $page + 1; ?>&<?php echo $queryParams; ?>">
                            Sau <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <div class="text-center py-5">
            <img src="/quanlydoan/assets/images/empty-state.svg" alt="Không có dữ liệu" style="max-width: 200px; opacity: 0.5;">
            <p class="text-muted mt-3">Không tìm thấy đồ án nào phù hợp.</p>
        </div>
    <?php endif; ?>
</div>


<div class="modal fade" id="viewProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 overflow-hidden">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalProjectTitle">Đang tải...</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="projectDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Đang tải thông tin đồ án...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <small class="text-muted me-auto">Mã đồ án: <strong id="modalProjectId">...</strong></small>
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="importProjectForm" enctype="multipart/form-data">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-2"></i>Nhập Đồ án từ Excel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel (.xlsx)</label>
                        <input type="file" name="excelFile" class="form-control" accept=".xlsx" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="update_existing" id="updateExisting">
                        <label class="form-check-label" for="updateExisting">
                            Cập nhật nếu tiêu đề đồ án đã tồn tại
                        </label>
                    </div>
                    <div class="alert alert-light border">
                        <small><i class="bi bi-info-circle me-1"></i> Tải file mẫu:
                            <a href="/quanlydoan/project/downloadTemplateForLecturer" class="fw-bold text-decoration-underline">Tại đây</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-info text-white" id="btnSubmitImport">
                        Nhập dữ liệu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Xử lý Xem chi tiết (Logic cũ)
        const viewBtns = document.querySelectorAll('.view-project');
        const viewModalEl = document.getElementById('viewProjectModal');
        const viewModal = new bootstrap.Modal(viewModalEl);
        const contentDiv = document.getElementById('projectDetailsContent');
        const titleEl = document.getElementById('modalProjectTitle');
        const idEl = document.getElementById('modalProjectId');

        viewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                viewModal.show();
                // Reset modal content
                titleEl.textContent = 'Đang tải...';
                contentDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

                fetch(`/quanlydoan/project/getProjectDetails/${id}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const p = data.project;
                            titleEl.textContent = p.title;
                            idEl.textContent = '#' + p.project_id;

                            // Render nội dung chi tiết (giữ nguyên logic render HTML của bạn)
                            let html = `
                            <div class="p-3">
                                <p><strong>Giảng viên:</strong> ${p.lecturer_name}</p>
                                <p><strong>Trạng thái:</strong> <span class="badge bg-${p.badge_color}">${p.status}</span></p>
                                <p><strong>Mô tả:</strong> ${p.description || 'Không có'}</p>
                                <hr>
                                <h6>Thành viên nhóm (${data.members.length}):</h6>
                                <ul class="list-group">
                        `;
                            if (data.members.length > 0) {
                                data.members.forEach(m => {
                                    html += `<li class="list-group-item">${m.full_name} (${m.mssv})</li>`;
                                });
                            } else {
                                html += `<li class="list-group-item text-muted">Chưa có thành viên</li>`;
                            }
                            html += `</ul></div>`;
                            contentDiv.innerHTML = html;
                        } else {
                            contentDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                        }
                    })
                    .catch(e => contentDiv.innerHTML = `<div class="alert alert-danger">Lỗi kết nối</div>`);
            });
        });

        // 2. [MỚI] Xử lý Import Excel bằng AJAX
        const importForm = document.getElementById('importProjectForm');
        if (importForm) {
            importForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('btnSubmitImport');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';

                const formData = new FormData(this);

                fetch('/quanlydoan/project/importByLecturer', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Lỗi: ' + data.message);
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    })
                    .catch(e => {
                        alert('Lỗi kết nối server');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
            });
        }
    });
</script>