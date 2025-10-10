<?php
require_once 'database.php';

/**
 * Lấy danh sách đơn hàng với tùy chọn lọc theo trạng thái, tìm kiếm và khoảng thời gian.
 *
 * @param array $filters ['status' => string|null, 'search' => string|null, 'from' => string|null, 'to' => string|null]
 * @return array
 */
function getOrders(array $filters = []) {
    global $conn;

    $sql = "SELECT o.order_id, o.user_id, o.order_date, o.total, o.status, o.voucher_id, o.shipping_address, o.note,
                   u.fullname, u.email, u.phone,
                   v.code AS voucher_code
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            LEFT JOIN vouchers v ON o.voucher_id = v.voucher_id
            WHERE 1 = 1";

    $types = '';
    $params = [];

    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $sql .= " AND o.status = ?";
        $types .= 's';
        $params[] = $filters['status'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (o.order_id = ? OR u.fullname LIKE ? OR u.email LIKE ?)";
        $types .= 'iss';
        $params[] = (int) $filters['search'];
        $likeTerm = '%' . $filters['search'] . '%';
        $params[] = $likeTerm;
        $params[] = $likeTerm;
    }

    if (!empty($filters['from'])) {
        $sql .= " AND DATE(o.order_date) >= ?";
        $types .= 's';
        $params[] = $filters['from'];
    }

    if (!empty($filters['to'])) {
        $sql .= " AND DATE(o.order_date) <= ?";
        $types .= 's';
        $params[] = $filters['to'];
    }

    $sql .= " ORDER BY o.order_date DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log('Query prepare failed: ' . $conn->error);
        return [];
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }

    $stmt->close();
    return $orders;
}

/**
 * Lấy thông tin chi tiết của một đơn hàng.
 */
function getOrderById($order_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT o.order_id, o.user_id, o.order_date, o.total, o.status, o.voucher_id, o.shipping_address, o.note,
                                   u.fullname, u.email, u.phone,
                                   v.code AS voucher_code
                            FROM orders o
                            LEFT JOIN users u ON o.user_id = u.user_id
                            LEFT JOIN vouchers v ON o.voucher_id = v.voucher_id
                            WHERE o.order_id = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $order;
}

/**
 * Lấy danh sách sản phẩm trong đơn hàng.
 */
function getOrderItems($order_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT od.order_detail_id, od.order_id, od.product_id, od.quantity, od.price, od.reviewed,
                                   p.name AS product_name, p.image AS product_image
                            FROM order_details od
                            LEFT JOIN products p ON od.product_id = p.product_id
                            WHERE od.order_id = ?");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    $stmt->close();
    return $items;
}

/**
 * Cập nhật trạng thái, địa chỉ giao hàng và ghi chú của đơn.
 */
function updateOrderStatus($order_id, $status, $shipping_address, $note) {
    global $conn;
    $stmt = $conn->prepare("UPDATE orders SET status = ?, shipping_address = ?, note = ? WHERE order_id = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('sssi', $status, $shipping_address, $note, $order_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

/**
 * Thống kê số lượng đơn theo trạng thái.
 */
function getOrderStatusCounts() {
    global $conn;
    $sql = "SELECT status, COUNT(*) AS total FROM orders GROUP BY status";
    $result = $conn->query($sql);
    $counts = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $counts[$row['status']] = (int) $row['total'];
        }
    }
    return $counts;
}
?>
