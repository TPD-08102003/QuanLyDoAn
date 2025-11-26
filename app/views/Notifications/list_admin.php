<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2><i class="bi bi-bell me-2 text-primary"></i>Thông báo hệ thống</h2>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/quanlydoan/HomeAdmin/index">Dashboard</a></li>
                <li class="breadcrumb-item active">Danh sách thông báo</li>
            </ol>
        </div>
        <button class="btn btn-outline-primary" onclick="markAllAsRead()">
            <i class="bi bi-check2-all me-2"></i>Đánh dấu tất cả đã đọc
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bell-slash display-4 mb-3 d-block"></i>
                    <p class="fs-5">Không có thông báo nào.</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush" id="notificationList">
                    <?php foreach ($notifications as $notif): ?>
                        <?php
                        $isUnread = ($notif['status'] === 'unread');
                        // Kiểm tra xem có phải thông báo duyệt đồ án không để hiện nút action
                        $isApprovalRequest = (stripos($notif['title'], 'duyệt') !== false || stripos($notif['message'], 'duyệt') !== false);
                        ?>
                        <div class="list-group-item p-4 transition-hover <?php echo $isUnread ? 'bg-primary-subtle border-start border-4 border-primary' : ''; ?>"
                            id="notif-<?php echo $notif['notification_id']; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1 me-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <?php if ($isUnread): ?>
                                            <span class="badge bg-primary me-2 rounded-pill">Mới</span>
                                        <?php endif; ?>
                                        <h5 class="mb-0 <?php echo $isUnread ? 'fw-bold text-primary' : 'text-dark'; ?>">
                                            <?php echo htmlspecialchars($notif['title']); ?>
                                        </h5>
                                        <small class="text-muted ms-auto d-md-none">
                                            <?php echo date('H:i d/m/Y', strtotime($notif['created_at'])); ?>
                                        </small>
                                    </div>

                                    <p class="mb-2 text-secondary <?php echo $isUnread ? 'fw-semibold' : ''; ?>">
                                        <?php echo nl2br(htmlspecialchars($notif['message'])); ?>
                                    </p>

                                    <div class="d-flex align-items-center gap-2 mt-3">
                                        <small class="text-muted d-none d-md-inline-block">
                                            <i class="bi bi-clock me-1"></i>
                                            <?php echo date('H:i, d/m/Y', strtotime($notif['created_at'])); ?>
                                        </small>

                                        <?php if ($isApprovalRequest): ?>
                                            <a href="/quanlydoan/Project/approve" class="btn btn-sm btn-success rounded-pill px-3">
                                                <i class="bi bi-check-circle me-1"></i>Đi tới Duyệt đồ án
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="d-flex flex-column align-items-end gap-2">
                                    <small class="text-muted d-none d-md-block text-nowrap">
                                        <?php echo date('d/m/Y', strtotime($notif['created_at'])); ?>
                                    </small>
                                    <?php if ($isUnread): ?>
                                        <button class="btn btn-sm btn-light text-primary rounded-circle shadow-sm"
                                            title="Đánh dấu đã đọc"
                                            onclick="markAsRead(<?php echo $notif['notification_id']; ?>)">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function markAsRead(id) {
        fetch('/quanlydoan/Notification/markRead/' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.getElementById('notif-' + id);
                    // Xóa style chưa đọc
                    item.classList.remove('bg-primary-subtle', 'border-start', 'border-4', 'border-primary');
                    // Update title style
                    const title = item.querySelector('h5');
                    title.classList.remove('fw-bold', 'text-primary');
                    title.classList.add('text-dark');
                    // Update text style
                    const text = item.querySelector('p');
                    text.classList.remove('fw-semibold');

                    // Ẩn nút check và badge
                    const checkBtn = item.querySelector('button[title="Đánh dấu đã đọc"]');
                    if (checkBtn) checkBtn.remove();

                    const badge = item.querySelector('.badge.bg-primary');
                    if (badge) badge.remove();

                    // Cập nhật lại số lượng trên Navbar
                    updateAdminBadgeCount();
                }
            })
            .catch(err => console.error('Error:', err));
    }

    function markAllAsRead() {
        if (!confirm('Bạn có chắc muốn đánh dấu tất cả là đã đọc?')) return;

        fetch('/quanlydoan/Notification/markAllRead')
            .then(() => {
                window.location.reload();
            });
    }
</script>