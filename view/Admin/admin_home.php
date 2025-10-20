<?php
session_start();
require_once '../../model/database.php';
checkAccess('admin');

$adminName = $_SESSION['fullname'] ?? 'Quản trị viên';

// Chuẩn bị số liệu tổng quan cho dashboard.
$productCount = $activeProductCount = $pendingOrderCount = $userCount = $todayRevenue = 0.0;
$latestOrders = [];

// Đếm tổng sản phẩm và sản phẩm đang bán.
if ($result = $conn->query("SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_total FROM products")) {
    $row = $result->fetch_assoc();
    $productCount = (int) ($row['total'] ?? 0);
    $activeProductCount = (int) ($row['active_total'] ?? 0);
}

// Đếm đơn hàng đang chờ xử lý.
if ($result = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status IN ('pending', 'confirmed')")) {
    $row = $result->fetch_assoc();
    $pendingOrderCount = (int) ($row['total'] ?? 0);
}

// Đếm số lượng người dùng (không tính admin).
if ($result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'")) {
    $row = $result->fetch_assoc();
    $userCount = (int) ($row['total'] ?? 0);
}

// Tính doanh thu hôm nay.
if ($result = $conn->query("SELECT COALESCE(SUM(total), 0) AS revenue FROM orders WHERE status = 'delivered' AND DATE(order_date) = CURDATE()")) {
    $row = $result->fetch_assoc();
    $todayRevenue = (float) ($row['revenue'] ?? 0);
}

// Lấy 5 đơn hàng gần nhất.
$ordersSql = "SELECT o.order_id, o.order_date, o.total, o.status, u.fullname
              FROM orders o
              LEFT JOIN users u ON o.user_id = u.user_id
              ORDER BY o.order_date DESC
              LIMIT 5";
