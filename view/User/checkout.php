<?php
session_start();
require_once '../../model/auth_middleware.php';
require_once '../../model/database.php';
require_once '../../model/product_model.php';
require_once '../../model/user_model.php';

// Bắt buộc phải đăng nhập để checkout
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php&msg=login_required');
    exit();
}

checkSessionTimeout();

// Lấy thông tin user
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

if (!$user) {
    header('Location: login.php?msg=user_not_found');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Snowboard Shop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Righteous&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Css/User/user_home.css">
    <link rel="stylesheet" href="../../Css/User/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Font override để đảm bảo fonts và icons hoạt động -->
    <style>
        body, p, div, span, a, button, input, select, textarea, .card-text, .btn, .nav-link { font-family: "Barlow", sans-serif !important; font-weight: 500 !important; }
        h1, h2, h3, h4, h5, h6, .navbar-brand, .card-title, .product-title { font-family: "Righteous", sans-serif !important; }
        /* Giữ font mặc định cho icons - Enhanced */
        .fas, .far, .fal, .fab, [class*="fa-"], 
        i.fas, i.far, i.fal, i.fab, i[class*="fa-"],
        .footer .fas, .footer .far, .footer .fal, .footer .fab, .footer [class*="fa-"],
        .social-links i, .social-links [class*="fa-"] { 
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
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['fullname']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit me-2"></i>Hồ sơ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../../controller/controller_User/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Checkout Container -->
    <div class="container checkout-container my-5">
        <!-- Progress Steps -->
        <div class="checkout-progress mb-5">
            <div class="progress-step completed">
                <div class="step-number">1</div>
                <div class="step-label">Giỏ hàng</div>
            </div>
            <div class="progress-line completed"></div>
            <div class="progress-step active">
                <div class="step-number">2</div>
                <div class="step-label">Thông tin giao hàng</div>
            </div>
            <div class="progress-line"></div>
            <div class="progress-step">
                <div class="step-number">3</div>
                <div class="step-label">Thanh toán</div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - Shipping & Payment Info -->
            <div class="col-lg-7">
                <!-- Shipping Information Card -->
                <div class="card checkout-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-shipping-fast me-2"></i>Thông tin giao hàng
                        </h5>
                        <hr>
                        
                        <form id="checkoutForm">
                            <!-- Họ tên -->
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fullname" name="fullname" 
                                       value="<?= htmlspecialchars($user['fullname']) ?>" required>
                                <div class="invalid-feedback">Vui lòng nhập họ tên</div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required readonly>
                                <small class="text-muted">Email xác nhận đơn hàng sẽ được gửi đến địa chỉ này</small>
                            </div>

                            <!-- Số điện thoại -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                       placeholder="0912345678" required>
                                <div class="invalid-feedback">Vui lòng nhập số điện thoại hợp lệ</div>
                            </div>

                            <!-- Địa chỉ giao hàng -->
                            <div class="mb-3">
                                <label for="address" class="form-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="3" 
                                          placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                <div class="invalid-feedback">Vui lòng nhập địa chỉ giao hàng đầy đủ</div>
                            </div>

                            <!-- Ghi chú đơn hàng -->
                            <div class="mb-3">
                                <label for="note" class="form-label">Ghi chú đơn hàng (tùy chọn)</label>
                                <textarea class="form-control" id="note" name="note" rows="2" 
                                          placeholder="Ví dụ: Giao hàng giờ hành chính, gọi trước khi giao..."></textarea>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Payment Method Card -->
                <div class="card checkout-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-credit-card me-2"></i>Phương thức thanh toán
                        </h5>
                        <hr>

                        <div class="payment-methods">
                            <!-- COD Payment Method -->
                            <div class="payment-method active" data-method="cod">
                                <input type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                                <label for="payment_cod" class="payment-label">
                                    <div class="payment-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="payment-info">
                                        <div class="payment-name">Thanh toán khi nhận hàng (COD)</div>
                                        <div class="payment-desc">Thanh toán bằng tiền mặt khi nhận hàng</div>
                                    </div>
                                    <div class="payment-check">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </label>
                            </div>

                            <!-- VNPay - Coming Soon -->
                            <div class="payment-method disabled" data-method="vnpay">
                                <input type="radio" name="payment_method" id="payment_vnpay" value="vnpay" disabled>
                                <label for="payment_vnpay" class="payment-label">
                                    <div class="payment-icon">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div class="payment-info">
                                        <div class="payment-name">VNPay <span class="badge bg-secondary">Sắp ra mắt</span></div>
                                        <div class="payment-desc">Thanh toán qua ví điện tử VNPay</div>
                                    </div>
                                    <div class="payment-check">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </label>
                            </div>

                            <!-- MoMo - Coming Soon -->
                            <div class="payment-method disabled" data-method="momo">
                                <input type="radio" name="payment_method" id="payment_momo" value="momo" disabled>
                                <label for="payment_momo" class="payment-label">
                                    <div class="payment-icon">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div class="payment-info">
                                        <div class="payment-name">MoMo <span class="badge bg-secondary">Sắp ra mắt</span></div>
                                        <div class="payment-desc">Thanh toán qua ví MoMo</div>
                                    </div>
                                    <div class="payment-check">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="col-lg-5">
                <div class="card checkout-card order-summary-card sticky-top" style="top: 100px;">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-receipt me-2"></i>Tóm tắt đơn hàng
                        </h5>
                        <hr>

                        <!-- Cart Items -->
                        <div class="order-items" id="orderItems">
                            <!-- Will be populated by JavaScript -->
                        </div>

                        <!-- Voucher Section -->
                        <div class="voucher-section mt-3">
                            <div class="input-group">
                                <input type="text" class="form-control" id="voucherCode" placeholder="Nhập mã giảm giá">
                                <button class="btn btn-outline-secondary" type="button" id="applyVoucherBtn">
                                    <i class="fas fa-tag me-1"></i>Áp dụng
                                </button>
                            </div>
                            <div id="voucherMessage" class="mt-2"></div>
                        </div>

                        <!-- Price Summary -->
                        <div class="price-summary mt-4">
                            <div class="price-row">
                                <span>Tạm tính:</span>
                                <span id="subtotal">0đ</span>
                            </div>
                            <div class="price-row">
                                <span>Phí vận chuyển:</span>
                                <span id="shippingFee">30,000đ</span>
                            </div>
                            <div class="price-row discount-row" id="discountRow" style="display: none;">
                                <span>Giảm giá:</span>
                                <span class="text-success" id="discount">-0đ</span>
                            </div>
                            <hr>
                            <div class="price-row total-row">
                                <span>Tổng cộng:</span>
                                <span class="total-price" id="totalPrice">0đ</span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <button type="button" class="btn btn-dark btn-lg w-100 mt-4 checkout-btn" id="placeOrderBtn">
                            <i class="fas fa-check-circle me-2"></i>Đặt hàng
                        </button>

                        <!-- Security Note -->
                        <div class="security-note mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>
                                Giao dịch của bạn được bảo mật và mã hóa
                            </small>
                        </div>

                        <!-- Return to Cart -->
                        <div class="text-center mt-3">
                            <a href="cart.php" class="btn btn-link text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Quay lại giỏ hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer bg-dark text-white pt-5 pb-3 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-12 mb-4">
                    <h6 class="fw-bold mb-3">SNOWBOARD SHOP</h6>
                    <p class="text-white-50">Chuyên cung cấp các sản phẩm snowboard chất lượng cao với giá tốt nhất.</p>
                    <div class="social-links">
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

    <!-- Toast Notification -->
    <div id="toast" class="toast-notification"></div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Đang xử lý...</span>
        </div>
        <p class="text-white mt-3">Đang xử lý đơn hàng của bạn...</p>
    </div>

    <!-- Scripts -->
    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Js/User/checkout.js?v=<?= time() ?>"></script>

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
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>