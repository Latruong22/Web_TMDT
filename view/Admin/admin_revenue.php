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
	<title>Báo cáo doanh thu</title>
	<link rel="stylesheet" href="../../Css/Admin/admin_revenue.css">
</head>
<body>
<div class="admin-container">
	<header class="page-header">
		<div>
			<h1>Báo cáo doanh thu</h1>
			<p>Theo dõi kết quả bán hàng và hiệu suất theo khoảng thời gian.</p>
		</div>
		<a class="back-link" href="admin_home.php">← Quay lại bảng điều khiển</a>
	</header>

	<?php if ($alert_text): ?>
		<div class="alert error"><?php echo htmlspecialchars($alert_text); ?></div>
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

<script src="../../Js/Admin/revenue.js"></script>
</body>
</html>
