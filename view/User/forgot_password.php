<?php
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Snowboard Shop</title>
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Css/User/forgot_password.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="forgot-password-container">
        <div class="row g-0 h-100">
            <!-- Left Side - Forgot Password Form -->
            <div class="col-lg-5">
                <div class="form-side">
                    <div class="form-container">
                        <!-- Mobile Logo -->
                        <div class="mobile-logo d-lg-none text-center mb-4">
                            <img src="../../Images/logo/logo.jpg" alt="Logo" class="img-fluid rounded-3" style="max-width: 120px;">
                            <h4 class="mt-3 fw-bold">SNOWBOARD SHOP</h4>
                        </div>

                        <!-- Back to Login -->
                        <div class="back-to-home mb-4">
                            <a href="login.php" class="text-decoration-none text-dark">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại đăng nhập
                            </a>
                        </div>

                        <?php if ($msg == 'sent'): ?>
                            <!-- Success State -->
                            <div class="text-center mb-4">
                                <div class="success-icon mb-4">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h2 class="form-title">Email đã được gửi!</h2>
                                <p class="form-subtitle text-muted">Vui lòng kiểm tra hộp thư đến của bạn</p>
                            </div>

                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-envelope-open-text me-2"></i>
                                <strong>Link khôi phục mật khẩu</strong> đã được gửi đến email của bạn.
                            </div>

                            <div class="info-card">
                                <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Lưu ý:</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Link có hiệu lực trong 1 giờ</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Kiểm tra cả thư mục spam</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Yêu cầu lại nếu không nhận được</li>
                                </ul>
                            </div>

                            <div class="text-center mt-4">
                                <a href="login.php" class="btn btn-dark btn-lg w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Về trang đăng nhập
                                </a>
                            </div>

                        <?php else: ?>
                            <!-- Form State -->
                            <div class="text-center mb-4">
                                <div class="forgot-icon mb-3">
                                    <i class="fas fa-key"></i>
                                </div>
                                <h2 class="form-title">Quên mật khẩu?</h2>
                                <p class="form-subtitle text-muted">Nhập email để nhận link đặt lại mật khẩu</p>
                            </div>

                            <form method="post" action="../../controller/controller_User/email_controller.php" class="auth-form" id="forgotPasswordForm">
                                <div class="mb-4">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Địa chỉ Email
                                    </label>
                                    <input type="email" 
                                           class="form-control form-control-lg" 
                                           id="email" 
                                           name="email" 
                                           placeholder="example@email.com" 
                                           required
                                           autofocus>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Nhập email bạn đã sử dụng để đăng ký tài khoản
                                    </div>
                                </div>

                                <button type="submit" name="forgot_password" class="btn btn-dark btn-lg w-100 mb-3">
                                    <i class="fas fa-paper-plane me-2"></i>Gửi link khôi phục
                                </button>

                                <div class="text-center">
                                    <p class="text-muted mb-0">
                                        Nhớ mật khẩu? 
                                        <a href="login.php" class="fw-bold text-dark text-decoration-none">Đăng nhập ngay</a>
                                    </p>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Side - Banner Image -->
            <div class="col-lg-7 d-none d-lg-block p-0">
                <div class="banner-side">
                    <img src="../../Images/baner/baner6.jpg" alt="Snowboard Banner" class="banner-image">
                    <div class="banner-overlay">
                        <div class="banner-content">
                            <div class="logo-circle">
                                <img src="../../Images/logo/logo.jpg" alt="Logo" class="banner-logo">
                            </div>
                            <h2 class="banner-title">KHÔI PHỤC TÀI KHOẢN</h2>
                            <p class="banner-subtitle">Đừng lo lắng - chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
                            <div class="banner-features">
                                <div class="banner-feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="feature-text">
                                        <strong>Bảo mật tuyệt đối</strong>
                                        <small>Mã xác thực được mã hóa an toàn</small>
                                    </div>
                                </div>
                                <div class="banner-feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                    <div class="feature-text">
                                        <strong>Xử lý nhanh chóng</strong>
                                        <small>Nhận email khôi phục ngay lập tức</small>
                                    </div>
                                </div>
                                <div class="banner-feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="feature-text">
                                        <strong>Hiệu lực 1 giờ</strong>
                                        <small>Link khôi phục có giá trị trong 60 phút</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission with loading state
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgotPasswordForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang gửi email...';
                        submitBtn.disabled = true;

                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 5000);
                    }
                });
            }

            // Email validation
            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
                    if (this.value && !emailRegex.test(this.value)) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });
            }
        });
    </script>
</body>
</html>