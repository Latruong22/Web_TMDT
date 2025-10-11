<?php
session_start();
require_once '../../model/database.php';
checkAccess('admin');
require_once '../../model/order_model.php';

$statuses = [
    'pending' => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao',
    'delivered' => 'Đã giao',
    'cancelled' => 'Đã hủy',
];

$filter_status = $_GET['status'] ?? 'all';
$filter_search = trim($_GET['search'] ?? '');
$filter_from = $_GET['from'] ?? '';
$filter_to = $_GET['to'] ?? '';

$filters = [
    'status' => $filter_status,
    'search' => $filter_search,
    'from' => $filter_from,
    'to' => $filter_to,
];

$orders = getOrders($filters);
$orderStatusCounts = getOrderStatusCounts();
$totalOrders = array_sum($orderStatusCounts);
$currentUrl = $_SERVER['REQUEST_URI'] ?? '../../view/Admin/admin_order.php';
$msg = $_GET['msg'] ?? '';

$message_map = [
    'updated' => 'Đã cập nhật đơn hàng thành công.',
    'invalid' => 'Dữ liệu gửi lên không hợp lệ.',
    'error' => 'Có lỗi xảy ra, vui lòng thử lại.',
    'notfound' => 'Không tìm thấy đơn hàng tương ứng.',
];
$alert_text = $message_map[$msg] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Snowboard Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../Css/Admin/admin_home.css">
    <link rel="stylesheet" href="../../Css/Admin/admin_order.css">
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
            <a href="admin_home.php" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Bảng điều khiển</span>
            </a>
            <a href="admin_product.php" class="nav-link">
                <i class="fas fa-box"></i>
                <span>Quản lý sản phẩm</span>
            </a>
            <a href="admin_order.php" class="nav-link active">
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
                <h5 class="mb-0">Quản lý đơn hàng</h5>
            </div>
            <div class="navbar-right">
                <span class="navbar-time" id="dashboard-clock"></span>
                <div class="navbar-user">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Admin'); ?></span>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="container-fluid py-4">
            <div class="admin-container">

                <?php if ($alert_text): ?>
                    <div class="alert alert-<?php echo in_array($msg, ['updated']) ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($alert_text); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-3 mb-4">
                    <div class="col-md-4 col-lg-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="text-muted">Tổng đơn</h6>
                                <h3 class="mb-0"><?php echo number_format($totalOrders); ?></h3>
                            </div>
                        </div>
                    </div>
                    <?php foreach ($statuses as $key => $label): ?>
                        <div class="col-md-4 col-lg-2">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h6 class="text-muted"><?php echo $label; ?></h6>
                                    <h3 class="mb-0"><?php echo number_format($orderStatusCounts[$key] ?? 0); ?></h3>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Bộ lọc</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                                    <?php foreach ($statuses as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo $filter_status === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tìm kiếm</label>
                                <input type="text" name="search" class="form-control" placeholder="Mã đơn, tên hoặc email" value="<?php echo htmlspecialchars($filter_search); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Từ ngày</label>
                                <input type="date" name="from" class="form-control" value="<?php echo htmlspecialchars($filter_from); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Đến ngày</label>
                                <input type="date" name="to" class="form-control" value="<?php echo htmlspecialchars($filter_to); ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i>Lọc
                                </button>
                                <a class="btn btn-secondary" href="admin_order.php">
                                    <i class="fas fa-redo me-1"></i>Làm mới
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Danh sách đơn hàng</h5>
                        <span class="badge bg-primary"><?php echo count($orders); ?> đơn</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="6" class="text-center empty">Không tìm thấy đơn hàng phù hợp.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo (int) $order['order_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($order['fullname'] ?? 'Khách lẻ'); ?></strong>
                                <div class="muted">Email: <?php echo htmlspecialchars($order['email'] ?? '-'); ?></div>
                                <div class="muted">SĐT: <?php echo htmlspecialchars($order['phone'] ?? '-'); ?></div>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                            <td><?php echo number_format($order['total'], 0, ',', '.'); ?> ₫</td>
                            <td><span class="badge bg-<?php echo $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'cancelled' ? 'danger' : 'warning'); ?>"><?php echo $statuses[$order['status']] ?? $order['status']; ?></span></td>
                            <td class="actions">
                                <button type="button" class="btn btn-sm btn-info toggle-details" data-target="details-<?php echo (int) $order['order_id']; ?>">
                                    <i class="fas fa-eye me-1"></i>Chi tiết
                                </button>
                            </td>
                        </tr>
                        <tr id="details-<?php echo (int) $order['order_id']; ?>" class="order-details">
                            <td colspan="6">
                                <div class="details-grid">
                                    <div>
                                        <h3>Thông tin giao hàng</h3>
                                        <p><strong>Địa chỉ:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? '')); ?></p>
                                        <p><strong>Ghi chú:</strong> <?php echo nl2br(htmlspecialchars($order['note'] ?? 'Không có ghi chú.')); ?></p>
                                        <?php if (!empty($order['voucher_code'])): ?>
                                            <p><strong>Voucher:</strong> <?php echo htmlspecialchars($order['voucher_code']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h3>Cập nhật đơn hàng</h3>
                                        <form method="post" action="../../controller/controller_Admin/admin_order_controller.php" class="order-update-form">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo (int) $order['order_id']; ?>">
                                            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($currentUrl); ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Trạng thái</label>
                                                <select name="status" class="form-select" required>
                                                    <?php foreach ($statuses as $value => $label): ?>
                                                        <option value="<?php echo $value; ?>" <?php echo $order['status'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Địa chỉ giao hàng</label>
                                                <textarea name="shipping_address" rows="2" class="form-control" placeholder="Nhập địa chỉ giao hàng"><?php echo htmlspecialchars($order['shipping_address'] ?? ''); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Ghi chú</label>
                                                <textarea name="note" rows="2" class="form-control" placeholder="Ghi chú xử lý đơn"><?php echo htmlspecialchars($order['note'] ?? ''); ?></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>Lưu thay đổi
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="items">
                                    <h3>Sản phẩm trong đơn</h3>
                                    <div class="items-list">
                                        <?php $items = getOrderItems($order['order_id']); ?>
                                        <?php if (empty($items)): ?>
                                            <p class="muted">Không có sản phẩm.</p>
                                        <?php else: ?>
                                            <?php foreach ($items as $item): ?>
                                                <article class="item">
                                                    <div class="item-info">
                                                        <strong><?php echo htmlspecialchars($item['product_name'] ?? 'Sản phẩm đã xóa'); ?></strong>
                                                        <div>Số lượng: <?php echo (int) $item['quantity']; ?></div>
                                                        <div>Đơn giá: <?php echo number_format($item['price'], 0, ',', '.'); ?> ₫</div>
                                                    </div>
                                                    <div class="item-summary">
                                                        <span>Tổng: <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> ₫</span>
                                                    </div>
                                                </article>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
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

    <!-- Bootstrap JS -->
    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../Js/Admin/home.js"></script>
    <script src="../../Js/Admin/order.js"></script>
</body>
</html>
