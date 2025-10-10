<?php
require_once 'database.php';

// Tự động đánh dấu những voucher đã hết hạn
function updateExpiredVouchers() {
	global $conn;
	$sql = "UPDATE vouchers
			SET status = 'expired'
			WHERE status <> 'expired'
			  AND expiry_date IS NOT NULL
			  AND expiry_date < CURDATE()";
	$conn->query($sql);
}

// Thống kê nhanh cho trang quản trị
function getVoucherSummaryStats() {
	global $conn;
	updateExpiredVouchers();

	$sql = "SELECT
				COUNT(*) AS total,
				SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_total,
				SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) AS expired_total
			FROM vouchers";
	$result = $conn->query($sql);
	$row = $result ? $result->fetch_assoc() : [];

	return [
		'total' => (int) ($row['total'] ?? 0),
		'active' => (int) ($row['active_total'] ?? 0),
		'expired' => (int) ($row['expired_total'] ?? 0),
	];
}

// Kiểm tra mã voucher đã tồn tại chưa
function voucherCodeExists($code, $excludeId = null) {
	global $conn;
	$sql = "SELECT voucher_id FROM vouchers WHERE UPPER(code) = UPPER(?)";
	if ($excludeId) {
		$sql .= " AND voucher_id <> ?";
	}

	$stmt = $conn->prepare($sql);
	if ($excludeId) {
		$stmt->bind_param('si', $code, $excludeId);
	} else {
		$stmt->bind_param('s', $code);
	}

	$stmt->execute();
	$stmt->store_result();
	$exists = $stmt->num_rows > 0;
	$stmt->close();
	return $exists;
}

// Lấy danh sách voucher với bộ lọc linh hoạt
function getVouchers(array $filters = []) {
	global $conn;
	updateExpiredVouchers();

	$sql = "SELECT v.voucher_id, v.code, v.discount, v.type, v.expiry_date, v.usage_limit, v.status,
				   COUNT(o.order_id) AS used_count
			FROM vouchers v
			LEFT JOIN orders o ON o.voucher_id = v.voucher_id
			WHERE 1 = 1";

	$types = '';
	$params = [];

	if (!empty($filters['status']) && $filters['status'] !== 'all') {
		$sql .= " AND v.status = ?";
		$types .= 's';
		$params[] = $filters['status'];
	}

	if (!empty($filters['search'])) {
		$sql .= " AND (v.code LIKE ? OR v.type LIKE ?)";
		$types .= 'ss';
		$searchTerm = '%' . $filters['search'] . '%';
		$params[] = $searchTerm;
		$params[] = $searchTerm;
	}

	if (!empty($filters['from'])) {
		$sql .= " AND (v.expiry_date IS NULL OR v.expiry_date >= ?)";
		$types .= 's';
		$params[] = $filters['from'];
	}

	if (!empty($filters['to'])) {
		$sql .= " AND (v.expiry_date IS NULL OR v.expiry_date <= ?)";
		$types .= 's';
		$params[] = $filters['to'];
	}

	$sql .= " GROUP BY v.voucher_id ORDER BY v.voucher_id DESC";

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		return [];
	}

	if (!empty($params)) {
		$stmt->bind_param($types, ...$params);
	}

	$stmt->execute();
	$result = $stmt->get_result();
	$vouchers = [];
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$vouchers[] = $row;
		}
	}
	$stmt->close();
	return $vouchers;
}

function getVoucherById($voucher_id) {
	global $conn;
	updateExpiredVouchers();

	$stmt = $conn->prepare("SELECT voucher_id, code, discount, type, expiry_date, usage_limit, status FROM vouchers WHERE voucher_id = ?");
	if (!$stmt) {
		return null;
	}
	$stmt->bind_param('i', $voucher_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$voucher = $result ? $result->fetch_assoc() : null;
	$stmt->close();
	return $voucher;
}

function createVoucher($code, $discount, $type, $expiry_date, $usage_limit, $status) {
	global $conn;
	$stmt = $conn->prepare("INSERT INTO vouchers (code, discount, type, expiry_date, usage_limit, status) VALUES (?, ?, ?, ?, ?, ?)");
	if (!$stmt) {
		return false;
	}
	$stmt->bind_param('sdssis', $code, $discount, $type, $expiry_date, $usage_limit, $status);
	$result = $stmt->execute();
	$stmt->close();
	return $result;
}

function updateVoucher($voucher_id, $code, $discount, $type, $expiry_date, $usage_limit, $status) {
	global $conn;
	$stmt = $conn->prepare("UPDATE vouchers SET code = ?, discount = ?, type = ?, expiry_date = ?, usage_limit = ?, status = ? WHERE voucher_id = ?");
	if (!$stmt) {
		return false;
	}
	$stmt->bind_param('sdssisi', $code, $discount, $type, $expiry_date, $usage_limit, $status, $voucher_id);
	$result = $stmt->execute();
	$stmt->close();
	return $result;
}

function changeVoucherStatus($voucher_id, $status) {
	global $conn;
	$stmt = $conn->prepare("UPDATE vouchers SET status = ? WHERE voucher_id = ?");
	if (!$stmt) {
		return false;
	}
	$stmt->bind_param('si', $status, $voucher_id);
	$result = $stmt->execute();
	$stmt->close();
	return $result;
}

function deleteVoucher($voucher_id) {
	global $conn;
	// Chỉ cho phép xóa khi voucher chưa được sử dụng trong đơn nào
	$stmt = $conn->prepare("SELECT COUNT(*) AS usage_total FROM orders WHERE voucher_id = ?");
	if (!$stmt) {
		return false;
	}
	$stmt->bind_param('i', $voucher_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result ? $result->fetch_assoc() : ['usage_total' => 0];
	$stmt->close();

	if ((int) ($row['usage_total'] ?? 0) > 0) {
		return false;
	}

	$delete = $conn->prepare("DELETE FROM vouchers WHERE voucher_id = ?");
	if (!$delete) {
		return false;
	}
	$delete->bind_param('i', $voucher_id);
	$result = $delete->execute();
	$delete->close();
	return $result;
}
?>
