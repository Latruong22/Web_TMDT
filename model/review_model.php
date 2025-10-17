<?php
require_once 'database.php';

function getReviewSummaryStats() {
	global $conn;
	$sql = "SELECT
				COUNT(*) AS total,
				SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_total,
				SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_total,
				SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_total,
				AVG(rating) AS avg_rating
			FROM reviews";
	$result = $conn->query($sql);
	$row = $result ? $result->fetch_assoc() : [];

	return [
		'total' => (int) ($row['total'] ?? 0),
		'pending' => (int) ($row['pending_total'] ?? 0),
		'approved' => (int) ($row['approved_total'] ?? 0),
		'rejected' => (int) ($row['rejected_total'] ?? 0),
		'avg_rating' => $row['avg_rating'] !== null ? round((float) $row['avg_rating'], 2) : null,
	];
}

function getReviewCountsByRating() {
	global $conn;
	$sql = "SELECT rating, COUNT(*) AS total FROM reviews GROUP BY rating";
	$result = $conn->query($sql);
	$counts = [];
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$counts[(int) $row['rating']] = (int) $row['total'];
		}
	}
	return $counts;
}

function getReviews(array $filters = []) {
	global $conn;

	$sql = "SELECT r.review_id, r.product_id, r.user_id, r.content, r.rating, r.status, r.created_at,
				   p.name AS product_name,
				   u.fullname AS user_name,
				   u.email AS user_email
			FROM reviews r
			LEFT JOIN products p ON r.product_id = p.product_id
			LEFT JOIN users u ON r.user_id = u.user_id
			WHERE 1 = 1";

	$types = '';
	$params = [];

	if (!empty($filters['status']) && $filters['status'] !== 'all') {
		$sql .= " AND r.status = ?";
		$types .= 's';
		$params[] = $filters['status'];
	}

	if (!empty($filters['rating']) && $filters['rating'] !== 'all') {
		$sql .= " AND r.rating = ?";
		$types .= 'i';
		$params[] = (int) $filters['rating'];
	}

	if (!empty($filters['search'])) {
		$sql .= " AND (p.name LIKE ? OR u.fullname LIKE ? OR u.email LIKE ?)";
		$types .= 'sss';
		$searchTerm = '%' . $filters['search'] . '%';
		$params[] = $searchTerm;
		$params[] = $searchTerm;
		$params[] = $searchTerm;
	}

	if (!empty($filters['from'])) {
		$sql .= " AND DATE(r.created_at) >= ?";
		$types .= 's';
		$params[] = $filters['from'];
	}

	if (!empty($filters['to'])) {
		$sql .= " AND DATE(r.created_at) <= ?";
		$types .= 's';
		$params[] = $filters['to'];
	}

	$sql .= " ORDER BY r.created_at DESC";

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		return [];
	}

	if (!empty($params)) {
		$stmt->bind_param($types, ...$params);
	}

	$stmt->execute();
	$result = $stmt->get_result();
	$reviews = [];
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$reviews[] = $row;
		}
	}
	$stmt->close();
	return $reviews;
}

function getReviewById($review_id) {
	global $conn;
	$stmt = $conn->prepare("SELECT review_id, product_id, user_id, content, rating, status FROM reviews WHERE review_id = ?");
	if (!$stmt) {
		return null;
	}
	$stmt->bind_param('i', $review_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$review = $result ? $result->fetch_assoc() : null;
	$stmt->close();
	return $review;
}

function updateReviewStatus($review_id, $status) {
	global $conn;
	if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
		return false;
	}
	$stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE review_id = ?");
	if (!$stmt) {
		return false;
	}
	$stmt->bind_param('si', $status, $review_id);
	$result = $stmt->execute();
	$stmt->close();
	return $result;
}

function deleteReview($review_id) {
	global $conn;
	$stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
	if (!$stmt) {
		return false;
	}
	$stmt->bind_param('i', $review_id);
	$result = $stmt->execute();
	$stmt->close();
	return $result;
}

// ========== USER FUNCTIONS ==========

/**
 * Kiểm tra user đã mua sản phẩm chưa (chỉ order delivered mới được review)
 */
function canUserReview($user_id, $product_id) {
	global $conn;
	
	// Check user đã mua sản phẩm và order đã delivered
	$sql = "SELECT COUNT(*) as count 
			FROM orders o
			JOIN order_details od ON o.order_id = od.order_id
			WHERE o.user_id = ? 
				AND od.product_id = ?
				AND o.status = 'delivered'";
	
	$stmt = $conn->prepare($sql);
	if (!$stmt) return false;
	
	$stmt->bind_param('ii', $user_id, $product_id);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	
	if ($result['count'] == 0) {
		return false; // Chưa mua hoặc order chưa delivered
	}
	
	// Check đã review chưa
	$check_sql = "SELECT COUNT(*) as count 
				  FROM reviews 
				  WHERE user_id = ? AND product_id = ?";
	
	$stmt2 = $conn->prepare($check_sql);
	if (!$stmt2) return false;
	
	$stmt2->bind_param('ii', $user_id, $product_id);
	$stmt2->execute();
	$existing = $stmt2->get_result()->fetch_assoc();
	$stmt2->close();
	
	if ($existing['count'] > 0) {
		return false; // Đã review rồi
	}
	
	return true; // Có thể review
}

