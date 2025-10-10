<?php
require_once 'database.php';

// Lấy bộ lọc chuẩn hóa với khoảng thời gian mặc định
function normalizeRevenueFilters(array $filters = []) {
	$range = $filters['range'] ?? 'last_30';
	$from = $filters['from'] ?? '';
	$to = $filters['to'] ?? '';

	// Nếu người dùng nhập ngày thủ công thì ưu tiên dùng chế độ tùy chọn
	if ($range !== 'custom' && ($from !== '' || $to !== '')) {
		$range = 'custom';
	}

	if ($range === 'custom' && $from !== '' && $to !== '' && $from > $to) {
		[$from, $to] = [$to, $from];
	}

	if ($range !== 'custom') {
		switch ($range) {
			case 'last_7':
				$from = date('Y-m-d', strtotime('-6 days'));
				$to = date('Y-m-d');
				break;
			case 'last_30':
			default:
				$from = date('Y-m-d', strtotime('-29 days'));
				$to = date('Y-m-d');
				$range = 'last_30';
				break;
			case 'last_90':
				$from = date('Y-m-d', strtotime('-89 days'));
				$to = date('Y-m-d');
				break;
			case 'this_month':
				$from = date('Y-m-01');
				$to = date('Y-m-t');
				break;
		}
	} else {
		if (empty($from)) {
			$from = date('Y-m-d', strtotime('-29 days'));
		}
		if (empty($to)) {
			$to = date('Y-m-d');
		}
		if ($from > $to) {
			[$from, $to] = [$to, $from];
		}
	}

	return [
		'range' => $range,
		'from' => $from,
		'to' => $to,
	];
}

// Tổng quan doanh thu trong khoảng thời gian lựa chọn
function getRevenueOverview(array $filters = []) {
	global $conn;
	$filters = normalizeRevenueFilters($filters);

	$sql = "SELECT
				COALESCE(SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END), 0) AS total_revenue,
				COUNT(*) AS total_orders,
				SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered_orders,
				SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders
			FROM orders
			WHERE DATE(order_date) BETWEEN ? AND ?";

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		return [
			'total_revenue' => 0,
			'total_orders' => 0,
			'delivered_orders' => 0,
			'cancelled_orders' => 0,
			'avg_order_value' => 0,
		];
	}

	$stmt->bind_param('ss', $filters['from'], $filters['to']);
	$stmt->execute();
	$result = $stmt->get_result();
	$data = $result ? $result->fetch_assoc() : [];
	$stmt->close();

	$totalRevenue = (float) ($data['total_revenue'] ?? 0);
	$totalOrders = (int) ($data['total_orders'] ?? 0);
	$deliveredOrders = (int) ($data['delivered_orders'] ?? 0);
	$cancelledOrders = (int) ($data['cancelled_orders'] ?? 0);

	$avgOrderValue = $deliveredOrders > 0 ? round($totalRevenue / $deliveredOrders, 2) : 0;

	return [
		'total_revenue' => $totalRevenue,
		'total_orders' => $totalOrders,
		'delivered_orders' => $deliveredOrders,
		'cancelled_orders' => $cancelledOrders,
		'avg_order_value' => $avgOrderValue,
		'filters' => $filters,
	];
}

// Doanh thu theo từng ngày trong khoảng thời gian
function getRevenueTrend(array $filters = []) {
	global $conn;
	$filters = normalizeRevenueFilters($filters);

	$sql = "SELECT DATE(order_date) AS order_day,
				   SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END) AS revenue,
				   SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered_orders,
				   SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders
			FROM orders
			WHERE DATE(order_date) BETWEEN ? AND ?
			GROUP BY DATE(order_date)
			ORDER BY order_day";

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		return [];
	}

	$stmt->bind_param('ss', $filters['from'], $filters['to']);
	$stmt->execute();
	$result = $stmt->get_result();

	$trend = [];
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$trend[] = $row;
		}
	}
	$stmt->close();

	return $trend;
}

// Thống kê doanh thu theo trạng thái đơn hàng
function getRevenueStatusBreakdown(array $filters = []) {
	global $conn;
	$filters = normalizeRevenueFilters($filters);

	$sql = "SELECT status,
				   COUNT(*) AS total_orders,
				   COALESCE(SUM(total), 0) AS revenue
			FROM orders
			WHERE DATE(order_date) BETWEEN ? AND ?
			GROUP BY status";

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		return [];
	}

	$stmt->bind_param('ss', $filters['from'], $filters['to']);
	$stmt->execute();
	$result = $stmt->get_result();

	$data = [];
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$data[$row['status']] = [
				'total_orders' => (int) $row['total_orders'],
				'revenue' => (float) $row['revenue'],
			];
		}
	}
	$stmt->close();

	return $data;
}

// Sản phẩm bán chạy theo doanh thu
function getTopProductsByRevenue(array $filters = [], $limit = 5) {
	global $conn;
	$filters = normalizeRevenueFilters($filters);

	$sql = "SELECT p.product_id, p.name,
				   SUM(od.quantity) AS total_quantity,
				   SUM(od.quantity * od.price) AS total_revenue
			FROM order_details od
			INNER JOIN orders o ON od.order_id = o.order_id
			INNER JOIN products p ON od.product_id = p.product_id
			WHERE DATE(o.order_date) BETWEEN ? AND ?
			  AND o.status = 'delivered'
			GROUP BY p.product_id, p.name
			ORDER BY total_revenue DESC
			LIMIT ?";

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		return [];
	}

	$stmt->bind_param('ssi', $filters['from'], $filters['to'], $limit);
	$stmt->execute();
	$result = $stmt->get_result();

	$products = [];
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$products[] = $row;
		}
	}
	$stmt->close();

	return $products;
}

// Khách hàng mang lại doanh thu cao nhất
function getTopCustomersByRevenue(array $filters = [], $limit = 5) {
	global $conn;
	$filters = normalizeRevenueFilters($filters);

	$sql = "SELECT u.user_id, u.fullname, u.email,
				   SUM(o.total) AS total_spent,
				   COUNT(*) AS orders_count
			FROM orders o
			INNER JOIN users u ON o.user_id = u.user_id
			WHERE DATE(o.order_date) BETWEEN ? AND ?
			  AND o.status = 'delivered'
			GROUP BY u.user_id, u.fullname, u.email
			ORDER BY total_spent DESC
			LIMIT ?";

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		return [];
	}

	$stmt->bind_param('ssi', $filters['from'], $filters['to'], $limit);
	$stmt->execute();
	$result = $stmt->get_result();

	$customers = [];
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$customers[] = $row;
		}
	}
	$stmt->close();

	return $customers;
}

// Lấy dữ liệu chi tiết đơn hàng để xuất báo cáo
function getRevenueReportRows(array $filters = []) {
	global $conn;
	$filters = normalizeRevenueFilters($filters);

	$sql = "SELECT o.order_id, o.order_date, o.total, o.status,
				   u.fullname, u.email,
				   v.code AS voucher_code
			FROM orders o
			LEFT JOIN users u ON o.user_id = u.user_id
			LEFT JOIN vouchers v ON o.voucher_id = v.voucher_id
			WHERE DATE(o.order_date) BETWEEN ? AND ?
			ORDER BY o.order_date DESC";

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		return [];
	}

	$stmt->bind_param('ss', $filters['from'], $filters['to']);
	$stmt->execute();
	$result = $stmt->get_result();

	$rows = [];
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
	}
	$stmt->close();

	return $rows;
}
?>
