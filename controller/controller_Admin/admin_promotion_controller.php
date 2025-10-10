<?php
require_once '../../model/database.php';
require_once '../../model/promotion_model.php';
session_start();
checkAccess('admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function sanitizeVoucherInput($value) {
	return trim(strip_tags($value));
}

function validateVoucherData($code, $discount, $type, $usage_limit) {
	if (strlen($code) < 3 || strlen($code) > 50) {
		return 'Mã khuyến mãi phải từ 3 đến 50 ký tự.';
	}

	if (!in_array($type, ['percent', 'fixed'], true)) {
		return 'Loại khuyến mãi không hợp lệ.';
	}

	if (!is_numeric($discount) || $discount <= 0) {
		return 'Giá trị khuyến mãi phải lớn hơn 0.';
	}

	if ($type === 'percent' && $discount > 100) {
		return 'Khuyến mãi phần trăm không được vượt quá 100%.';
	}

	if (!is_numeric($usage_limit) || $usage_limit < 0) {
		return 'Giới hạn sử dụng phải là số không âm.';
	}

	return null;
}

switch ($action) {
	case 'add':
		$code = strtoupper(sanitizeVoucherInput($_POST['code'] ?? ''));
		$discount = (float) ($_POST['discount'] ?? 0);
		$type = $_POST['type'] ?? 'percent';
		$expiry_date = sanitizeVoucherInput($_POST['expiry_date'] ?? '');
		$usage_limit = isset($_POST['usage_limit']) ? (int) $_POST['usage_limit'] : 0;
		$status = $_POST['status'] ?? 'active';

		if ($expiry_date === '') {
			$expiry_date = null;
		}

		$error = validateVoucherData($code, $discount, $type, $usage_limit);
		if ($error !== null) {
			header('Location: ../../view/Admin/admin_promotion.php?msg=' . urlencode($error));
			exit();
		}

		if (!in_array($status, ['active', 'expired'], true)) {
			$status = 'active';
		}

		if (voucherCodeExists($code)) {
			header('Location: ../../view/Admin/admin_promotion.php?msg=' . urlencode('Mã khuyến mãi đã tồn tại.'));
			exit();
		}

		if (createVoucher($code, $discount, $type, $expiry_date, $usage_limit, $status)) {
			header('Location: ../../view/Admin/admin_promotion.php?msg=created');
			exit();
		}

		header('Location: ../../view/Admin/admin_promotion.php?msg=error');
		exit();

	case 'update':
		$voucher_id = isset($_POST['voucher_id']) ? (int) $_POST['voucher_id'] : 0;
		$code = strtoupper(sanitizeVoucherInput($_POST['code'] ?? ''));
		$discount = (float) ($_POST['discount'] ?? 0);
		$type = $_POST['type'] ?? 'percent';
		$expiry_date = sanitizeVoucherInput($_POST['expiry_date'] ?? '');
		$usage_limit = isset($_POST['usage_limit']) ? (int) $_POST['usage_limit'] : 0;
		$status = $_POST['status'] ?? 'active';

		if ($voucher_id <= 0) {
			header('Location: ../../view/Admin/admin_promotion.php?msg=invalid');
			exit();
		}

		if ($expiry_date === '') {
			$expiry_date = null;
		}

		$error = validateVoucherData($code, $discount, $type, $usage_limit);
		if ($error !== null) {
			header('Location: ../../view/Admin/admin_promotion.php?action=edit&id=' . $voucher_id . '&msg=' . urlencode($error));
			exit();
		}

		if (!in_array($status, ['active', 'expired'], true)) {
			$status = 'active';
		}

		if (voucherCodeExists($code, $voucher_id)) {
			header('Location: ../../view/Admin/admin_promotion.php?action=edit&id=' . $voucher_id . '&msg=' . urlencode('Mã khuyến mãi đã tồn tại.'));
			exit();
		}

		if (updateVoucher($voucher_id, $code, $discount, $type, $expiry_date, $usage_limit, $status)) {
			header('Location: ../../view/Admin/admin_promotion.php?msg=updated');
			exit();
		}

		header('Location: ../../view/Admin/admin_promotion.php?action=edit&id=' . $voucher_id . '&msg=error');
		exit();

	case 'change_status':
		$voucher_id = isset($_POST['voucher_id']) ? (int) $_POST['voucher_id'] : 0;
		$status = $_POST['status'] ?? '';

		if ($voucher_id <= 0 || !in_array($status, ['active', 'expired'], true)) {
			header('Location: ../../view/Admin/admin_promotion.php?msg=invalid');
			exit();
		}

		if (changeVoucherStatus($voucher_id, $status)) {
			header('Location: ../../view/Admin/admin_promotion.php?msg=status_done');
			exit();
		}

		header('Location: ../../view/Admin/admin_promotion.php?msg=error');
		exit();

	case 'delete':
		$voucher_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
		if ($voucher_id <= 0) {
			header('Location: ../../view/Admin/admin_promotion.php?msg=invalid');
			exit();
		}

		if (deleteVoucher($voucher_id)) {
			header('Location: ../../view/Admin/admin_promotion.php?msg=deleted');
			exit();
		}

		header('Location: ../../view/Admin/admin_promotion.php?msg=cannot_delete');
		exit();

	default:
		header('Location: ../../view/Admin/admin_promotion.php');
		exit();
}
?>
