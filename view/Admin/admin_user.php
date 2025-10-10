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
	<title>Quản lý người dùng</title>
	<link rel="stylesheet" href="../../Css/Admin/admin_user.css">
</head>
<body>
<div class="admin-container">
	<header class="page-header">
		<div>
			<h1>Quản lý người dùng</h1>
			<p>Theo dõi tài khoản, cập nhật quyền và khóa/mở tài khoản.</p>
		</div>
		<a class="back-link" href="admin_home.php">← Quay lại bảng điều khiển</a>
	</header>

	<?php if ($alert_text): ?>
		<div class="alert <?php echo in_array($msg, ['status_done', 'role_done', 'reset_done'], true) ? 'success' : 'error'; ?>">
			<?php echo htmlspecialchars($alert_text); ?>
		</div>
	<?php endif; ?>

	<?php if ($recentReset && ($msg === 'reset_done')): ?>
		<div class="alert info">
			<strong>Mật khẩu tạm thời:</strong>
			Người dùng #<?php echo (int) $recentReset['user_id']; ?> →
			<code><?php echo htmlspecialchars($recentReset['password']); ?></code>
			<span class="muted">(Chỉ hiển thị trong 5 phút)</span>
		</div>
	<?php endif; ?>

	<section class="summary-grid">
		<article class="summary-card">
			<h3>Tổng tài khoản</h3>
			<p class="summary-value"><?php echo number_format($stats['total_users'] ?? 0); ?></p>
		</article>
		<article class="summary-card">
			<h3>Đang hoạt động</h3>
			<p class="summary-value positive"><?php echo number_format($stats['active_users'] ?? 0); ?></p>
		</article>
		<article class="summary-card">
			<h3>Chờ kích hoạt</h3>
			<p class="summary-value warning"><?php echo number_format($stats['pending_users'] ?? 0); ?></p>
		</article>
		<article class="summary-card">
			<h3>Bị khóa</h3>
			<p class="summary-value danger"><?php echo number_format($stats['locked_users'] ?? 0); ?></p>
		</article>
		<article class="summary-card">
			<h3>Tài khoản admin</h3>
			<p class="summary-value"><?php echo number_format($stats['admin_users'] ?? 0); ?></p>
		</article>
	</section>

	<section class="filter-section">
		<form method="get" class="filter-form">
			<label>
				Trạng thái
				<select name="status">
					<option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>Tất cả</option>
					<option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
					<option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Chờ kích hoạt</option>
					<option value="locked" <?php echo $filter_status === 'locked' ? 'selected' : ''; ?>>Đang bị khóa</option>
				</select>
			</label>    
			<label>
				Vai trò
				<select name="role">
					<option value="all" <?php echo $filter_role === 'all' ? 'selected' : ''; ?>>Tất cả</option>
					<option value="user" <?php echo $filter_role === 'user' ? 'selected' : ''; ?>>Khách hàng</option>
					<option value="admin" <?php echo $filter_role === 'admin' ? 'selected' : ''; ?>>Quản trị</option>
				</select>
			</label>
			<label>
				Từ khóa
				<input type="text" name="search" placeholder="Tên, email hoặc SĐT" value="<?php echo htmlspecialchars($filter_search); ?>">
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
				<a class="btn-secondary" href="admin_user.php">Làm mới</a>
			</div>
		</form>
	</section>

	<section class="table-section">
		<div class="section-header">
			<h2>Danh sách người dùng</h2>
			<span><?php echo count($users); ?> tài khoản</span>
		</div>
		<div class="table-responsive">
			<table>
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
					<tr><td colspan="6" class="empty">Không tìm thấy người dùng phù hợp.</td></tr>
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
								<span class="tag role-<?php echo htmlspecialchars($user['role']); ?>"><?php echo $user['role'] === 'admin' ? 'Quản trị' : 'Khách hàng'; ?></span>
							</td>
							<td>
								<span class="status status-<?php echo htmlspecialchars($statusClass); ?>"><?php
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
							<td class="actions"><button type="button" class="btn-link toggle-details" data-target="user-<?php echo (int) $user['user_id']; ?>">Chi tiết</button></td>
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
										<form method="post" action="../../controller/controller_Admin/admin_user_controller.php" class="inline-form">
											<input type="hidden" name="action" value="update_status">
											<input type="hidden" name="user_id" value="<?php echo (int) $user['user_id']; ?>">
											<input type="hidden" name="return_url" value="<?php echo htmlspecialchars($currentUrl); ?>">
											<label>Trạng thái
												<select name="status" required>
													<option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
													<option value="pending" <?php echo $isPendingStatus ? 'selected' : ''; ?>>Chờ kích hoạt</option>
													<option value="locked" <?php echo $user['status'] === 'locked' ? 'selected' : ''; ?>>Khóa</option>
												</select>
											</label>
											<button type="submit" class="btn-primary btn-small">Lưu trạng thái</button>
										</form>

										<form method="post" action="../../controller/controller_Admin/admin_user_controller.php" class="inline-form">
											<input type="hidden" name="action" value="update_role">
											<input type="hidden" name="user_id" value="<?php echo (int) $user['user_id']; ?>">
											<input type="hidden" name="return_url" value="<?php echo htmlspecialchars($currentUrl); ?>">
											<label>Vai trò
												<select name="role" required>
													<option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Khách hàng</option>
													<option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Quản trị</option>
												</select>
											</label>
											<button type="submit" class="btn-secondary btn-small">Cập nhật quyền</button>
										</form>

										<form method="post" action="../../controller/controller_Admin/admin_user_controller.php" class="inline-form">
											<input type="hidden" name="action" value="reset_password">
											<input type="hidden" name="user_id" value="<?php echo (int) $user['user_id']; ?>">
											<input type="hidden" name="return_url" value="<?php echo htmlspecialchars($currentUrl); ?>">
											<button type="submit" class="btn-danger btn-small">Đặt lại mật khẩu</button>
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
	</section>
</div>

<script src="../../Js/Admin/user.js"></script>
</body>
</html>
