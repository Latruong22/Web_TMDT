<?php
session_start();
require_once '../../model/auth_middleware.php';
require_once '../../model/database.php';
require_once '../../model/order_model.php';

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=order_tracking.php&msg=login_required');
    exit();
}

checkSessionTimeout();

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Lấy thông tin đơn hàng
$order = getOrderById($order_id);

// Kiểm tra quyền truy cập (chỉ user sở hữu đơn mới xem được)
if (!$order || $order['user_id'] != $user_id) {
    header('Location: order_history.php?msg=access_denied');
    exit();
}

// Lấy danh sách sản phẩm trong đơn
$order_items = getOrderItems($order_id);

// Helper function để lấy label trạng thái
function getStatusLabel($status) {
    $labels = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy'
    ];
    return $labels[$status] ?? 'Không xác định';
}

// Helper function để lấy class badge
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'bg-warning',
        'confirmed' => 'bg-info',
        'shipping' => 'bg-primary',
        'delivered' => 'bg-success',
        'cancelled' => 'bg-danger'
    ];
    return $classes[$status] ?? 'bg-secondary';
}

// Helper function để lấy icon trạng thái
function getStatusIcon($status) {
    $icons = [
        'pending' => 'fas fa-clock',
        'confirmed' => 'fas fa-check-circle',
        'shipping' => 'fas fa-shipping-fast',
        'delivered' => 'fas fa-box-open',
        'cancelled' => 'fas fa-times-circle'
    ];
    return $icons[$status] ?? 'fas fa-question-circle';
}

