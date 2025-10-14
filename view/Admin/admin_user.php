<?php
session_start();
require_once '../../model/database.php';
checkAccess('admin');
require_once '../../model/user_model.php';

$filter_status = $_GET['status'] ?? 'all';
$filter_role = $_GET['role'] ?? 'all';
$filter_search = trim($_GET['search'] ?? '');
$filter_from = $_GET['from'] ?? '';
$filter_to = $_GET['to'] ?? '';

$filters = [
	'status' => $filter_status,
	'role' => $filter_role,
	'search' => $filter_search,
	'from' => $filter_from,
	'to' => $filter_to,
];

$users = getAdminUsers($filters);
$stats = getUserSummaryStats();
$currentUrl = $_SERVER['REQUEST_URI'] ?? '../../view/Admin/admin_user.php';
$msg = $_GET['msg'] ?? '';

$message_map = [
	'status_done' => 'Đã cập nhật trạng thái người dùng.',
	'role_done' => 'Đã cập nhật vai trò người dùng.',
	'reset_done' => 'Đã đặt lại mật khẩu người dùng, vui lòng gửi lại cho họ.',
	'invalid' => 'Dữ liệu gửi lên không hợp lệ.',
	'missing' => 'Không tìm thấy người dùng tương ứng.',
	'last_admin' => 'Không thể chuyển vai trò vì đây là quản trị viên cuối cùng.',
	'self' => 'Không thể thao tác này trên tài khoản của chính bạn.',
	'error' => 'Có lỗi xảy ra, vui lòng thử lại.',
];
$alert_text = $message_map[$msg] ?? '';

