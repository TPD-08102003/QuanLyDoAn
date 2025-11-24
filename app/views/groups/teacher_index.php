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
                                            <button class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
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