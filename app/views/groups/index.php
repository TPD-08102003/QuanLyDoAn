<style>
    /* CSS Tùy chỉnh cho trang này */
    .avatar-initial {
        width: 40px;
        height: 40px;
        background-color: #e9ecef;
        color: #495057;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: bold;
        font-size: 16px;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: all 0.2s;
    }

    .project-title {
        font-size: 1rem;
        color: #2c3e50;
        transition: color 0.2s;
    }

    .table-header-custom th {
        background-color: #f1f3f5;
        color: #495057;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .btn-icon-circle {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin: 0 2px;
    }
</style>

<div class="container py-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold text-primary mb-1">
                <i class="bi bi-people-fill me-2"></i>Quản lý Nhóm Sinh viên
            </h2>
            <p class="text-muted mb-0">Danh sách các nhóm và đồ án đang thực hiện</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="/quanlydoan/groups/create" class="btn btn-success shadow-sm">
                <i class="bi bi-plus-lg me-2"></i>Tạo nhóm mới
            </a>
        </div>
    </div>

    <div class="card shadow border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-header-custom">
                        <tr>
                            <th class="ps-4 text-center" style="width: 80px;">ID</th>
                            <th>Thông tin Đồ án</th>
                            <th>Trưởng nhóm</th>
                            <th class="text-center">Ngày tạo</th>
                            <th class="text-center" style="width: 150px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($groups)): ?>
                            <?php foreach ($groups as $group): ?>
                                <tr>
                                    <td class="ps-4 text-center">
                                        <span class="badge bg-light text-secondary border rounded-pill">
                                            #<?php echo $group['group_id']; ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if (isset($group['project_title'])): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <i class="bi bi-folder2-open text-warning fs-4"></i>
                                                </div>
                                                <div>
                                                    <a href="/quanlydoan/groups/show/<?php echo $group['group_id']; ?>" class="fw-bold text-decoration-none project-title text-dark stretched-link">
                                                        <?php echo htmlspecialchars($group['project_title']); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic"><i class="bi bi-exclamation-circle me-1"></i>Chưa đăng ký (ID: <?php echo $group['project_id']; ?>)</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (isset($group['leader_name'])): ?>
                                            <div class="d-flex align-items-center">
                                                <?php
                                                $firstLetter = mb_substr($group['leader_name'], 0, 1, "UTF-8");
                                                // Mảng màu ngẫu nhiên cho avatar
                                                $colors = ['bg-primary', 'bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-secondary'];
                                                $randomColor = $colors[$group['group_id'] % count($colors)];
                                                ?>
                                                <div class="avatar-initial <?php echo $randomColor; ?> bg-opacity-10 text-dark me-3">
                                                    <?php echo $firstLetter; ?>
                                                </div>

                                                <div>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($group['leader_name']); ?></div>
                                                    <div class="small text-muted">
                                                        <i class="bi bi-person-badge me-1"></i><?php echo htmlspecialchars($group['leader_mssv'] ?? 'N/A'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Student ID: <?php echo $group['leader_id']; ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="small text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($group['created_at'])); ?>
                                        </div>
                                    </td>

                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center">
                                            <a href="/quanlydoan/groups/show/<?php echo $group['group_id']; ?>"
                                                class="btn btn-icon-circle btn-outline-info me-2"
                                                data-bs-toggle="tooltip" title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="/quanlydoan/groups/edit/<?php echo $group['group_id']; ?>"
                                                class="btn btn-icon-circle btn-outline-warning me-2"
                                                data-bs-toggle="tooltip" title="Chỉnh sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <button type="button"
                                                class="btn btn-icon-circle btn-outline-danger"
                                                onclick="confirmDelete(<?php echo $group['group_id']; ?>)"
                                                data-bs-toggle="tooltip" title="Xóa nhóm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <form id="delete-form-<?php echo $group['group_id']; ?>"
                                            action="/quanlydoan/groups/destroy/<?php echo $group['group_id']; ?>"
                                            method="POST" style="display: none;">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                        <h5 class="fw-normal">Chưa có nhóm nào được tạo</h5>
                                        <p class="small">Hãy bắt đầu bằng cách nhấn nút "Tạo nhóm mới"</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Khởi tạo Tooltip của Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    function confirmDelete(id) {
        Swal.fire({
            title: 'Xóa nhóm này?',
            text: "Dữ liệu thành viên trong nhóm cũng sẽ bị xóa khỏi nhóm!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // Màu đỏ cho nút xóa
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Đồng ý xóa',
            cancelButtonText: 'Hủy bỏ',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>