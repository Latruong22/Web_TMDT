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
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" href="../../Css/Admin/admin_order.css">
</head>
<body>
<div class="admin-container">
    <header class="page-header">
        <div>
            <h1>Quản lý đơn hàng</h1>
            <p>Theo dõi, cập nhật và xử lý đơn hàng của khách.</p>
        </div>
        <a class="back-link" href="admin_home.php">← Quay lại bảng điều khiển</a>
    </header>

    <?php if ($alert_text): ?>
        <div class="alert <?php echo in_array($msg, ['updated']) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($alert_text); ?>
        </div>
    <?php endif; ?>

    <section class="summary-grid">
        <article class="summary-card">
            <h3>Tổng đơn</h3>
            <p class="summary-value"><?php echo number_format($totalOrders); ?></p>
        </article>
        <?php foreach ($statuses as $key => $label): ?>
            <article class="summary-card">
                <h3><?php echo $label; ?></h3>
                <p class="summary-value"><?php echo number_format($orderStatusCounts[$key] ?? 0); ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="filter-section">
        <form method="get" class="filter-form">
            <label>
                Trạng thái
                <select name="status">
                    <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                    <?php foreach ($statuses as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $filter_status === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Tìm kiếm
                <input type="text" name="search" placeholder="Mã đơn, tên hoặc email" value="<?php echo htmlspecialchars($filter_search); ?>">
            </label>
            <label>
                Từ ngày
                <input type="date" name="from" value="<?php echo htmlspecialchars($filter_from); ?>">
            </label>
            <label>
                Đến ngày
                <input type="date" name="to" value="<?php echo htmlspecialchars($filter_to); ?>">
            </label>
            <div class="filter-actions">
                <button type="submit" class="btn-primary">Lọc</button>
                <a class="btn-secondary" href="admin_order.php">Làm mới</a>
            </div>
        </form>
    </section>

    <section class="table-section">
        <div class="section-header">
            <h2>Danh sách đơn hàng</h2>
            <span><?php echo count($orders); ?> đơn</span>
        </div>
        <div class="table-responsive">
            <table>
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
                    <tr><td colspan="6" class="empty">Không tìm thấy đơn hàng phù hợp.</td></tr>
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
                            <td><span class="status status-<?php echo htmlspecialchars($order['status']); ?>"><?php echo $statuses[$order['status']] ?? $order['status']; ?></span></td>
                            <td class="actions"><button type="button" class="btn-link toggle-details" data-target="details-<?php echo (int) $order['order_id']; ?>">Chi tiết</button></td>
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
                                            <label>
                                                Trạng thái
                                                <select name="status" required>
                                                    <?php foreach ($statuses as $value => $label): ?>
                                                        <option value="<?php echo $value; ?>" <?php echo $order['status'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </label>
                                            <label>
                                                Địa chỉ giao hàng
                                                <textarea name="shipping_address" rows="2" placeholder="Nhập địa chỉ giao hàng"><?php echo htmlspecialchars($order['shipping_address'] ?? ''); ?></textarea>
                                            </label>
                                            <label>
                                                Ghi chú
                                                <textarea name="note" rows="2" placeholder="Ghi chú xử lý đơn"><?php echo htmlspecialchars($order['note'] ?? ''); ?></textarea>
                                            </label>
                                            <button type="submit" class="btn-primary">Lưu thay đổi</button>
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
    </section>
</div>

<script src="../../Js/Admin/order.js"></script>
</body>
</html>
