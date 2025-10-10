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
    <title>Bảng điều khiển quản trị</title>
    <link rel="stylesheet" href="../../Css/Admin/admin_home.css">
</head>
<body>
    <div class="admin-wrapper">
        <header class="admin-header">
            <div>
                <h1>Bảng điều khiển</h1>
                <p>Chào mừng, <strong><?php echo htmlspecialchars($adminName); ?></strong></p>
            </div>
            <div class="header-meta">
                <span id="dashboard-clock"></span>
                <a class="btn-secondary" href="../../controller/controller_User/controller.php?action=logout">Đăng xuất</a>
            </div>
        </header>

        <section class="dashboard-grid">
            <article class="card primary">
                <h2>Tổng sản phẩm</h2>
                <p class="card-value"><?php echo number_format($productCount); ?></p>
                <p class="card-sub">Đang bán: <?php echo number_format($activeProductCount); ?></p>
                <a class="card-link" href="admin_product.php">Quản lý sản phẩm →</a>
            </article>

            <article class="card warning">
                <h2>Đơn hàng cần xử lý</h2>
                <p class="card-value"><?php echo number_format($pendingOrderCount); ?></p>
                <p class="card-sub">Bao gồm trạng thái chờ xác nhận &amp; chuẩn bị hàng</p>
                <a class="card-link" href="admin_order.php">Xem đơn hàng →</a>
            </article>

            <article class="card success">
                <h2>Doanh thu hôm nay</h2>
                <p class="card-value"><?php echo number_format($todayRevenue, 0, ',', '.'); ?> ₫</p>
                <p class="card-sub">Đã giao thành công trong ngày</p>
                <a class="card-link" href="admin_revenue.php">Báo cáo doanh thu →</a>
            </article>

            <article class="card info">
                <h2>Khách hàng</h2>
                <p class="card-value"><?php echo number_format($userCount); ?></p>
                <p class="card-sub">Người dùng đang hoạt động</p>
                <a class="card-link" href="admin_user.php">Quản lý người dùng →</a>
            </article>
        </section>

        <section class="quick-links">
            <h2>Liên kết nhanh</h2>
            <div class="quick-grid">
                <a class="quick-item" href="admin_product.php">
                    <span class="quick-title">Thêm sản phẩm</span>
                    <span class="quick-desc">Tạo mới danh mục hàng hóa</span>
                </a>
                <a class="quick-item" href="admin_order.php">
                    <span class="quick-title">Theo dõi đơn hàng</span>
                    <span class="quick-desc">Cập nhật trạng thái &amp; vận chuyển</span>
                </a>
                <a class="quick-item" href="admin_promotion.php">
                    <span class="quick-title">Quản lý khuyến mãi</span>
                    <span class="quick-desc">Thiết lập mã giảm giá &amp; banner</span>
                </a>
                <a class="quick-item" href="admin_review.php">
                    <span class="quick-title">Duyệt đánh giá</span>
                    <span class="quick-desc">Kiểm soát nội dung phản hồi</span>
                </a>
            </div>
        </section>

        <section class="orders-section">
            <div class="section-header">
                <h2>Đơn hàng gần đây</h2>
                <a class="btn-link" href="admin_order.php">Xem tất cả</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($latestOrders)): ?>
                            <tr><td colspan="5" class="empty">Chưa có đơn hàng nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($latestOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo (int) $order['order_id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['fullname'] ?? 'Khách lẻ'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                    <td><?php echo number_format($order['total'], 0, ',', '.'); ?> ₫</td>
                                    <td><span class="status status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script src="../../Js/Admin/home.js"></script>
</body>
</html>
