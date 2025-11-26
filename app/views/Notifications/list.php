<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bell-fill text-warning me-2"></i>Thông báo của bạn</h2>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php if (empty($notifications)): ?>
                <div class="alert alert-info text-center">
                    Bạn chưa có thông báo nào.
                </div>
            <?php else: ?>
                <div class="list-group shadow-sm">
                    <?php foreach ($notifications as $notif): ?>
                        <?php
                        $isUnread = ($notif['status'] === 'unread');
                        $bgClass = $isUnread ? 'bg-light border-primary border-start border-4' : 'bg-white';
                        $textClass = $isUnread ? 'fw-bold' : 'text-secondary';
                        ?>
                        <div class="list-group-item list-group-item-action p-4 mb-2 rounded border <?php echo $bgClass; ?>"
                            id="notif-<?php echo $notif['notification_id']; ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h5 class="mb-1 <?php echo $isUnread ? 'text-primary' : ''; ?>">
                                    <?php echo htmlspecialchars($notif['title']); ?>
                                </h5>
                                <small class="text-muted">
                                    <?php echo date('H:i d/m/Y', strtotime($notif['created_at'])); ?>
                                </small>
                            </div>
                            <p class="mb-1 mt-2 <?php echo $textClass; ?>">
                                <?php echo nl2br(htmlspecialchars($notif['message'])); ?>
                            </p>

                            <?php if ($isUnread): ?>
                                <div class="mt-3 text-end">
                                    <button class="btn btn-sm btn-outline-primary"
                                        onclick="markAsRead(<?php echo $notif['notification_id']; ?>)">
                                        <i class="bi bi-check2-all me-1"></i>Đánh dấu đã đọc
                                    </button>
                                </div>
                            <?php endif; ?>
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
                    // Cập nhật giao diện: bỏ highlight, ẩn nút
                    const item = document.getElementById('notif-' + id);
                    item.classList.remove('bg-light', 'border-primary', 'border-start', 'border-4');
                    item.classList.add('bg-white');
                    item.querySelector('h5').classList.remove('text-primary');
                    item.querySelector('p').classList.remove('fw-bold');
                    item.querySelector('p').classList.add('text-secondary');

                    // Xóa nút bấm
                    const btnDiv = item.querySelector('.text-end');
                    if (btnDiv) btnDiv.remove();

                    // Cập nhật badge số lượng trên menu (nếu có script toàn cục)
                    updateNotificationCount();
                }
            });
    }

    // Hàm giả lập cập nhật badge số lượng trên Header
    function updateNotificationCount() {
        const badge = document.querySelector('.badge.bg-danger');
        if (badge) {
            let count = parseInt(badge.innerText);
            if (count > 0) {
                badge.innerText = count - 1;
                if (count - 1 === 0) badge.style.display = 'none';
            }
        }
    }
</script>