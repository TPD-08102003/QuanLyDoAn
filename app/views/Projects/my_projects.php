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

// Giả sử có dữ liệu thống kê từ controller
$stats = [
    'total' => $totalProjects ?? 0,
    'byStatus' => [
        'ChoDuyet' => $countByStatus['ChoDuyet'] ?? 0,
        'DaDuyet' => $countByStatus['DaDuyet'] ?? 0,
        'DangThucHien' => $countByStatus['DangThucHien'] ?? 0,
        'DaNopBaoCao' => $countByStatus['DaNopBaoCao'] ?? 0,
        'DaBaoVe' => $countByStatus['DaBaoVe'] ?? 0,
        'HoanThanh' => $countByStatus['HoanThanh'] ?? 0,
        'Huy' => $countByStatus['Huy'] ?? 0,
    ]
];

// Giả sử có danh sách khoa từ controller
$faculties = $faculties ?? [];
?>

<div class="container py-4">
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
                                    <span class="fw-bold text-muted">#<?php echo htmlspecialchars($project['project_id']); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3 text-primary">
                                            <i class="bi bi-book"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 300px;">
                                                <?php echo htmlspecialchars($project['title']); ?>
                                            </h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small"><?php echo htmlspecialchars($project['faculty_name'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <span class="text-muted small"><?php echo $project['current_students'] ?? 0; ?>/<?php echo $project['max_students'] ?? 0; ?></span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill <?php echo $status['class']; ?> px-3 py-2 fw-normal">
                                        <?php echo $status['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo isset($project['created_at']) ? date('d/m/Y', strtotime($project['created_at'])) : 'N/A'; ?></small>
                                </td>

                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary border-0 rounded-circle me-1 view-project"
                                            data-id="<?php echo $project['project_id']; ?>"
                                            title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="/quanlydoan/project/edit/<?php echo $project['project_id']; ?>"
                                            class="btn btn-sm btn-outline-warning border-0 rounded-circle me-1"
                                            title="Chỉnh sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 card-view d-none">
            <?php foreach ($projects as $project):
                $statusKey = $project['status'];
                $status = $statusConfig[$statusKey] ?? ['label' => $statusKey, 'class' => 'bg-secondary', 'icon' => 'bi-circle'];
            ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 border-bottom-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary">
                                    <i class="bi bi-book"></i>
                                </div>
                                <span class="badge rounded-pill <?php echo $status['class']; ?> px-3 py-2">
                                    <?php echo $status['label']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body py-0">
                            <h5 class="card-title fw-bold text-dark mb-2 text-truncate"><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="card-text text-muted small mb-3 line-clamp-2"><?php echo htmlspecialchars($project['description'] ?? ''); ?></p>
                        </div>
                        <div class="card-footer bg-white py-3 border-top-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?php echo isset($project['created_at']) ? date('d/m/Y', strtotime($project['created_at'])) : 'N/A'; ?>
                                </small>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary border-0 rounded-circle me-1 view-project"
                                        data-id="<?php echo $project['project_id']; ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="/quanlydoan/project/edit/<?php echo $project['project_id']; ?>"
                                        class="btn btn-sm btn-outline-warning border-0 rounded-circle me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Chuyển đổi chế độ xem (Giữ nguyên)
        const tableViewRadio = document.getElementById('tableView');
        const cardViewRadio = document.getElementById('cardView');
        const tableView = document.querySelector('.table-view');
        const cardView = document.querySelector('.card-view');

        if (tableViewRadio && cardViewRadio) {
            tableViewRadio.addEventListener('change', () => {
                tableView.classList.remove('d-none');
                cardView.classList.add('d-none');
            });
            cardViewRadio.addEventListener('change', () => {
                tableView.classList.add('d-none');
                cardView.classList.remove('d-none');
            });
        }

        // 2. Xử lý nút Xem chi tiết (CẬP NHẬT LOGIC RENDER)
        const viewBtns = document.querySelectorAll('.view-project');
        const viewModalEl = document.getElementById('viewProjectModal');
        const viewModal = new bootstrap.Modal(viewModalEl);
        const contentDiv = document.getElementById('projectDetailsContent');
        const titleEl = document.getElementById('modalProjectTitle');
        const idEl = document.getElementById('modalProjectId');

        viewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');

                // Reset UI
                viewModal.show();
                titleEl.textContent = 'Đang tải...';
                idEl.textContent = '...';
                contentDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-3 text-muted">Đang tải dữ liệu...</p></div>';

                // Gọi API
                fetch(`/quanlydoan/project/getProjectDetails/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.project) {
                            const p = data.project;
                            const members = data.members || [];

                            // Cập nhật Header/Footer Modal
                            titleEl.textContent = p.title;
                            idEl.textContent = '#' + p.project_id;

                            // Render HTML thành viên
                            let membersHtml = '';
                            if (members.length > 0) {
                                membersHtml = `<div class="row g-2 mt-2">`;
                                members.forEach(mem => {
                                    membersHtml += `
                                    <div class="col-md-6">
                                        <div class="p-2 border rounded d-flex align-items-center bg-white">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width:35px; height:35px;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-truncate" style="font-size: 0.9rem;">${mem.full_name}</div>
                                                <div class="text-muted small" style="font-size: 0.8rem;">${mem.mssv}</div>
                                            </div>
                                        </div>
                                    </div>`;
                                });
                                membersHtml += `</div>`;
                            } else {
                                membersHtml = `
                                <div class="text-center py-4 bg-white rounded-3 border border-dashed mt-2">
                                    <i class="bi bi-people text-muted fs-3"></i>
                                    <p class="text-muted small mb-0 mt-1">Chưa có thành viên nào đăng ký.</p>
                                </div>`;
                            }

                            // Render Layout (Copy cấu trúc từ list_for_student)
                            contentDiv.innerHTML = `
                            <div class="row g-4">
                                <div class="col-lg-7">
                                    <div class="d-flex flex-column h-100 gap-3">
                                        <div class="bg-light rounded-4 p-3 border-start border-4 border-info">
                                            <h6 class="fw-bold text-dark mb-3 text-uppercase small ls-1">
                                                <i class="bi bi-info-circle-fill me-2 text-info"></i>Thông tin chung
                                            </h6>
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <small class="text-muted d-block">Giảng viên</small>
                                                    <span class="fw-bold text-dark">${p.lecturer_name}</span>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <small class="text-muted d-block">Khoa</small>
                                                    <span class="fw-bold text-dark">${p.faculty_name || 'N/A'}</span>
                                                </div>
                                                <div class="col-12">
                                                    <small class="text-muted d-block mb-1">Trạng thái</small>
                                                    <span class="badge bg-${p.badge_color} px-3 py-2 rounded-pill">${p.status}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-light rounded-4 p-3 flex-grow-1 border-start border-4 border-primary">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold text-dark mb-0 text-uppercase small ls-1">
                                                    <i class="bi bi-people-fill me-2 text-primary"></i>Nhóm thực hiện
                                                </h6>
                                                <span class="badge bg-primary rounded-pill">${members.length} / ${p.max_students} SV</span>
                                            </div>
                                            <hr class="my-2 opacity-25">
                                            ${membersHtml}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-5">
                                    <div class="bg-white border rounded-4 p-4 h-100 shadow-sm position-relative overflow-hidden">
                                        <h5 class="fw-bold text-primary mb-3 position-relative z-1">
                                            Mô tả yêu cầu
                                        </h5>
                                        <div class="text-dark lh-base position-relative z-1" style="text-align: justify; white-space: pre-line; font-size: 0.95rem;">
                                            ${p.description ? p.description : '<em class="text-muted">Chưa có mô tả chi tiết.</em>'}
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        } else {
                            contentDiv.innerHTML = `<div class="alert alert-danger">Không tìm thấy thông tin đồ án.</div>`;
                        }
                    })
                    .catch(err => {
                        contentDiv.innerHTML = `<div class="alert alert-danger">Lỗi kết nối: ${err.message}</div>`;
                    });
            });
        });

        // 4. Xử lý xuất Excel
        const exportBtn = document.querySelector('.export-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xuất...';
                this.disabled = true;

                // Lấy các tham số tìm kiếm hiện tại
                const formData = new FormData(document.getElementById('searchForm'));
                const params = new URLSearchParams(formData).toString();

                fetch(`/quanlydoan/project/export?${params}`)
                    .then(response => response.blob())
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `danh-sach-do-an-${new Date().toISOString().split('T')[0]}.xlsx`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);

                        this.innerHTML = '<i class="bi bi-file-earmark-excel me-2"></i>Xuất Excel';
                        this.disabled = false;
                    })
                    .catch(err => {
                        alert('Lỗi xuất file: ' + err.message);
                        this.innerHTML = '<i class="bi bi-file-earmark-excel me-2"></i>Xuất Excel';
                        this.disabled = false;
                    });
            });
        }
    });
</script>