// Tính tổng tiền sản phẩm
$subtotal = 0;
foreach ($order_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Tính discount nếu có voucher
$discount = 0;
if ($order['voucher_id']) {
    // Logic tính discount sẽ được xử lý ở checkout
    // Ở đây chỉ hiển thị thông tin
}

// Helper function để lấy thumbnail sản phẩm
function getProductThumbnail($product_id, $fallback_image = '') {
    $sp_folder = "Sp" . $product_id;
    $folder_path = $_SERVER['DOCUMENT_ROOT'] . "/Web_TMDT/Images/product/" . $sp_folder . "/";
    $folder_url = "/Web_TMDT/Images/product/" . $sp_folder . "/";
    
    if (is_dir($folder_path)) {
        $files = scandir($folder_path);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                return $folder_url . $file;
            }
        }
    }
    
    if ($fallback_image) {
        if (strpos($fallback_image, 'http') === 0 || strpos($fallback_image, '/Web_TMDT/') === 0) {
            return $fallback_image;
        }
        return "/Web_TMDT/" . $fallback_image;
    }
    
    return "/Web_TMDT/Images/product/placeholder.jpg";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?= $order['order_id'] ?> - Snowboard Shop</title>
    
    <!-- CSS Bootstrap -->
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@300;400;500;600;700&family=Righteous&display=swap" rel="stylesheet">
    
    <!-- CSS Tùy chỉnh -->
    <link rel="stylesheet" href="../../Css/User/user_home.css">
    <link rel="stylesheet" href="../../Css/User/order_tracking.css">
    
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
    <!-- Thanh điều hướng -->
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
                        <a class="nav-link active" href="order_history.php"><i class="fas fa-history me-1"></i>Đơn hàng</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['fullname']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit me-2"></i>Hồ sơ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../../controller/controller_User/controller.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Nội dung chính -->
    <div class="container my-5">
        <!-- Đường dẫn -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="order_history.php">Đơn hàng</a></li>
                <li class="breadcrumb-item active">Theo dõi đơn hàng #<?= $order_id ?></li>
            </ol>
        </nav>

        <!-- Tiêu đề trang -->
        <div class="page-header mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="page-title mb-2">Đơn hàng #<?= $order_id ?></h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Đặt ngày: <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?>
                    </p>
                </div>
                <div>
                    <span class="status-badge <?= getStatusBadgeClass($order['status']) ?> px-4 py-2 rounded-pill">
                        <i class="<?= getStatusIcon($order['status']) ?> me-2"></i>
                        <?= getStatusLabel($order['status']) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Order Timeline -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-route me-2"></i>Trạng thái đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="order-timeline">
                            <!-- Pending -->
                            <div class="timeline-item <?= in_array($order['status'], ['pending', 'confirmed', 'shipping', 'delivered']) ? 'completed' : '' ?> <?= $order['status'] === 'pending' ? 'active' : '' ?>">
                                <div class="timeline-marker">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Chờ xử lý</h6>
                                    <p class="text-muted mb-0">Đơn hàng đang chờ xác nhận</p>
                                </div>
                            </div>

                            <!-- Confirmed -->
                            <div class="timeline-item <?= in_array($order['status'], ['confirmed', 'shipping', 'delivered']) ? 'completed' : '' ?> <?= $order['status'] === 'confirmed' ? 'active' : '' ?>">
                                <div class="timeline-marker">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đã xác nhận</h6>
                                    <p class="text-muted mb-0">Đơn hàng đã được xác nhận</p>
                                </div>
                            </div>

                            <!-- Shipping -->
                            <div class="timeline-item <?= in_array($order['status'], ['shipping', 'delivered']) ? 'completed' : '' ?> <?= $order['status'] === 'shipping' ? 'active' : '' ?>">
                                <div class="timeline-marker">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đang giao hàng</h6>
                                    <p class="text-muted mb-0">Đơn hàng đang được vận chuyển</p>
                                </div>
                            </div>

                            <!-- Delivered -->
                            <div class="timeline-item <?= $order['status'] === 'delivered' ? 'completed active' : '' ?>">
                                <div class="timeline-marker">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đã giao hàng</h6>
                                    <p class="text-muted mb-0">Đơn hàng đã được giao thành công</p>
                                </div>
                            </div>

                            <!-- Cancelled (if applicable) -->
                            <?php if ($order['status'] === 'cancelled'): ?>
                            <div class="timeline-item cancelled active">
                                <div class="timeline-marker">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>Đã hủy</h6>
                                    <p class="text-muted mb-0">Đơn hàng đã bị hủy</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sản phẩm trong đơn hàng -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-box me-2"></i>Sản phẩm (<?= count($order_items) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">Số lượng</th>
                                        <th class="text-end">Đơn giá</th>
                                        <th class="text-end">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= getProductThumbnail($item['product_id'], $item['product_image'] ?? '') ?>" 
                                                     alt="<?= htmlspecialchars($item['product_name']) ?>"
                                                     class="product-thumbnail"
                                                     onerror="this.src='/Web_TMDT/Images/product/placeholder.jpg'">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                                                    <small class="text-muted">ID: <?= $item['product_id'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge bg-secondary"><?= $item['quantity'] ?></span>
                                        </td>
                                        <td class="text-end align-middle">
                                            <?= number_format($item['price'], 0, ',', '.') ?>₫
                                        </td>
                                        <td class="text-end align-middle fw-bold">
                                            <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>₫
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thanh bên tổng kết đơn hàng -->
            <div class="col-lg-4">
                <!-- Tổng kết đơn hàng -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Tổng đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <span><?= number_format($subtotal, 0, ',', '.') ?>₫</span>
                        </div>
                        <?php if ($order['voucher_code']): ?>
                        <div class="summary-row">
                            <span>
                                <i class="fas fa-tag me-1 text-success"></i>
                                Voucher (<?= htmlspecialchars($order['voucher_code']) ?>):
                            </span>
                            <span class="text-success">-<?= number_format($subtotal - $order['total'], 0, ',', '.') ?>₫</span>
                        </div>
                        <?php endif; ?>
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span>Miễn phí</span>
                        </div>
                        <hr>
                        <div class="summary-row total-row">
                            <strong>Tổng cộng:</strong>
                            <strong class="text-danger"><?= number_format($order['total'], 0, ',', '.') ?>₫</strong>
                        </div>
                    </div>
                </div>

                <!-- Thông tin giao hàng -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Thông tin giao hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Người nhận:</label>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($order['fullname']) ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Số điện thoại:</label>
                            <p class="mb-0"><?= htmlspecialchars($order['phone']) ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Email:</label>
                            <p class="mb-0"><?= htmlspecialchars($order['email']) ?></p>
                        </div>
                        <div>
                            <label class="text-muted small">Địa chỉ giao hàng:</label>
                            <p class="mb-0"><?= htmlspecialchars($order['shipping_address']) ?></p>
                        </div>
                        <?php if (!empty($order['note'])): ?>
                        <hr>
                        <div>
                            <label class="text-muted small">Ghi chú:</label>
                            <p class="mb-0 fst-italic"><?= htmlspecialchars($order['note']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Hành động -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($order['status'] === 'pending'): ?>
                            <a href="order_cancel.php?order_id=<?= $order_id ?>" class="btn btn-outline-danger">
                                <i class="fas fa-times-circle me-2"></i>Hủy đơn hàng
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'delivered'): ?>
                            <button class="btn btn-success" onclick="openReviewModal()">
                                <i class="fas fa-star me-2"></i>Đánh giá sản phẩm
                            </button>
                            <a href="checkout.php?reorder=<?= $order_id ?>" class="btn btn-outline-primary">
                                <i class="fas fa-redo me-2"></i>Mua lại
                            </a>
                            <?php endif; ?>
                            
                            <a href="order_history.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                            </a>
                            
                            <button class="btn btn-outline-dark" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>In đơn hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    <script src="../../Js/User/order_tracking.js"></script>
</body>
</html>