/**
 * Submit review từ user
 */
function submitReview($user_id, $product_id, $rating, $content) {
	global $conn;
	
	// Validate rating
	if ($rating < 1 || $rating > 5) {
		return ['success' => false, 'message' => 'Đánh giá phải từ 1-5 sao'];
	}
	
	// Validate content
	$content = trim($content);
	if (empty($content) || mb_strlen($content) < 10) {
		return ['success' => false, 'message' => 'Nhận xét tối thiểu 10 ký tự'];
	}
	
	// Check quyền review
	if (!canUserReview($user_id, $product_id)) {
		return ['success' => false, 'message' => 'Bạn chưa mua sản phẩm này hoặc đã đánh giá rồi'];
	}
	
	// Insert review (status = pending để admin duyệt)
	$sql = "INSERT INTO reviews (user_id, product_id, rating, content, status, created_at) 
			VALUES (?, ?, ?, ?, 'pending', NOW())";
	
	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		return ['success' => false, 'message' => 'Lỗi hệ thống'];
	}
	
	$stmt->bind_param('iiis', $user_id, $product_id, $rating, $content);
	
	if ($stmt->execute()) {
		$stmt->close();
		return ['success' => true, 'message' => 'Đánh giá đã được gửi, chờ duyệt từ admin'];
	}
	
	$stmt->close();
	return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
}

/**
 * Lấy reviews đã approved cho 1 sản phẩm
 */
function getProductReviews($product_id, $limit = 10, $offset = 0) {
	global $conn;
	
	$sql = "SELECT r.*, u.fullname, u.email,
				   DATE_FORMAT(r.created_at, '%d/%m/%Y %H:%i') as formatted_date
			FROM reviews r
			JOIN users u ON r.user_id = u.user_id
			WHERE r.product_id = ? AND r.status = 'approved'
			ORDER BY r.created_at DESC
			LIMIT ? OFFSET ?";
	
	$stmt = $conn->prepare($sql);
	if (!$stmt) return [];
	
	$stmt->bind_param('iii', $product_id, $limit, $offset);
	$stmt->execute();
	$result = $stmt->get_result();
	
	$reviews = [];
	while ($row = $result->fetch_assoc()) {
		$reviews[] = $row;
	}
	
	$stmt->close();
	return $reviews;
}

/**
 * Lấy rating trung bình và tổng reviews của sản phẩm
 */
function getProductRating($product_id) {
	global $conn;
	
	$sql = "SELECT 
				AVG(rating) as avg_rating, 
				COUNT(*) as total_reviews,
				SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as star_5,
				SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as star_4,
				SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as star_3,
				SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as star_2,
				SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as star_1
			FROM reviews
			WHERE product_id = ? AND status = 'approved'";
	
	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		return [
			'average' => 0,
			'total' => 0,
			'stars' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]
		];
	}
	
	$stmt->bind_param('i', $product_id);
	$stmt->execute();
	$result = $stmt->get_result()->fetch_assoc();
	$stmt->close();
	
	return [
		'average' => $result['avg_rating'] ? round($result['avg_rating'], 1) : 0,
		'total' => (int) $result['total_reviews'],
		'stars' => [
			5 => (int) $result['star_5'],
			4 => (int) $result['star_4'],
			3 => (int) $result['star_3'],
			2 => (int) $result['star_2'],
			1 => (int) $result['star_1']
		]
	];
}

/**
 * Lấy tất cả reviews của 1 user
 */
function getUserReviews($user_id) {
	global $conn;
	
	$sql = "SELECT r.*, p.name as product_name, p.image,
				   DATE_FORMAT(r.created_at, '%d/%m/%Y %H:%i') as formatted_date
			FROM reviews r
			JOIN products p ON r.product_id = p.product_id
			WHERE r.user_id = ?
			ORDER BY r.created_at DESC";
	
	$stmt = $conn->prepare($sql);
	if (!$stmt) return [];
	
	$stmt->bind_param('i', $user_id);
	$stmt->execute();
	$result = $stmt->get_result();
	
	$reviews = [];
	while ($row = $result->fetch_assoc()) {
		$reviews[] = $row;
	}
	
	$stmt->close();
	return $reviews;
}
?>
