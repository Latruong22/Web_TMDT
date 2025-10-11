<?php
session_start();
require_once '../../model/database.php';
checkAccess('admin');
require_once '../../model/revenue_model.php';

$inputFilters = [
	'range' => $_GET['range'] ?? 'last_30',
	'from' => $_GET['from'] ?? '',
	'to' => $_GET['to'] ?? '',
];

$overview = getRevenueOverview($inputFilters);
$filters = $overview['filters'];
$trend = getRevenueTrend($filters);
$statusBreakdown = getRevenueStatusBreakdown($filters);
$topProducts = getTopProductsByRevenue($filters);
$topCustomers = getTopCustomersByRevenue($filters);

$msg = $_GET['msg'] ?? '';
$message_map = [
	'export_error' => 'Không thể xuất báo cáo, vui lòng thử lại.',
];
$alert_text = $message_map[$msg] ?? '';

function format_currency($amount) {
	return number_format((float) $amount, 0, ',', '.') . ' ₫';
}

$range = $filters['range'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo doanh thu - Snowboard Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../Css/Admin/admin_home.css">
    <link rel="stylesheet" href="../../Css/Admin/admin_revenue.css">
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
            <a href="admin_revenue.php" class="nav-link active">
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
                <h5 class="mb-0">Báo cáo doanh thu</h5>
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
                		<div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo htmlspecialchars($alert_text); ?></div>
                	<?php endif; ?>

                	<section class="filter-section">
                		<form method="get" class="filter-form" id="revenue-filter-form">
                			<label>
                				Khoảng thời gian
                				<select name="range" id="range-select">
                					<option value="last_7" <?php echo $range === 'last_7' ? 'selected' : ''; ?>>7 ngày gần nhất</option>
                					<option value="last_30" <?php echo $range === 'last_30' ? 'selected' : ''; ?>>30 ngày gần nhất</option>
                					<option value="last_90" <?php echo $range === 'last_90' ? 'selected' : ''; ?>>90 ngày gần nhất</option>
                					<option value="this_month" <?php echo $range === 'this_month' ? 'selected' : ''; ?>>Tháng này</option>
                					<option value="custom" <?php echo $range === 'custom' ? 'selected' : ''; ?>>Tùy chọn</option>
                				</select>
                			</label>
                			<label>
                				Từ ngày
                				<input type="date" name="from" id="from-date" value="<?php echo htmlspecialchars($filters['from']); ?>" <?php echo $range === 'custom' ? '' : 'disabled'; ?>>
                			</label>
                			<label>
                				Đến ngày
                				<input type="date" name="to" id="to-date" value="<?php echo htmlspecialchars($filters['to']); ?>" <?php echo $range === 'custom' ? '' : 'disabled'; ?>>
                			</label>
                			<div class="filter-actions">
                				<button type="submit" class="btn-primary">Áp dụng</button>
                				<a class="btn-secondary" href="admin_revenue.php">Làm mới</a>
                				<button type="submit" formaction="../../controller/controller_Admin/admin_revenue_controller.php" formmethod="post" name="action" value="export_csv" class="btn-secondary export-btn">Xuất CSV</button>
                			</div>
                		</form>
                	</section>

                	<section class="summary-grid">
                		<article class="summary-card">
                			<h3>Tổng doanh thu</h3>
                			<p class="summary-value highlight"><?php echo format_currency($overview['total_revenue']); ?></p>
                			<span class="muted"><?php echo htmlspecialchars($filters['from']); ?> → <?php echo htmlspecialchars($filters['to']); ?></span>
                		</article>
                		<article class="summary-card">
                			<h3>Đơn hàng</h3>
                			<p class="summary-value"><?php echo number_format($overview['total_orders']); ?></p>
                			<span class="muted">Đã giao: <?php echo number_format($overview['delivered_orders']); ?></span>
                		</article>
                		<article class="summary-card">
                			<h3>Giá trị trung bình</h3>
                			<p class="summary-value"><?php echo format_currency($overview['avg_order_value']); ?></p>
                			<span class="muted">Chỉ tính đơn đã giao</span>
                		</article>
                		<article class="summary-card">
                			<h3>Đơn hủy</h3>
                			<p class="summary-value danger"><?php echo number_format($overview['cancelled_orders']); ?></p>
                			<span class="muted">Giai đoạn đã chọn</span>
                		</article>
                	</section>

                	<section class="grid-layout">
                		<article class="card">
                			<header class="card-header">
                				<h2>Xu hướng doanh thu</h2>
                				<span><?php echo count($trend); ?> ngày</span>
                			</header>
                			<?php if (empty($trend)): ?>
                				<p class="empty">Chưa có số liệu cho giai đoạn này.</p>
                			<?php else: ?>
                				<?php $maxRevenue = max(array_column($trend, 'revenue')); ?>
                				<ul class="trend-list">
                					<?php foreach ($trend as $item):
                						$percent = $maxRevenue > 0 ? round(($item['revenue'] / $maxRevenue) * 100) : 0;
                					?>
                						<li>
                							<div class="trend-info">
                								<strong><?php echo date('d/m', strtotime($item['order_day'])); ?></strong>
                								<span><?php echo format_currency($item['revenue']); ?></span>
                							</div>
                							<div class="trend-bar">
                								<div class="trend-bar-fill" style="width: <?php echo $percent; ?>%"></div>
                							</div>
                							<div class="trend-meta">Đơn giao: <?php echo (int) $item['delivered_orders']; ?> | Đơn hủy: <?php echo (int) $item['cancelled_orders']; ?></div>
                						</li>
                					<?php endforeach; ?>
                				</ul>
                			<?php endif; ?>
                		</article>

                		<article class="card">
                			<header class="card-header">
                				<h2>Trạng thái đơn hàng</h2>
                			</header>
                			<?php if (empty($statusBreakdown)): ?>
                				<p class="empty">Chưa có số liệu cho giai đoạn này.</p>
                			<?php else: ?>
                				<ul class="status-list">
                					<?php foreach ($statusBreakdown as $status => $data): ?>
                						<li>
                							<span class="status-label status-<?php echo htmlspecialchars($status); ?>"><?php
                								switch ($status) {
                									case 'pending':
                										echo 'Chờ xác nhận';
                										break;
                									case 'confirmed':
                										echo 'Đã xác nhận';
                										break;
                									case 'shipping':
                										echo 'Đang giao';
                										break;
                									case 'delivered':
                										echo 'Đã giao';
                										break;
                									case 'cancelled':
                										echo 'Đã hủy';
                										break;
                									default:
                										echo ucfirst($status);
                								}
                							?></span>
                							<span class="status-number"><?php echo number_format($data['total_orders']); ?> đơn</span>
                							<span class="status-revenue"><?php echo format_currency($data['revenue']); ?></span>
                						</li>
                					<?php endforeach; ?>
                				</ul>
                			<?php endif; ?>
                		</article>
                	</section>

                	<section class="grid-layout">
                		<article class="card">
                			<header class="card-header">
                				<h2>Sản phẩm bán chạy</h2>
                			</header>
                			<div class="table-responsive">
                				<table>
                					<thead>
                					<tr>
                						<th>Sản phẩm</th>
                						<th>Số lượng</th>
                						<th>Doanh thu</th>
                					</tr>
                					</thead>
                					<tbody>
                					<?php if (empty($topProducts)): ?>
                						<tr><td colspan="3" class="empty">Chưa có dữ liệu.</td></tr>
                					<?php else: ?>
                						<?php foreach ($topProducts as $product): ?>
                							<tr>
                								<td><?php echo htmlspecialchars($product['name'] ?? 'Sản phẩm đã xóa'); ?></td>
                								<td><?php echo number_format($product['total_quantity']); ?></td>
                								<td><?php echo format_currency($product['total_revenue']); ?></td>
                							</tr>
                						<?php endforeach; ?>
                					<?php endif; ?>
                					</tbody>
                				</table>
                			</div>
                		</article>

                		<article class="card">
                			<header class="card-header">
                				<h2>Khách hàng tiêu biểu</h2>
                			</header>
                			<div class="table-responsive">
                				<table>
                					<thead>
                					<tr>
                						<th>Khách hàng</th>
                						<th>Số đơn</th>
                						<th>Tổng chi</th>
                					</tr>
                					</thead>
                					<tbody>
                					<?php if (empty($topCustomers)): ?>
                						<tr><td colspan="3" class="empty">Chưa có dữ liệu.</td></tr>
                					<?php else: ?>
                						<?php foreach ($topCustomers as $customer): ?>
                							<tr>
                								<td>
                									<div><strong><?php echo htmlspecialchars($customer['fullname'] ?? 'Khách lẻ'); ?></strong></div>
                									<div class="muted"><?php echo htmlspecialchars($customer['email'] ?? '-'); ?></div>
                								</td>
                								<td><?php echo number_format($customer['orders_count']); ?></td>
                								<td><?php echo format_currency($customer['total_spent']); ?></td>
                							</tr>
                						<?php endforeach; ?>
                					<?php endif; ?>
                					</tbody>
                				</table>
                			</div>
                		</article>
                	</section>
        </div>
    </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../Js/Admin/home.js"></script>
    <script src="../../Js/Admin/revenue.js"></script>
</body>
</html>