$recentReset = $_SESSION['last_reset_password'] ?? null;
if ($recentReset && (time() - $recentReset['timestamp'] > 300)) {
	unset($_SESSION['last_reset_password']);
	$recentReset = null;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Quản lý người dùng - Snowboard Admin</title>
	
	<!-- Bootstrap 5 -->
	<link href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
	<!-- Custom CSS -->
	<link rel="stylesheet" href="../../Css/Admin/admin_home.css">
	<link rel="stylesheet" href="../../Css/Admin/admin_user.css">
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
			<a href="admin_user.php" class="nav-link active">
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
				<h5 class="mb-0">Quản lý người dùng</h5>
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
					<div class="alert alert-<?php echo in_array($msg, ['status_done', 'role_done', 'reset_done'], true) ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
						<?php echo htmlspecialchars($alert_text); ?>
						<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
					</div>
				<?php endif; ?>

				<?php if ($recentReset && ($msg === 'reset_done')): ?>
					<div class="alert alert-info alert-dismissible fade show" role="alert">
						<strong><i class="fas fa-key me-2"></i>Mật khẩu tạm thời:</strong>
						Người dùng #<?php echo (int) $recentReset['user_id']; ?> →
						<code class="bg-light px-2 py-1"><?php echo htmlspecialchars($recentReset['password']); ?></code>
						<span class="text-muted">(Chỉ hiển thị trong 5 phút)</span>
						<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
					</div>
				<?php endif; ?>

				<div class="row g-3 mb-4">
					<div class="col-md-4 col-lg">
						<div class="card text-center">
							<div class="card-body">
								<h6 class="text-muted">Tổng tài khoản</h6>
								<h3 class="mb-0"><?php echo number_format($stats['total_users'] ?? 0); ?></h3>
							</div>
						</div>
					</div>
					<div class="col-md-4 col-lg">
						<div class="card text-center border-success">
							<div class="card-body">
								<h6 class="text-muted">Đang hoạt động</h6>
								<h3 class="mb-0 text-success"><?php echo number_format($stats['active_users'] ?? 0); ?></h3>
							</div>
						</div>
					</div>
					<div class="col-md-4 col-lg">
						<div class="card text-center border-warning">
							<div class="card-body">
								<h6 class="text-muted">Chờ kích hoạt</h6>
								<h3 class="mb-0 text-warning"><?php echo number_format($stats['pending_users'] ?? 0); ?></h3>
							</div>
						</div>
					</div>
					<div class="col-md-4 col-lg">
						<div class="card text-center border-danger">
							<div class="card-body">
								<h6 class="text-muted">Bị khóa</h6>
								<h3 class="mb-0 text-danger"><?php echo number_format($stats['locked_users'] ?? 0); ?></h3>
							</div>
						</div>
					</div>
					<div class="col-md-4 col-lg">
						<div class="card text-center border-primary">
							<div class="card-body">
								<h6 class="text-muted">Tài khoản admin</h6>
								<h3 class="mb-0 text-primary"><?php echo number_format($stats['admin_users'] ?? 0); ?></h3>
							</div>
						</div>
					</div>
				</div>

			<div class="filter-panel mb-4">
				<div class="filter-header" onclick="userFilterManager.toggleFilterPanel()">
					<div class="d-flex align-items-center">
						<i class="fas fa-filter me-2"></i>
						<h5 class="mb-0">Bộ lọc người dùng</h5>
						<span id="filterBadge" class="filter-badge ms-2">0</span>
					</div>
					<i class="fas fa-chevron-down" id="filterToggleIcon"></i>
				</div>
				<div class="filter-body" id="filterBody">
					<div id="activeFilters" class="active-filters mb-3"></div>
					<form method="get" id="userFilterForm" class="row g-3">
						<div class="col-md-2">
							<label class="form-label">
								<i class="fas fa-info-circle me-1"></i>
								Trạng thái
							</label>
							<select name="status" class="form-select filter-select">
								<option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>Tất cả</option>
								<option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
								<option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Chờ kích hoạt</option>
								<option value="locked" <?php echo $filter_status === 'locked' ? 'selected' : ''; ?>>Đang bị khóa</option>
							</select>
						</div>    
						<div class="col-md-2">
							<label class="form-label">
								<i class="fas fa-user-shield me-1"></i>
								Vai trò
							</label>
							<select name="role" class="form-select filter-select">
								<option value="all" <?php echo $filter_role === 'all' ? 'selected' : ''; ?>>Tất cả</option>
								<option value="user" <?php echo $filter_role === 'user' ? 'selected' : ''; ?>>Khách hàng</option>
								<option value="admin" <?php echo $filter_role === 'admin' ? 'selected' : ''; ?>>Quản trị</option>
							</select>
						</div>
						<div class="col-md-3">
							<label class="form-label">
								<i class="fas fa-search me-1"></i>
								Từ khóa
							</label>
							<div class="input-group">
								<input type="text" name="search" id="searchInput" class="form-control" placeholder="Tên, email hoặc SĐT" value="<?php echo htmlspecialchars($filter_search); ?>">
								<span class="input-group-text" id="searchSpinner" style="display: none;">
									<i class="fas fa-spinner fa-spin"></i>
								</span>
							</div>
						</div>
						<div class="col-md-2">
							<label class="form-label">
								<i class="fas fa-calendar-alt me-1"></i>
								Từ ngày
							</label>
							<input type="date" name="from" class="form-control filter-date" value="<?php echo htmlspecialchars($filter_from); ?>">
						</div>
						<div class="col-md-2">
							<label class="form-label">
								<i class="fas fa-calendar-alt me-1"></i>
								Đến ngày
							</label>
							<input type="date" name="to" class="form-control filter-date" value="<?php echo htmlspecialchars($filter_to); ?>">
						</div>
						<div class="col-md-1 d-flex align-items-end">
							<button type="button" class="btn btn-outline-secondary w-100" onclick="userFilterManager.clearAllFilters()" title="Xóa tất cả bộ lọc">
								<i class="fas fa-times"></i>
							</button>
						</div>
					</form>
					<div class="filter-loading" id="filterLoading" style="display: none;">
						<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Đang tải...</span>
						</div>
					</div>
				</div>
			</div>				<div class="card">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="mb-0">Danh sách người dùng</h5>
						<span class="badge bg-primary"><?php echo count($users); ?> tài khoản</span>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-hover">
				<thead>
				<tr>
					<th>Người dùng</th>
					<th>Liên hệ</th>
					<th>Vai trò</th>
					<th>Trạng thái</th>
					<th>Ngày tạo</th>
					<th></th>
				</tr>
				</thead>
				<tbody>
				<?php if (empty($users)): ?>
					<tr><td colspan="6" class="text-center empty">Không tìm thấy người dùng phù hợp.</td></tr>
				<?php else: ?>
					<?php foreach ($users as $user): ?>
						<?php
						$history = getUserLoginHistory($user['user_id'], 3);
						$isPendingStatus = isPendingStatusValue($user['status'] ?? null);
						$statusClass = $isPendingStatus ? 'pending' : strtolower($user['status'] ?? '');
						if ($statusClass === '') {
							$statusClass = 'pending';
						}
						?>
						<tr>
							<td>
								<strong><?php echo htmlspecialchars($user['fullname'] ?? '---'); ?></strong>
								<div class="muted">ID: #<?php echo (int) $user['user_id']; ?></div>
								<?php if (!empty($user['last_login'])): ?>
									<div class="muted">Đăng nhập cuối: <?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?></div>
								<?php else: ?>
									<div class="muted">Chưa ghi nhận đăng nhập</div>
								<?php endif; ?>
							</td>
							<td>
								<div><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
								<div><?php echo htmlspecialchars($user['phone'] ?? ''); ?></div>
							</td>
							<td>
								<span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'secondary'; ?>"><?php echo $user['role'] === 'admin' ? 'Quản trị' : 'Khách hàng'; ?></span>
							</td>
							<td>
								<span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : ($isPendingStatus ? 'warning' : 'danger'); ?>"><?php
									if ($user['status'] === 'active') {
										echo 'Đang hoạt động';
									} elseif ($isPendingStatus) {
										echo 'Chờ kích hoạt';
									} else {
										echo 'Đang khóa';
									}
								?></span>
							</td>
							<td><?php echo $user['created_at'] ? date('d/m/Y', strtotime($user['created_at'])) : '-'; ?></td>
							<td class="actions">
								<button type="button" class="btn btn-sm btn-info toggle-details" data-target="user-<?php echo (int) $user['user_id']; ?>">
									<i class="fas fa-eye me-1"></i>Chi tiết
								</button>
							</td>
						</tr>
						<tr id="user-<?php echo (int) $user['user_id']; ?>" class="user-details">
							<td colspan="6">
								<div class="details-grid">
									<div>
										<h3>Thông tin thêm</h3>
										<p><strong>Địa chỉ:</strong> <?php echo nl2br(htmlspecialchars($user['address'] ?? '')); ?></p>
										<p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
										<p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($user['phone'] ?? ''); ?></p>
										<p><strong>Đăng nhập gần đây:</strong></p>
										<?php if (empty($history)): ?>
											<p class="muted">Không có dữ liệu lịch sử.</p>
										<?php else: ?>
											<ul class="history-list">
												<?php foreach ($history as $item): ?>
													<li>
														<span><?php echo date('d/m/Y H:i', strtotime($item['login_time'])); ?></span>
														<span class="muted">IP: <?php echo htmlspecialchars($item['ip_address'] ?? '-'); ?></span>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</div>
									<div>
										<h3>Cập nhật quản trị</h3>
										<form method="post" action="../../controller/controller_Admin/admin_user_controller.php" class="mb-3">
											<input type="hidden" name="action" value="update_status">
											<input type="hidden" name="user_id" value="<?php echo (int) $user['user_id']; ?>">
											<input type="hidden" name="return_url" value="<?php echo htmlspecialchars($currentUrl); ?>">
											<label class="form-label">Trạng thái</label>
											<select name="status" class="form-select mb-2" required>
												<option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
												<option value="pending" <?php echo $isPendingStatus ? 'selected' : ''; ?>>Chờ kích hoạt</option>
												<option value="locked" <?php echo $user['status'] === 'locked' ? 'selected' : ''; ?>>Khóa</option>
											</select>
											<button type="submit" class="btn btn-primary btn-sm">
												<i class="fas fa-save me-1"></i>Lưu trạng thái
											</button>
										</form>

										<form method="post" action="../../controller/controller_Admin/admin_user_controller.php" class="mb-3">
											<input type="hidden" name="action" value="update_role">
											<input type="hidden" name="user_id" value="<?php echo (int) $user['user_id']; ?>">
											<input type="hidden" name="return_url" value="<?php echo htmlspecialchars($currentUrl); ?>">
											<label class="form-label">Vai trò</label>
											<select name="role" class="form-select mb-2" required>
												<option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Khách hàng</option>
												<option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Quản trị</option>
											</select>
											<button type="submit" class="btn btn-secondary btn-sm">
												<i class="fas fa-user-shield me-1"></i>Cập nhật quyền
											</button>
										</form>

										<form method="post" action="../../controller/controller_Admin/admin_user_controller.php">
											<input type="hidden" name="action" value="reset_password">
											<input type="hidden" name="user_id" value="<?php echo (int) $user['user_id']; ?>">
											<input type="hidden" name="return_url" value="<?php echo htmlspecialchars($currentUrl); ?>">
											<button type="submit" class="btn btn-danger btn-sm">
												<i class="fas fa-key me-1"></i>Đặt lại mật khẩu
											</button>
										</form>
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
	<script src="../../Js/Admin/user.js"></script>
</body>
</html>
