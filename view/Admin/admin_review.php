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
	<title>Quản lý đánh giá</title>
	<link rel="stylesheet" href="../../Css/Admin/admin_review.css">
</head>
<body>
<div class="admin-container">
	<header class="page-header">
		<div>
			<h1>Quản lý đánh giá</h1>
			<p>Duyệt và kiểm soát nội dung đánh giá từ khách hàng.</p>
		</div>
		<a class="back-link" href="admin_home.php">← Quay lại bảng điều khiển</a>
	</header>

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
				Đánh giá
				<select name="rating">
					<option value="all" <?php echo $filter_rating === 'all' ? 'selected' : ''; ?>>Tất cả</option>
					<?php for ($i = 5; $i >= 1; $i--): ?>
						<option value="<?php echo $i; ?>" <?php echo (string) $i === (string) $filter_rating ? 'selected' : ''; ?>><?php echo $i; ?> sao</option>
					<?php endfor; ?>
				</select>
			</label>
			<label>
				Từ khóa
				<input type="text" name="search" placeholder="Tên sản phẩm hoặc khách hàng" value="<?php echo htmlspecialchars($filter_search); ?>">
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
				<a class="btn-secondary" href="admin_review.php">Làm mới</a>
			</div>
		</form>
	</section>

	<section class="table-section">
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

<script src="../../Js/Admin/review.js"></script>
</body>
</html>
