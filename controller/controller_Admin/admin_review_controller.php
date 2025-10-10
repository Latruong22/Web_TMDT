<?php
require_once '../../model/database.php';
require_once '../../model/review_model.php';
session_start();
checkAccess('admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
	case 'update_status':
		$review_id = isset($_POST['review_id']) ? (int) $_POST['review_id'] : 0;
		$status = $_POST['status'] ?? '';

		if ($review_id <= 0 || !in_array($status, ['pending', 'approved', 'rejected'], true)) {
			header('Location: ../../view/Admin/admin_review.php?msg=invalid');
			exit();
		}

		if (updateReviewStatus($review_id, $status)) {
			header('Location: ../../view/Admin/admin_review.php?msg=status_done');
			exit();
		}

		header('Location: ../../view/Admin/admin_review.php?msg=error');
		exit();

	case 'delete':
		$review_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
		if ($review_id <= 0) {
			header('Location: ../../view/Admin/admin_review.php?msg=invalid');
			exit();
		}

		if (deleteReview($review_id)) {
			header('Location: ../../view/Admin/admin_review.php?msg=deleted');
			exit();
		}

		header('Location: ../../view/Admin/admin_review.php?msg=error');
		exit();

	default:
		header('Location: ../../view/Admin/admin_review.php');
		exit();
}
?>
