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
?>