if ($result = $conn->query($ordersSql)) {
    while ($row = $result->fetch_assoc()) {
        $latestOrders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển quản trị - Snowboard Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../Css/Admin/admin_home.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <img src="../../Images/logo/logo.jpg" alt="Logo" class="sidebar-logo">
            <h4 class="sidebar-title">Snowboard Admin</h4>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <a href="admin_home.php" class="nav-link active">
                <i class="fas fa-home"></i>
                <span>Bảng điều khiển</span>
            </a>
            <a href="admin_product.php" class="nav-link">
                <i class="fas fa-box"></i>
                <span>Quản lý sản phẩm</span>
            </a>
            <a href="admin_order.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i>
                <span>Quản lý đơn hàng</span>
            </a>
            <a href="admin_user.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Quản lý người dùng</span>
            </a>
            <a href="admin_promotion.php" class="nav-link">
                <i class="fas fa-tags"></i>
                <span>Khuyến mãi & Voucher</span>
            </a>
            <a href="admin_review.php" class="nav-link">
                <i class="fas fa-star"></i>
                <span>Quản lý đánh giá</span>
            </a>
            <a href="admin_revenue.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Báo cáo doanh thu</span>
            </a>
            <a href="admin_email.php" class="nav-link">
                <i class="fas fa-envelope"></i>
                <span>Gửi Email</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="../../controller/controller_User/controller.php?action=logout" class="nav-link logout-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Đăng xuất</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="navbar-title">
                <h5 class="mb-0">Bảng điều khiển</h5>
            </div>
            <div class="navbar-right">
                <span class="navbar-time" id="dashboard-clock"></span>
                <div class="navbar-user">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($adminName); ?></span>
                </div>
            </div>
        </nav>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="banner-overlay"></div>
            <div class="banner-content">
                <h2>Chào mừng trở lại, <?php echo htmlspecialchars($adminName); ?>!</h2>
                <p>Quản lý cửa hàng Snowboard của bạn một cách dễ dàng</p>
            </div>
        </div>

        <!-- Main Dashboard Content -->
        <div class="container-fluid py-4">

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card card-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title">Tổng sản phẩm</h6>
                                    <h2 class="card-value mb-1"><?php echo number_format($productCount); ?></h2>
                                    <p class="card-subtitle">Đang bán: <?php echo number_format($activeProductCount); ?></p>
                                </div>
                                <div class="card-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                            </div>
                            <a href="admin_product.php" class="card-link">
                                Quản lý sản phẩm <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stats-card card-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title">Đơn hàng chờ</h6>
                                    <h2 class="card-value mb-1"><?php echo number_format($pendingOrderCount); ?></h2>
                                    <p class="card-subtitle">Cần xử lý ngay</p>
                                </div>
                                <div class="card-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <a href="admin_order.php" class="card-link">
                                Xem đơn hàng <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stats-card card-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title">Doanh thu hôm nay</h6>
                                    <h2 class="card-value mb-1"><?php echo number_format($todayRevenue, 0, ',', '.'); ?>₫</h2>
                                    <p class="card-subtitle">Đã giao thành công</p>
                                </div>
                                <div class="card-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                            <a href="admin_revenue.php" class="card-link">
                                Xem báo cáo <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stats-card card-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title">Khách hàng</h6>
                                    <h2 class="card-value mb-1"><?php echo number_format($userCount); ?></h2>
                                    <p class="card-subtitle">Đang hoạt động</p>
                                </div>
                                <div class="card-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <a href="admin_user.php" class="card-link">
                                Quản lý user <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Hành động nhanh</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <a href="admin_product.php" class="quick-action-card">
                                        <div class="quick-icon bg-primary">
                                            <i class="fas fa-plus"></i>
                                        </div>
                                        <div class="quick-text">
                                            <h6>Thêm sản phẩm</h6>
                                            <p>Tạo mới danh mục hàng hóa</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <a href="admin_order.php" class="quick-action-card">
                                        <div class="quick-icon bg-warning">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                        <div class="quick-text">
                                            <h6>Theo dõi đơn hàng</h6>
                                            <p>Cập nhật trạng thái vận chuyển</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <a href="admin_promotion.php" class="quick-action-card">
                                        <div class="quick-icon bg-success">
                                            <i class="fas fa-percentage"></i>
                                        </div>
                                        <div class="quick-text">
                                            <h6>Quản lý khuyến mãi</h6>
                                            <p>Thiết lập mã giảm giá</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <a href="admin_review.php" class="quick-action-card">
                                        <div class="quick-icon bg-info">
                                            <i class="fas fa-comment-dots"></i>
                                        </div>
                                        <div class="quick-text">
                                            <h6>Duyệt đánh giá</h6>
                                            <p>Kiểm soát nội dung phản hồi</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Đơn hàng gần đây</h5>
                            <a href="admin_order.php" class="btn btn-sm btn-dark">
                                Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Khách hàng</th>
                                            <th>Ngày đặt</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($latestOrders)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                    Chưa có đơn hàng nào
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($latestOrders as $order): ?>
                                                <tr>
                                                    <td><strong>#<?php echo (int) $order['order_id']; ?></strong></td>
                                                    <td><?php echo htmlspecialchars($order['fullname'] ?? 'Khách lẻ'); ?></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                                    <td><strong><?php echo number_format($order['total'], 0, ',', '.'); ?>₫</strong></td>
                                                    <td>
                                                        <?php
                                                        $statusMap = [
                                                            'pending' => ['class' => 'warning', 'text' => 'Chờ xử lý'],
                                                            'confirmed' => ['class' => 'info', 'text' => 'Đã xác nhận'],
                                                            'shipping' => ['class' => 'primary', 'text' => 'Đang giao'],
                                                            'delivered' => ['class' => 'success', 'text' => 'Đã giao'],
                                                            'cancelled' => ['class' => 'danger', 'text' => 'Đã hủy']
                                                        ];
                                                        $status = $statusMap[$order['status']] ?? ['class' => 'secondary', 'text' => $order['status']];
                                                        ?>
                                                        <span class="badge bg-<?php echo $status['class']; ?>">
                                                            <?php echo $status['text']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="admin_order.php?id=<?php echo $order['order_id']; ?>" 
                                                           class="btn btn-sm btn-outline-dark">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../Js/Admin/home.js"></script>
</body>
</html>
