<?php
session_start();
require_once '../../model/database.php';
checkAccess('admin');
require_once '../../model/promotion_model.php';

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

$vouchers = getVouchers($filters);
$stats = getVoucherSummaryStats();

$action = $_GET['action'] ?? '';
$edit_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$edit_voucher = ($action === 'edit' && $edit_id) ? getVoucherById($edit_id) : null;

$msg = $_GET['msg'] ?? '';
$message_map = [
	'created' => 'Đã tạo voucher mới thành công.',
	'updated' => 'Đã cập nhật voucher.',
	'deleted' => 'Đã xóa voucher khỏi hệ thống.',
	'cannot_delete' => 'Không thể xóa voucher đã được sử dụng.',
	'status_done' => 'Đã cập nhật trạng thái voucher.',
	'invalid' => 'Dữ liệu gửi lên không hợp lệ.',
	'error' => 'Có lỗi xảy ra. Vui lòng thử lại.',
];
$alert_text = $message_map[$msg] ?? ($msg && !isset($message_map[$msg]) ? $msg : '');

$statuses = [
	'all' => 'Tất cả',
	'active' => 'Đang hoạt động',
	'expired' => 'Hết hạn',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khuyến mãi & Voucher - Snowboard Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../Css/Admin/admin_home.css">
    <link rel="stylesheet" href="../../Css/Admin/admin_promotion.css">
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
            <a href="admin_promotion.php" class="nav-link active">
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
                <h5 class="mb-0">Khuyến mãi & Voucher</h5>
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
                		<div class="alert <?php echo in_array($msg, ['created', 'updated', 'deleted', 'status_done'], true) ? 'success' : 'error'; ?>">
                			<?php echo htmlspecialchars($alert_text); ?>
                		</div>
                	<?php endif; ?>

                	<section class="summary-grid">
                		<article class="summary-card">
                			<h3>Tổng voucher</h3>
                			<p class="summary-value"><?php echo number_format($stats['total']); ?></p>
                		</article>
                		<article class="summary-card">
                			<h3>Đang hoạt động</h3>
                			<p class="summary-value positive"><?php echo number_format($stats['active']); ?></p>
                		</article>
                		<article class="summary-card">
                			<h3>Hết hạn</h3>
                			<p class="summary-value danger"><?php echo number_format($stats['expired']); ?></p>
                		</article>
                	</section>

                	<section class="form-section">
                		<div class="form-header">
                			<h2><?php echo $edit_voucher ? 'Cập nhật voucher' : 'Thêm voucher mới'; ?></h2>
                			<?php if ($edit_voucher): ?>
                				<a class="btn-secondary" href="admin_promotion.php">+ Thêm voucher mới</a>
                			<?php endif; ?>
                		</div>
                		<form method="post" action="../../controller/controller_Admin/admin_promotion_controller.php" class="voucher-form">
                			<input type="hidden" name="action" value="<?php echo $edit_voucher ? 'update' : 'add'; ?>">
                			<?php if ($edit_voucher): ?>
                				<input type="hidden" name="voucher_id" value="<?php echo (int) $edit_voucher['voucher_id']; ?>">
                			<?php endif; ?>

                			<div class="form-grid">
                				<label>
                					Mã khuyến mãi
                					<input type="text" name="code" maxlength="50" placeholder="VD: SAVE20" value="<?php echo htmlspecialchars($edit_voucher['code'] ?? ''); ?>" required>
                				</label>
                				<label>
                					Loại giảm giá
                					<select name="type" required>
                						<option value="percent" <?php echo ($edit_voucher['type'] ?? '') === 'percent' ? 'selected' : ''; ?>>Phần trăm (%)</option>
                						<option value="fixed" <?php echo ($edit_voucher['type'] ?? '') === 'fixed' ? 'selected' : ''; ?>>Số tiền cố định</option>
                					</select>
                				</label>
                				<label>
                					Giá trị giảm
                					<input type="number" name="discount" step="0.01" min="0" value="<?php echo isset($edit_voucher['discount']) ? htmlspecialchars($edit_voucher['discount']) : ''; ?>" required>
                					<small>Đối với phần trăm, tối đa 100.</small>
                				</label>
                				<label>
                					Giới hạn sử dụng
                					<input type="number" name="usage_limit" min="0" value="<?php echo isset($edit_voucher['usage_limit']) ? (int) $edit_voucher['usage_limit'] : 0; ?>">
                					<small>0 nghĩa là không giới hạn.</small>
                				</label>
                				<label>
                					Ngày hết hạn
                					<input type="date" name="expiry_date" value="<?php echo htmlspecialchars($edit_voucher['expiry_date'] ?? ''); ?>">
                					<small>Để trống nếu không giới hạn thời gian.</small>
                				</label>
                				<label>
                					Trạng thái
                					<select name="status">
                						<option value="active" <?php echo ($edit_voucher['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                						<option value="expired" <?php echo ($edit_voucher['status'] ?? '') === 'expired' ? 'selected' : ''; ?>>Hết hạn</option>
                					</select>
                				</label>
                			</div>

                			<div class="form-actions">
                				<button type="submit" class="btn-primary"><?php echo $edit_voucher ? 'Lưu thay đổi' : 'Tạo voucher'; ?></button>
                				<?php if ($edit_voucher): ?>
                					<a class="btn-secondary" href="admin_promotion.php">Hủy</a>
                				<?php endif; ?>
                			</div>
                		</form>
                	</section>

                	<section class="filter-section">
                		<form method="get" class="filter-form">
                			<label>
                				Trạng thái
                				<select name="status">
                					<?php foreach ($statuses as $value => $label): ?>
                						<option value="<?php echo $value; ?>" <?php echo $filter_status === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
                					<?php endforeach; ?>
                				</select>
                			</label>
                			<label>
                				Từ khóa
                				<input type="text" name="search" placeholder="Mã hoặc loại" value="<?php echo htmlspecialchars($filter_search); ?>">
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
                				<a class="btn-secondary" href="admin_promotion.php">Làm mới</a>
                			</div>
                		</form>
                	</section>

                	<section class="table-section">
                		<div class="section-header">
                			<h2>Danh sách voucher</h2>
                			<span><?php echo count($vouchers); ?> voucher</span>
                		</div>
                		<div class="table-responsive">
                			<table>
                				<thead>
                				<tr>
                					<th>Mã</th>
                					<th>Loại</th>
                					<th>Giá trị</th>
                					<th>Đã dùng</th>
                					<th>Giới hạn</th>
                					<th>Hết hạn</th>
                					<th>Trạng thái</th>
                					<th>Hành động</th>
                				</tr>
                				</thead>
                				<tbody>
                				<?php if (empty($vouchers)): ?>
                					<tr><td colspan="8" class="empty">Chưa có voucher nào phù hợp.</td></tr>
                				<?php else: ?>
                					<?php foreach ($vouchers as $voucher): ?>
                						<?php
                						$isExpired = $voucher['status'] === 'expired';
                						$statusLabel = $isExpired ? 'Hết hạn' : 'Đang hoạt động';
                						$statusClass = $isExpired ? 'status-expired' : 'status-active';
                						?>
                						<tr>
                							<td>
                								<strong><?php echo htmlspecialchars($voucher['code']); ?></strong>
                								<div class="muted">ID: #<?php echo (int) $voucher['voucher_id']; ?></div>
                							</td>
                							<td><?php echo $voucher['type'] === 'percent' ? 'Giảm %' : 'Giảm số tiền'; ?></td>
                							<td>
                								<?php if ($voucher['type'] === 'percent'): ?>
                									<?php echo rtrim(rtrim(number_format($voucher['discount'], 2, ',', '.'), '0'), ','); ?>%
                								<?php else: ?>
                									<?php echo number_format($voucher['discount'], 0, ',', '.'); ?> ₫
                								<?php endif; ?>
                							</td>
                							<td><?php echo number_format($voucher['used_count']); ?></td>
                							<td><?php echo (int) $voucher['usage_limit'] === 0 ? 'Không giới hạn' : number_format($voucher['usage_limit']); ?></td>
                							<td>
                								<?php echo $voucher['expiry_date'] ? date('d/m/Y', strtotime($voucher['expiry_date'])) : '<span class="muted">Không đặt</span>'; ?>
                							</td>
                							<td><span class="status <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></td>
                							<td class="actions">
                								<a class="btn-link" href="admin_promotion.php?action=edit&id=<?php echo (int) $voucher['voucher_id']; ?>">Sửa</a>
                								<form method="post" action="../../controller/controller_Admin/admin_promotion_controller.php" class="inline-form">
                									<input type="hidden" name="action" value="change_status">
                									<input type="hidden" name="voucher_id" value="<?php echo (int) $voucher['voucher_id']; ?>">
                									<input type="hidden" name="status" value="<?php echo $isExpired ? 'active' : 'expired'; ?>">
                									<button type="submit" class="btn-link <?php echo $isExpired ? 'success' : 'warning'; ?>" data-confirm="<?php echo $isExpired ? 'Kích hoạt lại voucher này?' : 'Đánh dấu voucher đã hết hạn?'; ?>">
                										<?php echo $isExpired ? 'Kích hoạt' : 'Đánh dấu hết hạn'; ?>
                									</button>
                								</form>
                								<a class="btn-link danger" href="../../controller/controller_Admin/admin_promotion_controller.php?action=delete&id=<?php echo (int) $voucher['voucher_id']; ?>" data-confirm="Xóa voucher này vĩnh viễn?">Xóa</a>
                							</td>
                						</tr>
                					<?php endforeach; ?>
                				<?php endif; ?>
                				</tbody>
                			</table>
                		</div>
                	</section>
        </div>
    </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../../Js/Admin/home.js"></script>
    <script src="../../Js/Admin/promotion.js"></script>
</body>
</html>