<?php
session_start();
require_once '../../model/auth_middleware.php';

// Cho phép cả guest và user truy cập home.php
// requireUser(); // Bỏ yêu cầu bắt buộc đăng nhập
if (isset($_SESSION['user_id'])) {
    checkSessionTimeout(); // Chỉ check timeout khi đã login
}

require_once '../../model/database.php';
require_once '../../model/product_model.php';
require_once '../../model/category_model.php';

// Lấy sản phẩm nổi bật (8 sản phẩm mới nhất có status = 'active')
$featured_products = array_slice(array_filter(getAllProducts(), function($p) {
    return $p['status'] === 'active';
}), 0, 8);

$categories = getAllCategories();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snowboard Shop - Trang chủ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Righteous&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Css/User/user_home.css">
    <!-- Font override để đảm bảo fonts hoạt động -->
    <style>
        body, p, div, span, a, button, input, select, textarea, .card-text, .btn, .nav-link { font-family: "Barlow", sans-serif !important; font-weight: 500 !important; }
        h1, h2, h3, h4, h5, h6, .navbar-brand, .card-title, .display-1, .display-2, .display-3, .display-4, .display-5, .display-6 { font-family: "Righteous", sans-serif !important; }
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
                        <a class="nav-link active" href="home.php"><i class="fas fa-home me-1"></i>Trang chủ</a>
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="order_history.php"><i class="fas fa-history me-1"></i>Đơn hàng</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['fullname']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="../../controller/controller_User/controller.php?action=logout">Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Đăng nhập</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Băng chuyền Hero -->
    <section class="hero-section">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="4"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="5"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="../../Images/baner/banner1.jpg" class="d-block w-100" alt="Banner 1">
                    <div class="carousel-caption">
                        <h1 class="display-3 fw-bold animate-fade-up">Chinh phục đỉnh tuyết</h1>
                        <p class="lead animate-fade-up-delay">Khám phá bộ sưu tập snowboard chất lượng cao</p>
                        <a href="product_list.php" class="btn btn-light btn-lg animate-fade-up-delay-2">Xem sản phẩm</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="../../Images/baner/baner2.jpg" class="d-block w-100" alt="Banner 2">
                    <div class="carousel-caption">
                        <h2 class="display-4 fw-bold">Trải nghiệm mùa đông</h2>
                        <p class="lead">Thiết bị chuyên nghiệp cho mọi cấp độ</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="../../Images/baner/baner3.jpg" class="d-block w-100" alt="Banner 3">
                    <div class="carousel-caption">
                        <h2 class="display-4 fw-bold">Phong cách riêng biệt</h2>
                        <p class="lead">Thiết kế độc đáo, chất lượng vượt trội</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="../../Images/baner/baner4.jpg" class="d-block w-100" alt="Banner 4">
                    <div class="carousel-caption">
                        <h2 class="display-4 fw-bold">Khám phá giới hạn</h2>
                        <p class="lead">Cùng nhau chinh phục mọi đỉnh cao</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="../../Images/baner/baner5.jpg" class="d-block w-100" alt="Banner 5">
                    <div class="carousel-caption">
                        <h2 class="display-4 fw-bold">Đam mê snowboard</h2>
                        <p class="lead">Nơi khởi nguồn của những hành trình tuyết</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="../../Images/baner/baner6.jpg" class="d-block w-100" alt="Banner 6">
                    <div class="carousel-caption">
                        <h2 class="display-4 fw-bold">Sẵn sàng xuất phát</h2>
                        <p class="lead">Trang bị toàn diện cho chuyến đi của bạn</p>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>

    <!-- Phần giới thiệu -->
    <section class="about-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="display-5 fw-bold mb-4">Về chúng tôi</h2>
                    <p class="lead text-muted mb-4">
                        Snowboard Shop là điểm đến lý tưởng cho những người đam mê trượt tuyết. 
                        Chúng tôi cung cấp các sản phẩm chất lượng cao từ những thương hiệu hàng đầu thế giới.
                    </p>
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="feature-box">
                                <i class="fas fa-shield-alt fa-2x mb-3 text-dark"></i>
                                <h5>Chất lượng đảm bảo</h5>
                                <p class="text-muted">Sản phẩm chính hãng 100%</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="feature-box">
                                <i class="fas fa-shipping-fast fa-2x mb-3 text-dark"></i>
                                <h5>Giao hàng nhanh</h5>
                                <p class="text-muted">Miễn phí vận chuyển toàn quốc</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="feature-box">
                                <i class="fas fa-headset fa-2x mb-3 text-dark"></i>
                                <h5>Hỗ trợ 24/7</h5>
                                <p class="text-muted">Tư vấn nhiệt tình, chuyên nghiệp</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="feature-box">
                                <i class="fas fa-undo-alt fa-2x mb-3 text-dark"></i>
                                <h5>Đổi trả dễ dàng</h5>
                                <p class="text-muted">Chính sách đổi trả trong 30 ngày</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="../../Images/baner/baner3.jpg" alt="About" class="img-fluid rounded-3 shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Phần danh mục -->
    <section class="categories-section py-5 bg-light">
        <div class="container">
            <h2 class="text-center display-5 fw-bold mb-5">Danh mục sản phẩm</h2>
            <div class="row g-4">
                <?php foreach ($categories as $category): ?>
                    <div class="col-md-4">
                        <a href="product_list.php?category=<?php echo $category['category_id']; ?>" class="category-card">
                            <div class="card h-100 border-0 shadow-sm hover-lift">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-snowboarding fa-3x mb-3 text-dark"></i>
                                    <h4 class="card-title fw-bold"><?php echo htmlspecialchars($category['name']); ?></h4>
                                    <p class="text-muted">Khám phá ngay</p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Sản phẩm nổi bật -->
    <section class="products-section py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="display-5 fw-bold">Sản phẩm nổi bật</h2>
                <p class="lead text-muted">Khám phá những sản phẩm mới nhất và hot nhất</p>
            </div>
            <div class="row g-4">
                <?php foreach ($featured_products as $product): ?>
                    <?php
                    $final_price = $product['price'] * (1 - $product['manual_discount'] / 100);
                    $has_discount = $product['manual_discount'] > 0;
                    ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product-card card h-100 shadow-sm">
                            <?php if ($has_discount): ?>
                                <div class="discount-badge">-<?php echo number_format($product['manual_discount']); ?>%</div>
                            <?php endif; ?>
                            <a href="product_detail.php?id=<?php echo $product['product_id']; ?>">
                                <img src="../../<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h5>
                                <p class="text-muted small mb-2"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                <div class="price-box mt-auto">
                                    <?php if ($has_discount): ?>
                                        <span class="original-price text-muted text-decoration-line-through me-2">
                                            <?php echo number_format($product['price'], 0, ',', '.'); ?>đ
                                        </span>
                                        <span class="final-price fw-bold fs-5" style="color: #212529 !important;">
                                            <?php echo number_format($final_price, 0, ',', '.'); ?>đ
                                        </span>
                                    <?php else: ?>
                                        <span class="final-price fw-bold fs-5" style="color: #212529 !important;">
                                            <?php echo number_format($product['price'], 0, ',', '.'); ?>đ
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-dark btn-sm mt-3">
                                    <i class="fas fa-eye me-1"></i>Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="product_list.php" class="btn btn-outline-dark btn-lg">
                    Xem tất cả sản phẩm <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Chân trang -->
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

    <!-- Nút lên đầu trang -->
    <button id="backToTopBtn" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Js/User/home.js"></script>
    <script>
        // Update cart count on page load
        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const cartBadge = document.getElementById('cart-count');
            if (cartBadge) {
                cartBadge.textContent = totalItems;
                if (totalItems === 0) {
                    cartBadge.style.display = 'none';
                } else {
                    cartBadge.style.display = 'inline-block';
                }
            }
        }
        
        // Update on page load
        updateCartCount();
        
        // Update on storage change (when cart updated in other tabs)
        window.addEventListener('storage', function(e) {
            if (e.key === 'cart') {
                updateCartCount();
            }
        });

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