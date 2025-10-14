<?php
session_start();
require_once '../../model/auth_middleware.php';
require_once '../../model/database.php';
require_once '../../model/order_model.php';
require_once '../../model/product_model.php';

// Bắt buộc đăng nhập để xem lịch sử đơn hàng
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=order_history.php&msg=login_required');
    exit();
}

checkSessionTimeout();

$user_id = $_SESSION['user_id'];

// Lấy filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;

// Build query để lấy orders của user
$sql = "SELECT o.order_id, o.order_date, o.total, o.status, o.voucher_id, o.shipping_address,
               v.code AS voucher_code, v.discount, v.type AS voucher_type
        FROM orders o
        LEFT JOIN vouchers v ON o.voucher_id = v.voucher_id
        WHERE o.user_id = ?";

$params = [$user_id];
$types = 'i';

if ($status_filter !== 'all') {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$sql .= " ORDER BY o.order_date DESC";

// Count total for pagination
$count_sql = "SELECT COUNT(*) as total FROM orders o WHERE o.user_id = ?";
if ($status_filter !== 'all') {
    $count_sql .= " AND o.status = ?";
}

$stmt_count = $conn->prepare($count_sql);
if (!$stmt_count) {
    die("Lỗi SQL Count: " . $conn->error);
}
$stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_orders = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $per_page);
$stmt_count->close();

// Get orders with pagination
$offset = ($page - 1) * $per_page;
$sql .= " LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Lỗi SQL Main: " . $conn->error);
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

// Get order status counts for filter badges
$status_counts = [
    'all' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'shipping' => 0,
    'delivered' => 0,
    'cancelled' => 0
];

$count_stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM orders WHERE user_id = ? GROUP BY status");
$count_stmt->bind_param('i', $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
while ($row = $count_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
    $status_counts['all'] += $row['count'];
}
$count_stmt->close();

// Helper function to get status label
function getStatusLabel($status) {
    $labels = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy'
    ];
    return $labels[$status] ?? $status;
}

// Helper function to get status badge class
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'shipping' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    return $classes[$status] ?? 'secondary';
}

