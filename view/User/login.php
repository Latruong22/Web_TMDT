<?php
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Snowboard Shop</title>
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Css/User/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="row g-0 h-100">
            <!-- Left Side - Login Form -->
            <div class="col-lg-5">
                <div class="form-side">
                    <div class="form-container">
                        <!-- Mobile Logo -->
                        <div class="mobile-logo d-lg-none text-center mb-4">
                            <img src="../../Images/logo/logo.jpg" alt="Logo" class="img-fluid rounded-3" style="max-width: 120px;">
                            <h4 class="mt-3 fw-bold">SNOWBOARD SHOP</h4>
                        </div>

                        <!-- Back to Home -->
                        <div class="back-to-home mb-4">
                            <a href="home.php" class="text-decoration-none text-dark">
                                <i class="fas fa-arrow-left me-2"></i>Về trang chủ
                            </a>
                        </div>

                        <h2 class="form-title">Đăng nhập</h2>
                        <p class="form-subtitle text-muted">Chào mừng bạn trở lại!</p>

                        <!-- Alert Messages -->
                        <?php
                        $messages = [
                            'success' => ['type' => 'success', 'icon' => 'fa-check-circle', 'text' => 'Đăng ký thành công! Vui lòng đăng nhập.'],
                            'fail' => ['type' => 'danger', 'icon' => 'fa-times-circle', 'text' => 'Tài khoản hoặc mật khẩu không đúng!'],
                            'verify' => ['type' => 'info', 'icon' => 'fa-info-circle', 'text' => 'Vui lòng kiểm tra email để xác nhận tài khoản trước khi đăng nhập.'],
                            'verified' => ['type' => 'success', 'icon' => 'fa-check-circle', 'text' => 'Email đã được xác nhận! Bạn có thể đăng nhập ngay bây giờ.'],
                            'pending' => ['type' => 'warning', 'icon' => 'fa-exclamation-triangle', 'text' => 'Tài khoản chưa được xác nhận! Vui lòng kiểm tra email.'],
                            'locked' => ['type' => 'danger', 'icon' => 'fa-lock', 'text' => 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.'],
                            'timeout' => ['type' => 'info', 'icon' => 'fa-clock', 'text' => 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.'],
                            'reset_success' => ['type' => 'success', 'icon' => 'fa-check-circle', 'text' => 'Mật khẩu đã được đặt lại thành công! Vui lòng đăng nhập.'],
                            'resent' => ['type' => 'info', 'icon' => 'fa-envelope', 'text' => 'Email xác nhận đã được gửi lại. Vui lòng kiểm tra hộp thư đến của bạn.']
                        ];

                        if ($msg && isset($messages[$msg])):
                            $alert = $messages[$msg];
                        ?>
                            <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                                <i class="fas <?php echo $alert['icon']; ?> me-2"></i>
                                <?php echo $alert['text']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form method="post" action="../../controller/controller_User/controller.php" class="auth-form">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="example@email.com" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Mật khẩu
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Nhập mật khẩu" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">
                                        Ghi nhớ đăng nhập
                                    </label>
                                </div>
                                <a href="forgot_password.php" class="text-decoration-none">Quên mật khẩu?</a>
                            </div>

                            <button type="submit" name="login" class="btn btn-dark btn-lg w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                            </button>

                            <div class="text-center">
                                <p class="text-muted mb-0">Chưa có tài khoản? 
                                    <a href="register.php" class="fw-bold text-dark text-decoration-none">Đăng ký ngay</a>
                                </p>
                            </div>
                        </form>

                        <!-- Resend Verification -->
                        <?php if ($msg == 'pending'): ?>
                            <div class="resend-section mt-4">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center">
                                        <p class="mb-2"><i class="fas fa-envelope-open-text me-2"></i>Chưa nhận được email xác nhận?</p>
                                        <form method="post" action="../../controller/controller_User/email_controller.php" class="d-inline">
                                            <input type="hidden" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
                                            <button type="submit" name="resend_verification" class="btn btn-sm btn-outline-dark">
                                                <i class="fas fa-redo me-1"></i>Gửi lại email
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
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
                            <h2 class="banner-title">SNOWBOARD SHOP</h2>
                            <p class="banner-subtitle">Chinh phục đỉnh tuyết - Thách thức giới hạn</p>
                            <div class="banner-features">
                                <div class="banner-feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="feature-text">
                                        <strong>Chất lượng đảm bảo</strong>
                                        <small>Sản phẩm chính hãng 100%</small>
                                    </div>
                                </div>
                                <div class="banner-feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-shipping-fast"></i>
                                    </div>
                                    <div class="feature-text">
                                        <strong>Giao hàng nhanh</strong>
                                        <small>Miễn phí toàn quốc</small>
                                    </div>
                                </div>
                                <div class="banner-feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-headset"></i>
                                    </div>
                                    <div class="feature-text">
                                        <strong>Hỗ trợ 24/7</strong>
                                        <small>Tư vấn chuyên nghiệp</small>
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
    <script src="../../Js/User/login.js"></script>
</body>
</html>