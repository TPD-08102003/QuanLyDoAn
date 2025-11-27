<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-1"><i class="bi bi-people-fill me-2"></i>Quản lý Nhóm Sinh viên</h3>
            <p class="text-muted mb-0">Danh sách các nhóm đăng ký đồ án</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="bg-white p-2 rounded shadow-sm border">
                <span class="text-muted small text-uppercase fw-bold me-2">Tổng số nhóm:</span>
                <span class="badge bg-primary fs-6 rounded-pill" id="totalCount"><?php echo count($groups); ?></span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Tìm tên đồ án, trưởng nhóm hoặc MSSV...">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <select class="form-select" id="statusFilter">
                        <option value="all">-- Tất cả trạng thái --</option>
                        <option value="active">Đang hoạt động</option>
                        <option value="locked">Đã khóa</option>
                    </select>
                </div>
                <div class="col-md-2 text-end ms-auto">
                    <button class="btn btn-outline-secondary" onclick="location.reload()" title="Tải lại dữ liệu">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="groupsTable">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 py-3" width="5%">ID</th>
                            <th width="35%">Thông tin Đồ án</th>
                            <th width="20%">Trưởng nhóm</th>
                            <th class="text-center" width="15%">Thành viên</th>
                            <th class="text-center" width="10%">Trạng thái</th>
                            <th class="text-end pe-4" width="15%">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($groups)): ?>
                            <?php foreach ($groups as $group): ?>
                                <tr class="group-row"
                                    data-status="<?php echo (isset($group['is_locked']) && $group['is_locked'] == 1) ? 'locked' : 'active'; ?>">

                                    <td class="ps-4 fw-bold text-muted">#<?php echo $group['group_id']; ?></td>

                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark fs-6 text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($group['project_title']); ?>">
                                                <?php echo htmlspecialchars($group['project_title']); ?>
                                            </span>
                                            <small class="text-muted mt-1">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                Ngày tạo: <?php echo date('d/m/Y', strtotime($group['created_at'])); ?>
                                            </small>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                                <?php
                                                $parts = explode(' ', $group['leader_name']);
                                                echo strtoupper(substr(end($parts), 0, 1));
                                                ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark search-target"><?php echo htmlspecialchars($group['leader_name']); ?></div>
                                                <small class="text-muted search-target"><?php echo htmlspecialchars($group['leader_mssv']); ?></small>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <?php
                                        global $pdo;
                                        $gmModel = new \App\Models\GroupModel($pdo);
                                        $count = $gmModel->getMemberCount($group['group_id']);
                                        ?>
                                        <span class="badge bg-light text-dark border border-secondary border-opacity-25 rounded-pill px-3 py-2">
                                            <i class="bi bi-people me-1"></i> <?php echo $count; ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <?php if (isset($group['is_locked']) && $group['is_locked'] == 1): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-10 rounded-pill px-3">
                                                <i class="bi bi-lock-fill me-1"></i> Đã khóa
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success border-opacity-10 rounded-pill px-3">
                                                <i class="bi bi-check-circle-fill me-1"></i> Hoạt động
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <?php if (isset($group['is_locked']) && $group['is_locked'] == 1): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                    onclick="toggleLock(<?php echo $group['group_id']; ?>, 0)"
                                                    title="Mở khóa nhóm">
                                                    <i class="bi bi-unlock-fill"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-warning"
                                                    onclick="toggleLock(<?php echo $group['group_id']; ?>, 1)"
                                                    title="Khóa nhóm này">
                                                    <i class="bi bi-lock-fill"></i>
                                                </button>
                                            <?php endif; ?>

                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                                onclick="deleteGroup(<?php echo $group['group_id']; ?>)"
                                                title="Xóa nhóm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <img src="/quanlydoan/assets/images/empty-state.svg" alt="No Data" style="width: 80px; opacity: 0.5;" onerror="this.style.display='none'">
                                    <p class="text-muted mt-3">Chưa có nhóm nào được tạo.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="noResults" class="text-center py-5 d-none">
                <i class="bi bi-search fs-1 text-muted opacity-25"></i>
                <p class="text-muted mt-2">Không tìm thấy nhóm nào phù hợp với từ khóa.</p>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Chức năng Tìm kiếm và Lọc (Client-side)
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const rows = document.querySelectorAll('.group-row');
        const noResults = document.getElementById('noResults');
        const totalCountBadge = document.getElementById('totalCount');

        function filterGroups() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusTerm = statusFilter.value;
            let visibleCount = 0;

            rows.forEach(row => {
                const textContent = row.innerText.toLowerCase();
                const rowStatus = row.getAttribute('data-status');

                // Điều kiện Tìm kiếm
                const matchesSearch = textContent.includes(searchTerm);
                // Điều kiện Lọc
                const matchesFilter = (statusTerm === 'all') || (statusTerm === rowStatus);

                if (matchesSearch && matchesFilter) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Hiển thị thông báo nếu không có kết quả
            if (visibleCount === 0 && rows.length > 0) {
                noResults.classList.remove('d-none');
            } else {
                noResults.classList.add('d-none');
            }

            // Cập nhật số lượng hiển thị
            totalCountBadge.textContent = visibleCount;
        }

        searchInput.addEventListener('keyup', filterGroups);
        statusFilter.addEventListener('change', filterGroups);
    });

    // 2. Hàm xử lý Khóa / Mở khóa (Giữ nguyên logic của bạn nhưng thêm loading UI)
    function toggleLock(groupId, newStatus) {
        const actionText = newStatus === 1 ? 'KHÓA' : 'MỞ KHÓA';
        const confirmMsg = newStatus === 1 ?
            `Bạn có muốn khóa nhóm #${groupId}? Nhóm sẽ không thể chat hoặc nộp báo cáo.` :
            `Bạn có muốn mở khóa nhóm #${groupId}?`;

        if (confirm(confirmMsg)) {
            // Hiển thị trạng thái đang xử lý (Optional UX improvement)
            document.body.style.cursor = 'wait';

            const formData = new FormData();
            formData.append('is_locked', newStatus);

            fetch(`/quanlydoan/Group/toggle_lock/${groupId}`, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    document.body.style.cursor = 'default';
                    if (data.success) {
                        // Reload trang để cập nhật UI
                        // Có thể dùng toast thông báo thay vì alert
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(err => {
                    document.body.style.cursor = 'default';
                    console.error(err);
                    alert('Có lỗi xảy ra khi kết nối server.');
                });
        }
    }

    // 3. Hàm xử lý Xóa nhóm
    function deleteGroup(id) {
        if (confirm('⚠️ CẢNH BÁO: Hành động này không thể hoàn tác!\n\nBạn sẽ xóa:\n- Nhóm và danh sách thành viên\n- Lịch sử chat và file báo cáo\n\nBạn có chắc chắn muốn xóa?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/quanlydoan/Group/destroy/${id}`;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>