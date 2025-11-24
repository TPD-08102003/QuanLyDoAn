<style>
    /* CSS cho Chat Box */
    .chat-container {
        height: 500px;
        display: flex;
        flex-direction: column;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #fff;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        background-color: #f8f9fa;
        display: flex;
        flex-direction: column;
        gap: 15px;
        /* Tăng khoảng cách giữa các tin nhắn */
    }

    .message-item {
        display: flex;
        align-items: flex-end;
        max-width: 75%;
        /* Giới hạn chiều rộng tin nhắn */
        margin-bottom: 5px;
    }

    .message-item.me {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        margin: 0 8px;
        object-fit: cover;
        flex-shrink: 0;
    }

    /* Bong bóng tin nhắn trở thành 1 container flex dọc */
    .message-bubble {
        padding: 8px 12px;
        border-radius: 12px;
        position: relative;
        font-size: 0.95rem;
        word-wrap: break-word;
        display: flex;
        flex-direction: column;
        min-width: 120px;
        /* Đảm bảo đủ rộng để chứa tên và giờ */
    }

    /* Style cho tin nhắn của mình */
    .message-item.me .message-bubble {
        background-color: #0d6efd;
        color: white;
        border-bottom-right-radius: 2px;
    }

    /* Style cho tin nhắn người khác */
    .message-item.other .message-bubble {
        background-color: #e9ecef;
        color: #212529;
        border-bottom-left-radius: 2px;
    }

    /* Tên người gửi (Bên trong bong bóng, ở trên cùng) */
    .message-sender {
        font-weight: 700;
        font-size: 0.8rem;
        margin-bottom: 4px;
        color: #0d6efd;
        /* Màu tên cho nổi bật trên nền xám */
    }

    /* Ẩn tên đối với tin nhắn của chính mình (hoặc có thể để nếu muốn) */
    .message-item.me .message-sender {
        display: none;
    }

    /* Nội dung tin nhắn */
    .message-text {
        margin-bottom: 4px;
        line-height: 1.4;
    }

    /* Thời gian (Bên trong bong bóng, góc dưới phải) */
    .message-time {
        font-size: 0.65rem;
        align-self: flex-end;
        /* Đẩy sang phải */
        opacity: 0.8;
    }

    /* Màu thời gian cho tin nhắn của mình (chữ trắng) */
    .message-item.me .message-time {
        color: rgba(255, 255, 255, 0.9);
    }

    /* Màu thời gian cho tin nhắn người khác (chữ xám) */
    .message-item.other .message-time {
        color: #6c757d;
    }

    .chat-input-area {
        padding: 1rem;
        background-color: #fff;
        border-top: 1px solid #dee2e6;
    }

    .message-recalled {
        font-style: italic;
        color: #6c757d !important;
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6;
    }

    /* Nút thu hồi (thùng rác) */
    .btn-recall {
        font-size: 0.7rem;
        color: #dc3545;
        /* Màu đỏ */
        background: none;
        border: none;
        padding: 0 5px;
        margin-right: 5px;
        cursor: pointer;
        opacity: 0;
        /* Mặc định ẩn */
        transition: opacity 0.2s;
    }

    /* Chỉ hiện nút xóa khi hover vào tin nhắn của mình */
    .message-item.me:hover .btn-recall {
        opacity: 1;
    }

    .message-content-wrapper {
        display: flex;
        align-items: center;
    }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/quanlydoan">Trang chủ</a></li>
                <?php if (!empty($is_teacher)): ?>
                    <li class="breadcrumb-item"><a href="/quanlydoan/group">Quản lý nhóm</a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page">Nhóm của tôi</li>
            </ol>
        </nav>

        <?php if (!empty($is_teacher)): ?>
            <a href="/quanlydoan/group" class="btn btn-sm btn-outline-secondary" style="color: blue">
                <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
            </a>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Thông tin nhóm</h5>
                </div>
                <div class="card-body">
                    <h5 class="card-title text-primary fw-bold mb-3">
                        <?php echo htmlspecialchars($group['project_title'] ?? 'Chưa có tên đồ án'); ?>
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><small class="text-muted">GVHD:</small> <strong><?php echo htmlspecialchars($group['lecturer_name'] ?? 'Chưa phân công'); ?></strong></li>
                        <li class="mb-2">
                            <small class="text-muted">Trạng thái:</small>
                            <?php
                            $statusMap = ['ChoDuyet' => ['Chờ duyệt', 'warning'], 'DaDuyet' => ['Đã duyệt', 'info'], 'DangThucHien' => ['Đang thực hiện', 'primary'], 'DaNopBaoCao' => ['Đã nộp BC', 'secondary'], 'DaBaoVe' => ['Đã bảo vệ', 'success'], 'HoanThanh' => ['Hoàn thành', 'success'], 'Huy' => ['Đã hủy', 'danger']];
                            $st = $statusMap[$group['project_status'] ?? 'ChoDuyet'] ?? ['Không xác định', 'secondary'];
                            ?>
                            <span class="badge bg-<?php echo $st[1]; ?>"><?php echo $st[0]; ?></span>
                        </li>
                    </ul>
                </div>
                <div class="card-footer bg-white border-top-0 pb-3">
                    <?php if (empty($is_teacher)): // Chỉ hiện cho sinh viên 
                    ?>
                        <a href="/quanlydoan/project/myProjects" class="btn btn-outline-primary w-100 btn-sm">
                            <i class="bi bi-arrow-right-circle me-2"></i>Đến trang nộp bài
                        </a>
                    <?php else: ?>
                        <span class="text-muted small d-block text-center">Bạn đang xem với tư cách Giảng viên</span>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light p-2 text-center">
                    <?php if (empty($is_teacher)): // Chỉ sinh viên mới có chức năng rời nhóm 
                    ?>
                        <?php if ($group['leader_id'] != $current_student_id): ?>
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="confirmLeave(<?php echo $group['group_id']; ?>)">
                                Rời nhóm
                            </button>
                        <?php else: ?>
                            <small class="text-muted fst-italic">Trưởng nhóm không thể tự rời</small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 text-primary"><i class="bi bi-people me-2"></i>Thành viên</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($members as $mem): ?>
                        <div class="list-group-item p-2">
                            <div class="d-flex align-items-center">
                                <?php $avatarPath = !empty($mem['avatar']) ? '/quanlydoan/assets/images/' . $mem['avatar'] : '/quanlydoan/assets/images/profile.png'; ?>
                                <img src="<?php echo htmlspecialchars($avatarPath); ?>" class="rounded-circle border me-2" width="40" height="40" style="object-fit: cover;">
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold small text-truncate"><?php echo htmlspecialchars($mem['full_name']); ?></span>
                                        <?php if ($group['leader_id'] == $mem['student_id']): ?>
                                            <i class="bi bi-star-fill text-warning" title="Trưởng nhóm"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($mem['mssv']); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary"><i class="bi bi-chat-dots-fill me-2"></i>Thảo luận nhóm</h5>
                    <span class="badge bg-success rounded-pill">Online</span>
                </div>

                <div class="chat-container border-0">
                    <div id="chat-messages" class="chat-messages">
                        <div class="text-center text-muted mt-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Đang tải tin nhắn...</p>
                        </div>
                    </div>

                    <div class="chat-input-area">
                        <form id="chat-form" class="d-flex gap-2">
                            <input type="hidden" id="group_id" value="<?php echo $group['group_id']; ?>">
                            <input type="hidden" id="current_user_id" value="<?php echo $current_user_id; ?>">

                            <input type="text" id="message-input" class="form-control" placeholder="Nhập tin nhắn..." autocomplete="off">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatMessages = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        const groupId = document.getElementById('group_id').value;
        const currentUserId = document.getElementById('current_user_id').value;
        let isFirstLoad = true;

        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Hàm render tin nhắn
        function renderMessages(messages) {
            if (messages.length === 0) {
                chatMessages.innerHTML = '<div class="text-center text-muted mt-5"><i class="bi bi-chat-square-dots fs-1"></i><p>Chưa có tin nhắn nào.</p></div>';
                return;
            }

            let html = '';

            messages.forEach(msg => {
                const isMe = (msg.user_id == currentUserId);
                const className = isMe ? 'me' : 'other';

                // Tên người gửi (chỉ hiện nếu không phải mình)
                const nameHtml = !isMe ? `<div class="message-sender">${msg.full_name}</div>` : '';

                // Nội dung tin nhắn
                let messageContent = '';
                let actionButton = '';

                if (msg.is_recalled) {
                    // Nếu đã thu hồi
                    messageContent = `<div class="message-bubble message-recalled shadow-sm">
                                        ${nameHtml}
                                        <div class="message-text small">Tin nhắn đã bị thu hồi</div>
                                        <div class="message-time">${msg.time}</div>
                                      </div>`;
                } else {
                    // Tin nhắn bình thường
                    // Nếu là tin của mình -> Thêm nút xóa
                    if (isMe) {
                        actionButton = `<button class="btn-recall" onclick="recallMessage(${msg.message_id})" title="Thu hồi tin nhắn"><i class="bi bi-trash"></i></button>`;
                    }

                    messageContent = `<div class="message-bubble shadow-sm">
                                        ${nameHtml}
                                        <div class="message-text">${msg.message}</div>
                                        <div class="message-time">${msg.time}</div>
                                      </div>`;
                }

                // Wrapper để chứa nút xóa + bong bóng chat
                html += `
                    <div class="message-item ${className}">
                        <img src="${msg.avatar}" class="message-avatar" title="${msg.full_name}">
                        <div class="message-content-wrapper">
                            ${actionButton} ${messageContent}
                        </div>
                    </div>
                `;
            });

            // Chỉ update DOM nếu nội dung thay đổi để tránh nháy (ở đây replace luôn cho đơn giản)
            // Trong thực tế nên so sánh độ dài hoặc ID cuối
            if (chatMessages.innerHTML !== html) {
                chatMessages.innerHTML = html;
                if (isFirstLoad) {
                    scrollToBottom();
                    isFirstLoad = false;
                }
            }
        }

        // Hàm load tin nhắn
        function loadMessages() {
            fetch(`/quanlydoan/group/getMessages/${groupId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderMessages(data.messages);
                    }
                })
                .catch(err => console.error('Lỗi tải tin nhắn:', err));
        }

        // Gửi tin nhắn
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            const formData = new FormData();
            formData.append('group_id', groupId);
            formData.append('message', message);

            fetch('/quanlydoan/group/sendMessage', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        loadMessages();
                        setTimeout(scrollToBottom, 200);
                    }
                });
        });

        // Hàm thu hồi tin nhắn (Global function để gọi từ onclick)
        window.recallMessage = function(messageId) {
            if (!confirm('Bạn muốn thu hồi tin nhắn này?')) return;

            const formData = new FormData();
            formData.append('message_id', messageId);

            fetch('/quanlydoan/group/recallMessage', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadMessages(); // Load lại để cập nhật giao diện
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => console.error('Lỗi:', err));
        };

        // Polling
        loadMessages();
        setInterval(loadMessages, 3000);
    });
</script>