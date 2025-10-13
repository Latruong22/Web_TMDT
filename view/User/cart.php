<?php
session_start();
require_once '../../model/auth_middleware.php';
require_once '../../model/database.php';
require_once '../../model/product_model.php';

// Cho phép cả guest và user truy cập giỏ hàng
if (isset($_SESSION['user_id'])) {
    checkSessionTimeout();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Snowboard Shop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Righteous&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Css/User/user_home.css">
    <link rel="stylesheet" href="../../Css/User/cart_new.css">
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
                        <a class="nav-link active" href="cart.php">
                            <i class="fas fa-shopping-cart me-1"></i>Giỏ hàng
                            <span class="cart-badge" id="cart-count">0</span>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
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
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="fas fa-user-plus me-1"></i>Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Cart Content -->
    <div class="container cart-container my-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="cart-title">Giỏ hàng</h1>
            </div>
        </div>

        <div class="row">
            <!-- Left Side - Cart Items -->
            <div class="col-lg-8">
                <!-- Cart Items Header -->
                <div class="cart-items-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Sản phẩm</h6>
                        </div>
                        <div class="col-md-2 text-center">
                            <h6 class="fw-bold">Giá</h6>
                        </div>
                        <div class="col-md-2 text-center">
                            <h6 class="fw-bold">Số lượng</h6>
                        </div>
                        <div class="col-md-2 text-center">
                            <h6 class="fw-bold">Tổng</h6>
                        </div>
                    </div>
                </div>
                <hr>

                <!-- Empty Cart Message -->
                <div id="emptyCart" class="empty-cart" style="display: none;">
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart empty-icon"></i>
                        <h4 class="mt-3">Giỏ hàng của bạn đang trống</h4>
                        <p class="text-muted">Hãy thêm một số sản phẩm tuyệt vời vào giỏ hàng của bạn!</p>
                        <a href="product_list.php" class="btn btn-outline-dark btn-lg">
                            <i class="fas fa-snowboarding me-2"></i>Tiếp tục mua sắm
                        </a>
                    </div>
                </div>

                <!-- Cart Items -->
                <div id="cartItems" class="cart-items">
                    <!-- Cart items will be dynamically populated here -->
                </div>

                <!-- Shipping Options Section -->
                <div class="shipping-options mt-4" id="shippingOptionsSection" style="display: none;">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-truck me-2"></i>Tùy chọn vận chuyển
                            </h5>
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="fw-bold mb-3">Chi phí vận chuyển ước tính</h6>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="postalCode" placeholder="Nhập mã bưu chính">
                                        <button class="btn btn-warning" type="button" onclick="calculateShipping()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted mt-2 d-block">Nhập mã bưu chính để tính phí vận chuyển chính xác</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Continue Shopping Button -->
                <div class="continue-shopping mt-4" id="continueShoppingSection" style="display: none;">
                    <a href="product_list.php" class="btn btn-outline-dark btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Tiếp tục mua sắm
                    </a>
                </div>
            </div>

            <!-- Right Side - Order Summary -->
            <div class="col-lg-4">
                <div class="order-summary-container" id="orderSummaryContainer" style="display: none;">
                    <!-- Promo Code Section -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Mã giảm giá</h6>
                            <div class="input-group">
                                <input type="text" class="form-control" id="promoCode" placeholder="Có mã giảm giá?">
                                <button class="btn btn-warning" type="button" onclick="applyPromoCode()">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                            <div id="promoMessage" class="promo-message mt-2"></div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Tóm tắt đơn hàng</h6>
                            
                            <div class="summary-row">
                                <span>Tạm tính</span>
                                <span id="subtotal">₫0.00</span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Vận chuyển</span>
                                <span id="shipping">₫0.00</span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Thuế bán hàng</span>
                                <span id="salesTax">₫0.00</span>
                            </div>
                            
                            <div class="summary-row" id="discountRow" style="display: none;">
                                <span>Giảm giá</span>
                                <span id="discount" class="text-success">-₫0.00</span>
                            </div>
                            
                            <hr>
                            
                            <div class="summary-row total-row">
                                <strong>
                                    <span>Tổng ước tính</span>
                                    <span id="total">₫0.00</span>
                                </strong>
                            </div>
                            
                            <!-- Checkout Button -->
                            <button class="btn btn-warning btn-checkout w-100 mt-3" onclick="proceedToCheckout()">
                                Thanh toán
                            </button>
                            
                            <!-- Affirm Info -->
                            <div class="affirm-info mt-3 text-center">
                                <small class="text-muted">
                                    Bắt đầu từ <strong>₫46/tháng</strong> hoặc 0% APR với 
                                    <span class="text-primary">Affirm</span>. 
                                    <a href="#" class="text-decoration-none">Kiểm tra sức mua của bạn</a>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                        <div class="payment-info mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Phí bắt đầu từ 46₫/tháng hoặc 0% APR với 
                                <strong>Affirm</strong>. 
                                <a href="#" class="text-warning">Kiểm tra khả năng mua hàng</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suggested Products Section -->
        <div class="row mt-5" id="suggestedProducts">
            <div class="col-12">
                <h3 class="section-title">
                    <i class="fas fa-lightbulb me-2"></i>Gợi ý sản phẩm khác
                </h3>
                <div class="suggested-products-grid" id="suggestedProductsGrid">
                    <!-- Suggested products will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-5">
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

    <!-- Toast Notifications -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="cartToast" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-check-circle text-success me-2"></i>
                <strong class="me-auto">Thành công</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                Đã cập nhật giỏ hàng!
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Js/User/cart_simple.js?v=<?= time() ?>"></script>

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
