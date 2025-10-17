<?php
session_start();
require_once '../../model/auth_middleware.php';
require_once '../../model/database.php';
require_once '../../model/user_model.php';

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile.php');
    exit();
}

checkSessionTimeout();

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

if (!$user) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - Snowboard Shop</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Righteous&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Css/User/user_home.css">
    <link rel="stylesheet" href="../../Css/User/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Font override -->
    <style>
        body, p, div, span, a, button, input, select, textarea, .card-text, .btn, .nav-link { font-family: "Barlow", sans-serif !important; font-weight: 500 !important; }
        h1, h2, h3, h4, h5, h6, .navbar-brand, .card-title { font-family: "Righteous", sans-serif !important; }
        .fas, .far, .fal, .fab, [class*="fa-"], 
        i.fas, i.far, i.fal, i.fab, i[class*="fa-"] { 
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 6 Brands" !important; 
            font-weight: 900 !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="home.php">
                <img src="../../Images/logo/logo.jpg" alt="Logo" class="logo-img">
                <span class="ms-2 fw-bold">SNOWBOARD SHOP</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php"><i class="fas fa-home me-1"></i>Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="product_list.php"><i class="fas fa-snowboarding me-1"></i>Sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Giỏ hàng
                            <span class="cart-badge" id="cart-count">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="order_history.php"><i class="fas fa-history me-1"></i>Đơn hàng</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($user['fullname']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item active" href="profile.php"><i class="fas fa-user-edit me-2"></i>Hồ sơ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../../controller/controller_User/controller.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Container -->
    <div class="container my-5">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="page-title">
                    <i class="fas fa-user-circle me-2"></i>Hồ sơ cá nhân
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="home.php">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Hồ sơ</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="profile-sidebar card shadow-sm">
                    <div class="profile-avatar text-center p-4 border-bottom">
                        <div class="avatar-wrapper position-relative">
                            <img src="<?= !empty($user['avatar']) ? '../../' . $user['avatar'] : '../../Images/logo/default-avatar.png' ?>" 
                                 alt="Avatar" 
                                 class="avatar-img"
                                 id="avatarImage">
                            <div class="avatar-overlay" onclick="document.getElementById('avatarInput').click()">
                                <i class="fas fa-camera"></i>
                                <span>Thay đổi</span>
                            </div>
                            <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="handleAvatarChange(this)">
                        </div>
                        <h5 class="mt-3 mb-1"><?= htmlspecialchars($user['fullname']) ?></h5>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($user['email']) ?></p>
                        <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?> mt-2">
                            <?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng' ?>
                        </span>
                    </div>
                    <ul class="profile-menu list-unstyled mb-0">
                        <li>
                            <a href="#info" class="profile-menu-link active">
                                <i class="fas fa-user me-2"></i>Thông tin cá nhân
                            </a>
                        </li>
                        <li>
                            <a href="#password" class="profile-menu-link">
                                <i class="fas fa-lock me-2"></i>Đổi mật khẩu
                            </a>
                        </li>
                        <li>
                            <a href="order_history.php" class="profile-menu-link">
                                <i class="fas fa-shopping-bag me-2"></i>Đơn hàng của tôi
                            </a>
                        </li>
                        <?php if ($user['role'] === 'admin'): ?>
                        <li>
                            <a href="../Admin/admin_home.php" class="profile-menu-link">
                                <i class="fas fa-tachometer-alt me-2"></i>Quản trị
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9 col-md-8">
                <!-- Personal Info -->
                <div id="info" class="profile-section card shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h4 class="section-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Thông tin cá nhân
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <form id="updateInfoForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">
                                        Họ và tên <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="fullname" 
                                           value="<?= htmlspecialchars($user['fullname']) ?>" 
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" class="form-control" 
                                           value="<?= htmlspecialchars($user['email']) ?>" 
                                           readonly disabled>
                                    <small class="text-muted">Email không thể thay đổi</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Số điện thoại</label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                           placeholder="0123456789">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Ngày tạo</label>
                                    <input type="text" class="form-control" 
                                           value="<?= date('d/m/Y', strtotime($user['created_at'])) ?>" 
                                           readonly disabled>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Địa chỉ</label>
                                    <textarea class="form-control" name="address" rows="3" 
                                              placeholder="Nhập địa chỉ của bạn"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-outline-warning" onclick="showPasswordForm()">
                                    <i class="fas fa-lock me-2"></i>Đổi mật khẩu
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div id="password" class="profile-section card shadow-sm" style="display: none;">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h4 class="section-title mb-0">
                            <i class="fas fa-key me-2"></i>Đổi mật khẩu
                        </h4>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hidePasswordForm()">
                            <i class="fas fa-times me-1"></i>Đóng
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <form id="changePasswordForm">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">
                                        Mật khẩu hiện tại <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="current_password" 
                                               id="currentPassword" required>
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="togglePassword('currentPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">
                                        Mật khẩu mới <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="new_password" 
                                               id="newPassword" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="togglePassword('newPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Tối thiểu 6 ký tự</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">
                                        Xác nhận mật khẩu mới <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="confirm_password" 
                                               id="confirmPassword" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="togglePassword('confirmPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <small>Sau khi đổi mật khẩu, bạn sẽ cần đăng nhập lại</small>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key me-2"></i>Đổi mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">SNOWBOARD SHOP</h5>
                    <p class="text-white-50">Điểm đến lý tưởng cho những người đam mê trượt tuyết và thể thao mùa đông.</p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold mb-3">Liên kết</h6>
                    <ul class="list-unstyled">
                        <li><a href="home.php" class="text-white-50 text-decoration-none">Trang chủ</a></li>
                        <li><a href="product_list.php" class="text-white-50 text-decoration-none">Sản phẩm</a></li>
                        <li><a href="cart.php" class="text-white-50 text-decoration-none">Giỏ hàng</a></li>
                        <li><a href="order_history.php" class="text-white-50 text-decoration-none">Đơn hàng</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-bold mb-3">Chính sách</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white-50 text-decoration-none">Chính sách bảo mật</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Điều khoản sử dụng</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Chính sách đổi trả</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Hướng dẫn mua hàng</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-bold mb-3">Liên hệ</h6>
                    <ul class="list-unstyled text-white-50">
                        <li><i class="fas fa-map-marker-alt me-2"></i>123 Đường ABC, Quận XYZ, TP.HCM</li>
                        <li><i class="fas fa-phone me-2"></i>0123 456 789</li>
                        <li><i class="fas fa-envelope me-2"></i>info@snowboardshop.vn</li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center text-white-50">
                <p class="mb-0">&copy; 2025 Snowboard Shop. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTopBtn" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Js/User/profile.js"></script>
    
    <script>
        // Back to Top functionality
        const backToTopBtn = document.getElementById('backToTopBtn');
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.opacity = '1';
                backToTopBtn.style.visibility = 'visible';
            } else {
                backToTopBtn.style.opacity = '0';
                backToTopBtn.style.visibility = 'hidden';
            }
        });
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                button.classList.remove('fa-eye');
                button.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                button.classList.remove('fa-eye-slash');
                button.classList.add('fa-eye');
            }
        }

        // Update cart count from localStorage
        function updateCartCount() {
            const cartBadge = document.getElementById('cart-count');
            if (cartBadge) {
                const cart = JSON.parse(localStorage.getItem('cart') || '[]');
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                cartBadge.textContent = totalItems;
            }
        }
        updateCartCount();
    </script>
</body>
</html>