// Helper function to get product thumbnail
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
    <title>Lịch sử đơn hàng - Snowboard Shop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Righteous&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Css/User/user_home.css">
    <link rel="stylesheet" href="../../Css/User/order_history.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Font override -->
    <style>
        body, p, div, span, a, button, input, select, textarea, .card-text, .btn, .nav-link { 
            font-family: "Barlow", sans-serif !important; 
            font-weight: 500 !important; 
        }
        h1, h2, h3, h4, h5, h6, .navbar-brand, .card-title, .page-title { 
            font-family: "Righteous", sans-serif !important; 
        }
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
                        <a class="nav-link active" href="order_history.php"><i class="fas fa-history me-1"></i>Đơn hàng</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($_SESSION['fullname'] ?? 'User') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="order_history.php"><i class="fas fa-history me-2"></i>Đơn hàng của tôi</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../../controller/controller_User/controller.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
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

    <!-- Nội dung chính -->
    <div class="container my-5">
        <!-- Tiêu đề trang -->
        <div class="page-header mb-4">
            <h1 class="page-title">
                <i class="fas fa-history me-2"></i> Lịch sử đơn hàng
            </h1>
            <p class="text-muted">Quản lý và theo dõi tất cả đơn hàng của bạn</p>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs mb-4">
            <div class="btn-group w-100" role="group">
                <a href="?status=all" class="btn btn-filter <?= $status_filter === 'all' ? 'active' : '' ?>">
                    <i class="fas fa-list me-1"></i> Tất cả
                    <?php if ($status_counts['all'] > 0): ?>
                        <span class="badge bg-dark ms-1"><?= $status_counts['all'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="?status=pending" class="btn btn-filter <?= $status_filter === 'pending' ? 'active' : '' ?>">
                    <i class="fas fa-clock me-1"></i> Chờ xử lý
                    <?php if ($status_counts['pending'] > 0): ?>
                        <span class="badge bg-warning ms-1"><?= $status_counts['pending'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="?status=confirmed" class="btn btn-filter <?= $status_filter === 'confirmed' ? 'active' : '' ?>">
                    <i class="fas fa-check-circle me-1"></i> Đã xác nhận
                    <?php if ($status_counts['confirmed'] > 0): ?>
                        <span class="badge bg-info ms-1"><?= $status_counts['confirmed'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="?status=shipping" class="btn btn-filter <?= $status_filter === 'shipping' ? 'active' : '' ?>">
                    <i class="fas fa-truck me-1"></i> Đang giao
                    <?php if ($status_counts['shipping'] > 0): ?>
                        <span class="badge bg-primary ms-1"><?= $status_counts['shipping'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="?status=delivered" class="btn btn-filter <?= $status_filter === 'delivered' ? 'active' : '' ?>">
                    <i class="fas fa-box-check me-1"></i> Đã giao
                    <?php if ($status_counts['delivered'] > 0): ?>
                        <span class="badge bg-success ms-1"><?= $status_counts['delivered'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="?status=cancelled" class="btn btn-filter <?= $status_filter === 'cancelled' ? 'active' : '' ?>">
                    <i class="fas fa-times-circle me-1"></i> Đã hủy
                    <?php if ($status_counts['cancelled'] > 0): ?>
                        <span class="badge bg-danger ms-1"><?= $status_counts['cancelled'] ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Orders List -->
        <?php if (empty($orders)): ?>
            <div class="empty-state text-center py-5">
                <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                <h3>Chưa có đơn hàng nào</h3>
                <p class="text-muted">
                    <?php if ($status_filter !== 'all'): ?>
                        Không có đơn hàng nào với trạng thái "<?= getStatusLabel($status_filter) ?>"
                    <?php else: ?>
                        Bạn chưa có đơn hàng nào. Hãy mua sắm ngay!
                    <?php endif; ?>
                </p>
                <a href="product_list.php" class="btn btn-primary btn-lg mt-3">
                    <i class="fas fa-shopping-cart me-2"></i> Mua sắm ngay
                </a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): 
                    // Get order items
                    $order_items = getOrderItems($order['order_id']);
                    $item_count = count($order_items);
                ?>
                    <div class="order-card mb-4">
                        <!-- Order Header -->
                        <div class="order-header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div class="order-info">
                                    <h5 class="order-id mb-1">
                                        <i class="fas fa-hashtag me-1"></i>
                                        Đơn hàng #<?= $order['order_id'] ?>
                                    </h5>
                                    <p class="order-date mb-0">
                                        <i class="far fa-calendar me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?>
                                    </p>
                                </div>
                                <div class="order-status">
                                    <span class="badge bg-<?= getStatusBadgeClass($order['status']) ?> status-badge">
                                        <?= getStatusLabel($order['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Nội dung đơn hàng -->
                        <div class="order-body">
                            <!-- Xem trước sản phẩm (3 sản phẩm đầu) -->
                            <div class="order-items-preview">
                                <?php 
                                $preview_items = array_slice($order_items, 0, 3);
                                foreach ($preview_items as $item): 
                                    $product = getProductById($item['product_id']);
                                    $thumbnail = getProductThumbnail($item['product_id'], $item['product_image']);
                                ?>
                                    <div class="item-preview">
                                        <img src="<?= htmlspecialchars($thumbnail) ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>"
                                             class="item-image">
                                        <div class="item-details">
                                            <p class="item-name mb-1"><?= htmlspecialchars($item['product_name']) ?></p>
                                            <p class="item-meta mb-0">
                                                <span>x<?= $item['quantity'] ?></span>
                                                <span class="text-muted mx-2">|</span>
                                                <span class="text-primary"><?= number_format($item['price'], 0, ',', '.') ?>đ</span>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if ($item_count > 3): ?>
                                    <p class="text-muted mb-0 mt-2">
                                        <i class="fas fa-plus-circle me-1"></i>
                                        Và <?= $item_count - 3 ?> sản phẩm khác...
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- Tổng kết đơn hàng -->
                            <div class="order-summary mt-3">
                                <div class="summary-row">
                                    <span><i class="fas fa-box me-2"></i> Số lượng sản phẩm:</span>
                                    <strong><?= $item_count ?> sản phẩm</strong>
                                </div>
                                <?php if ($order['voucher_code']): ?>
                                    <div class="summary-row text-success">
                                        <span><i class="fas fa-ticket me-2"></i> Mã giảm giá:</span>
                                        <strong><?= htmlspecialchars($order['voucher_code']) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <div class="summary-row total-row">
                                    <span><i class="fas fa-money-bill-wave me-2"></i> Tổng tiền:</span>
                                    <strong class="text-danger"><?= number_format($order['total'], 0, ',', '.') ?>đ</strong>
                                </div>
                                <?php if ($order['shipping_address']): ?>
                                    <div class="summary-row">
                                        <span><i class="fas fa-map-marker-alt me-2"></i> Địa chỉ:</span>
                                        <span class="text-truncate" style="max-width: 300px;"><?= htmlspecialchars($order['shipping_address']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Chân đơn hàng -->
                        <div class="order-footer">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="order-actions">
                                    <a href="order_tracking.php?order_id=<?= $order['order_id'] ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i> Xem chi tiết
                                    </a>
                                    
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <a href="order_cancel.php?order_id=<?= $order['order_id'] ?>" 
                                           class="btn btn-outline-danger btn-sm"
                                           onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                                            <i class="fas fa-times me-1"></i> Hủy đơn
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($order['status'] === 'delivered'): ?>
                                        <button class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-star me-1"></i> Đánh giá
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($order['status'] === 'delivered'): ?>
                                    <a href="checkout.php?reorder=<?= $order['order_id'] ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-redo me-1"></i> Mua lại
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Order pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Previous -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?status=<?= $status_filter ?>&page=<?= $page - 1 ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <!-- Page numbers -->
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?status=<?= $status_filter ?>&page=1">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?status=<?= $status_filter ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?status=<?= $status_filter ?>&page=<?= $total_pages ?>"><?= $total_pages ?></a>
                            </li>
                        <?php endif; ?>

                        <!-- Next -->
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?status=<?= $status_filter ?>&page=<?= $page + 1 ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
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

    <!-- Nút lên đầu trang -->
    <button id="backToTopBtn" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Js/User/order_history.js"></script>
</body>
</html>
