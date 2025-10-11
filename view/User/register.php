<?php
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Snowboard Shop</title>
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Css/User/register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="register-container">
        <div class="row g-0 h-100">
            <!-- Left Side - Register Form -->
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

                        <h2 class="form-title">Đăng ký tài khoản</h2>
                        <p class="form-subtitle text-muted">Tạo tài khoản để bắt đầu mua sắm</p>

                        <!-- Alert Messages -->
                        <?php if ($msg == 'exists'): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Email này đã được đăng ký. Vui lòng sử dụng email khác hoặc <a href="login.php" class="alert-link">đăng nhập</a>.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($msg == 'password' || $msg == 'password_short'): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-key me-2"></i>
                                Mật khẩu phải có ít nhất 6 ký tự!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($msg == 'mismatch'): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Mật khẩu xác nhận không khớp!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($msg == 'email'): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-envelope me-2"></i>
                                Email không hợp lệ!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($msg == 'phone'): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-phone me-2"></i>
                                Số điện thoại không hợp lệ! (Ví dụ: 0912345678)
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Register Form -->
                        <form method="post" action="../../controller/controller_User/controller.php" class="auth-form" id="registerForm">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="fullname" class="form-label">
                                        <i class="fas fa-user me-2"></i>Họ và tên
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="fullname" name="fullname" placeholder="Nguyễn Văn A" required>
                                </div>

                                <div class="col-12">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email
                                    </label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="example@email.com" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Mật khẩu
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Nhập mật khẩu" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye" id="eyeIcon"></i>
                                        </button>
                                    </div>
                                    <div id="passwordStrength" class="mt-2"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye" id="eyeIconConfirm"></i>
                                        </button>
                                    </div>
                                    <div id="passwordMatch" class="mt-2"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-2"></i>Số điện thoại
                                    </label>
                                    <input type="tel" class="form-control form-control-lg" id="phone" name="phone" placeholder="0123456789" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="address" name="address" placeholder="Địa chỉ của bạn" required>
                                </div>

                                <div class="col-12">
                                    <button type="submit" name="register" class="btn btn-dark btn-lg w-100 mb-3">
                                        <i class="fas fa-user-plus me-2"></i>Đăng ký ngay
                                    </button>
                                </div>

                                <div class="col-12 text-center">
                                    <p class="text-muted mb-0">Đã có tài khoản? 
                                        <a href="login.php" class="fw-bold text-dark text-decoration-none">Đăng nhập</a>
                                    </p>
                                </div>
                            </div>
                        </form>
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
                            <h2 class="banner-title">THAM GIA CÙNG CHÚNG TÔI</h2>
                            <p class="banner-subtitle">Trải nghiệm mua sắm tuyệt vời với những ưu đãi độc quyền</p>
                            <div class="banner-features">
                                <div class="banner-feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <div class="feature-text">
                                        <strong>Ưu đãi thành viên</strong>
                                        <small>Giảm giá đặc biệt cho thành viên mới</small>
                                    </div>
                                </div>
                                <div class="banner-feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-truck"></i>
                                    </div>
                                    <div class="feature-text">
                                        <strong>Miễn phí vận chuyển</strong>
                                        <small>Cho đơn hàng đầu tiên</small>
                                    </div>
                                </div>
                                <div class="banner-feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="feature-text">
                                        <strong>Tích điểm thưởng</strong>
                                        <small>Nhận điểm cho mọi giao dịch</small>
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
    <script src="../../Js/User/register.js"></script>
</body>
</html>