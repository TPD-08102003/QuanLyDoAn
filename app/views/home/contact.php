<!-- Header -->
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Liên Hệ Với Chúng Tôi</h1>
        <p class="text-muted">Bạn có thắc mắc hoặc cần hỗ trợ kỹ thuật? Hãy để lại tin nhắn.</p>
    </div>

    <div class="row g-5">
        <!-- Cột thông tin -->
        <div class="col-lg-5 contact-info">
            <div class="p-4 bg-light rounded shadow-sm h-100">
                <h3 class="fw-bold mb-4 text-primary"><i class="bi bi-info-circle me-2"></i>Thông tin liên lạc</h3>

                <div class="d-flex mb-4">
                    <div class="flex-shrink-0 btn-square bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold mb-1">Địa chỉ</h5>
                        <p class="text-muted mb-0">Đường Phạm Hữu Lầu, Phường Cao Lãnh, Đồng Tháp.</p>
                    </div>
                </div>

                <div class="d-flex mb-4">
                    <div class="flex-shrink-0 btn-square bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-telephone-fill fs-5"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold mb-1">Điện thoại</h5>
                        <p class="text-muted mb-0">(0277) 388 1234</p>
                        <small class="text-muted">Thứ 2 - Thứ 6, 8:00 - 17:00</small>
                    </div>
                </div>

                <div class="d-flex mb-4">
                    <div class="flex-shrink-0 btn-square bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-envelope-fill fs-5"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold mb-1">Email</h5>
                        <p class="text-muted mb-0">support@ql-doan.edu.vn</p>
                        <p class="text-muted mb-0">admin@ql-doan.edu.vn</p>
                    </div>
                </div>

                <div class="mt-4 rounded overflow-hidden shadow-sm">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3923.834694936306!2d105.6366553147116!3d10.45780879253818!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310a6566e01d1563%3A0x6a164e2d4d8c850!2sTr%C6%B0%E1%BB%9Dng%20%C4%90%E1%BA%A1i%20h%E1%BB%8Dc%20%C4%90%E1%BB%93ng%20Th%C3%A1p!5e0!3m2!1svi!2s!4v1684829302211!5m2!1svi!2s"
                        width="100%"
                        height="250"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- Cột Form -->
        <div class="col-lg-7">
            <div class="p-4 bg-white rounded shadow-sm border">
                <h3 class="fw-bold mb-4">Gửi tin nhắn</h3>

                <form action="/quanlydoan/contact/send" method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="contactName" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contactName" name="full_name" required placeholder="Nhập họ tên của bạn">
                        </div>
                        <div class="col-md-6">
                            <label for="contactEmail" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="contactEmail" name="email" required placeholder="name@example.com">
                        </div>

                        <div class="col-12">
                            <label for="contactSubject" class="form-label">Chủ đề</label>
                            <select class="form-select" id="contactSubject" name="subject">
                                <option value="general">Hỏi đáp chung</option>
                                <option value="support">Hỗ trợ kỹ thuật</option>
                                <option value="feedback">Góp ý hệ thống</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="contactMessage" class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="contactMessage" name="message" rows="5" required placeholder="Nhập nội dung tin nhắn..."></textarea>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-send-fill me-2"></i> Gửi tin nhắn
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>