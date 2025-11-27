<div class="container py-4">
    <h2 class="text-primary mb-4"><i class="bi bi-mortarboard-fill me-2"></i>Các nhóm đang hướng dẫn</h2>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($groups)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    <p>Chưa có nhóm nào đăng ký đồ án của bạn.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="ps-4">Đồ án</th>
                                <th>Trưởng nhóm</th>
                                <th class="text-center">Thành viên</th>
                                <th class="text-center" style="width: 200px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($groups as $group): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-primary"><?php echo htmlspecialchars($group['project_title']); ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($group['leader_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($group['leader_mssv']); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill text-dark">
                                            <i class="bi bi-people me-1"></i> <?php echo $group['member_count']; ?> SV
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="/quanlydoan/group/chat/<?php echo $group['group_id']; ?>" class="btn btn-sm btn-success" title="Chat với nhóm">
                                                <i class="bi bi-chat-dots-fill me-1"></i> Chat
                                            </a>
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="showGroupDetails(<?php echo $group['group_id']; ?>)"
                                                title="Xem chi tiết">
                                                <i class="bi bi-eye me-1"></i> Chi tiết
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

<div class="modal fade" id="groupDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i>Chi tiết nhóm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h5 id="modalProjectTitle" class="fw-bold text-primary">Loading...</h5>
                    <span id="modalProjectStatus" class="badge bg-secondary">Loading...</span>
                </div>

                <h6>Danh sách thành viên:</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Họ tên</th>
                                <th>MSSV</th>
                                <th>SĐT</th>
                                <th class="text-center">Vai trò</th>
                            </tr>
                        </thead>
                        <tbody id="modalMembersBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showGroupDetails(groupId) {
        // 1. Reset Modal
        const modalTitle = document.getElementById('modalProjectTitle');
        const modalStatus = document.getElementById('modalProjectStatus');
        const tbody = document.getElementById('modalMembersBody');

        modalTitle.innerText = 'Đang tải dữ liệu...';
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Đang tải...</td></tr>';

        // Hiển thị Modal trước
        const myModal = new bootstrap.Modal(document.getElementById('groupDetailModal'));
        myModal.show();

        // 2. Gọi AJAX
        fetch(`/quanlydoan/group/apiGetDetails?id=${groupId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fill thông tin nhóm
                    modalTitle.innerText = data.group.title;
                    modalStatus.innerText = data.group.status; // Bạn có thể thêm logic đổi màu badge ở đây

                    let html = '';
                    data.members.forEach((mem, index) => {
                        const roleBadge = mem.is_leader == 1 ?
                            '<span class="badge bg-warning text-dark">Trưởng nhóm</span>' :
                            '<span class="badge bg-light text-secondary">Thành viên</span>';

                        const avatarPath = mem.avatar ?
                            `/quanlydoan/assets/images/${mem.avatar}` :
                            '/quanlydoan/assets/images/profile.png';

                        // Xử lý hiển thị SĐT (nếu null thì hiện ---)
                        const phone = mem.phone_number ? mem.phone_number : '---';

                        html += `
        <tr>
            <td class="text-center">
                <img src="${avatarPath}" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
            </td>
            <td class="fw-bold">${mem.full_name}</td>
            <td>${mem.mssv}</td>
            <td>${phone}</td>
            <td class="text-center">${roleBadge}</td>
        </tr>
    `;
                    });
                    tbody.innerHTML = html;
                } else {
                    alert(data.message);
                    myModal.hide();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi khi tải dữ liệu</td></tr>';
            });
    }
</script>