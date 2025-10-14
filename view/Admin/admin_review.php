<?php
session_start();
require_once '../../model/database.php';
checkAccess('admin');
require_once '../../model/review_model.php';

$filter_status = $_GET['status'] ?? 'all';
$filter_rating = $_GET['rating'] ?? 'all';
$filter_search = trim($_GET['search'] ?? '');
$filter_from = $_GET['from'] ?? '';
$filter_to = $_GET['to'] ?? '';

$filters = [
	'status' => $filter_status,
	'rating' => $filter_rating,
	'search' => $filter_search,
	'from' => $filter_from,
	'to' => $filter_to,
];

$reviews = getReviews($filters);
$stats = getReviewSummaryStats();
$ratingStats = getReviewCountsByRating();

$msg = $_GET['msg'] ?? '';
$message_map = [
	'status_done' => 'Đã cập nhật trạng thái đánh giá.',
	'deleted' => 'Đã xóa đánh giá khỏi hệ thống.',
	'invalid' => 'Dữ liệu gửi lên không hợp lệ.',
	'error' => 'Có lỗi xảy ra. Vui lòng thử lại.',
];
$alert_text = $message_map[$msg] ?? '';

$statuses = [
	'all' => 'Tất cả',
	'pending' => 'Chờ duyệt',
	'approved' => 'Đã duyệt',
	'rejected' => 'Bị từ chối',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá - Snowboard Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../Css/Admin/admin_home.css">
    <link rel="stylesheet" href="../../Css/Admin/admin_review.css">
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
            <a href="admin_review.php" class="nav-link active">
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
                <h5 class="mb-0">Quản lý đánh giá</h5>
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
                		<div class="alert <?php echo in_array($msg, ['status_done', 'deleted'], true) ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($alert_text); ?></div>
                	<?php endif; ?>

                	<section class="summary-grid">
                		<article class="summary-card">
                			<h3>Tổng đánh giá</h3>
                			<p class="summary-value"><?php echo number_format($stats['total']); ?></p>
                		</article>
                		<article class="summary-card">
                			<h3>Chờ duyệt</h3>
                			<p class="summary-value warning"><?php echo number_format($stats['pending']); ?></p>
                		</article>
                		<article class="summary-card">
                			<h3>Đã duyệt</h3>
                			<p class="summary-value positive"><?php echo number_format($stats['approved']); ?></p>
                		</article>
                		<article class="summary-card">
                			<h3>Bị từ chối</h3>
                			<p class="summary-value danger"><?php echo number_format($stats['rejected']); ?></p>
                		</article>
                		<article class="summary-card">
                			<h3>Điểm trung bình</h3>
                			<p class="summary-value"><?php echo $stats['avg_rating'] !== null ? number_format($stats['avg_rating'], 2) : '—'; ?></p>
                		</article>
                	</section>

                	<section class="rating-breakdown">
                		<?php for ($i = 5; $i >= 1; $i--): ?>
                			<div class="rating-item">
                				<span class="stars" aria-hidden="true"><?php echo str_repeat('★', $i) . str_repeat('☆', 5 - $i); ?></span>
                				<span><?php echo $i; ?> sao</span>
                				<span class="count"><?php echo number_format($ratingStats[$i] ?? 0); ?></span>
                			</div>
                		<?php endfor; ?>
                	</section>

            	<!-- Modern Filter Panel -->
            	<div class="filter-panel mb-4">
            		<div class="filter-header" onclick="reviewFilterManager.toggleFilterPanel()">
            			<div class="d-flex align-items-center">
            				<i class="fas fa-filter me-2"></i>
            				<h5 class="mb-0">Bộ lọc đánh giá</h5>
            				<span id="filterBadge" class="filter-badge ms-2">0</span>
            			</div>
            			<i class="fas fa-chevron-down" id="filterToggleIcon"></i>
            		</div>
            		<div class="filter-body" id="filterBody">
            			<div id="activeFilters" class="active-filters mb-3"></div>
            			<form method="get" id="reviewFilterForm" class="row g-3">
            				<div class="col-md-2">
            					<label class="form-label">
            						<i class="fas fa-info-circle me-1"></i>
            						Trạng thái
            					</label>
            					<select name="status" class="form-select filter-select">
            						<?php foreach ($statuses as $value => $label): ?>
            							<option value="<?php echo $value; ?>" <?php echo $filter_status === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
            						<?php endforeach; ?>
            					</select>
            				</div>
            				<div class="col-md-2">
            					<label class="form-label">
            						<i class="fas fa-star me-1"></i>
            						Đánh giá
            					</label>
            					<select name="rating" class="form-select filter-select">
            						<option value="all" <?php echo $filter_rating === 'all' ? 'selected' : ''; ?>>Tất cả</option>
            						<?php for ($i = 5; $i >= 1; $i--): ?>
            							<option value="<?php echo $i; ?>" <?php echo (string) $i === (string) $filter_rating ? 'selected' : ''; ?>><?php echo $i; ?> sao</option>
            						<?php endfor; ?>
            					</select>
            				</div>
            				<div class="col-md-3">
            					<label class="form-label">
            						<i class="fas fa-search me-1"></i>
            						Từ khóa
            					</label>
            					<div class="input-group">
            						<input type="text" name="search" id="searchInput" class="form-control" placeholder="Tên sản phẩm hoặc khách hàng..." value="<?php echo htmlspecialchars($filter_search); ?>">
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
            					<button type="button" class="btn btn-outline-secondary w-100" onclick="reviewFilterManager.clearAllFilters()" title="Xóa tất cả bộ lọc">
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
            	</div>                	<section class="table-section">
                		<div class="section-header">
                			<h2>Danh sách đánh giá</h2>
                			<span><?php echo count($reviews); ?> đánh giá</span>
                		</div>
                		<div class="table-responsive">
                			<table>
                				<thead>
                				<tr>
                					<th>Sản phẩm</th>
                					<th>Khách hàng</th>
                					<th>Đánh giá</th>
                					<th>Nội dung</th>
                					<th>Trạng thái</th>
                					<th>Hành động</th>
                				</tr>
                				</thead>
                				<tbody>
                				<?php if (empty($reviews)): ?>
                					<tr><td colspan="6" class="empty">Không có đánh giá phù hợp.</td></tr>
                				<?php else: ?>
                					<?php foreach ($reviews as $review): ?>
                						<?php
                						$statusClass = 'status-' . $review['status'];
                						$statusLabel = $statuses[$review['status']] ?? $review['status'];
                						?>
                						<tr>
                							<td>
                								<strong><?php echo htmlspecialchars($review['product_name'] ?? 'Sản phẩm đã xóa'); ?></strong>
                								<div class="muted">ID: #<?php echo (int) $review['product_id']; ?></div>
                							</td>
                							<td>
                								<div><?php echo htmlspecialchars($review['user_name'] ?? 'Khách'); ?></div>
                								<div class="muted"><?php echo htmlspecialchars($review['user_email'] ?? '-'); ?></div>
                							</td>
                							<td>
                								<span class="stars" aria-hidden="true"><?php echo str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']); ?></span>
                								<div><?php echo (int) $review['rating']; ?>/5</div>
                								<div class="muted"><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></div>
                							</td>
                							<td class="content-cell"><?php echo nl2br(htmlspecialchars($review['content'] ?? '')); ?></td>
                							<td><span class="status <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></td>
                							<td class="actions">
                								<form method="post" action="../../controller/controller_Admin/admin_review_controller.php" class="inline-form">
                									<input type="hidden" name="action" value="update_status">
                									<input type="hidden" name="review_id" value="<?php echo (int) $review['review_id']; ?>">
                									<select name="status" required>
                										<option value="pending" <?php echo $review['status'] === 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                										<option value="approved" <?php echo $review['status'] === 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                										<option value="rejected" <?php echo $review['status'] === 'rejected' ? 'selected' : ''; ?>>Từ chối</option>
                									</select>
                									<button type="submit" class="btn-primary btn-small">Lưu</button>
                								</form>
                								<a class="btn-link danger" href="../../controller/controller_Admin/admin_review_controller.php?action=delete&id=<?php echo (int) $review['review_id']; ?>" data-confirm="Xóa đánh giá này?">Xóa</a>
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
    <script src="../../Js/Admin/review.js"></script>
</body>
</html>