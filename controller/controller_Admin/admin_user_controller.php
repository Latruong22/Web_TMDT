<?php
require_once '../../model/database.php';
require_once '../../model/user_model.php';

session_start();
checkAccess('admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$redirect = '../../view/Admin/admin_user.php';
$returnUrl = $_POST['return_url'] ?? $_GET['return_url'] ?? '';

if ($returnUrl) {
	$parsed = parse_url($returnUrl);
	if (empty($parsed['host']) && isset($parsed['path']) && str_starts_with($parsed['path'], '/Web_TMDT/')) {
		$redirect = $returnUrl;
	}
}

switch ($action) {
	case 'update_status':
		$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
		$status = $_POST['status'] ?? '';

		if (!$userId || !in_array($status, ['active', 'locked', 'pending'], true)) {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=invalid');
			exit();
		}

		if ($userId === (int) ($_SESSION['user_id'] ?? 0)) {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=self');
			exit();
		}

		if (!getUserById($userId)) {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=missing');
			exit();
		}

		if (updateUserStatusAdmin($userId, $status)) {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=status_done');
		} else {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=error');
		}
		exit();

	case 'update_role':
		$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
		$role = $_POST['role'] ?? '';

		if (!$userId || !in_array($role, ['user', 'admin'], true)) {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=invalid');
			exit();
		}

		$targetUser = getUserById($userId);
		if (!$targetUser) {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=missing');
			exit();
		}

		if ($userId === (int) ($_SESSION['user_id'] ?? 0) && $role !== 'admin') {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=self');
			exit();
		}

		if ($targetUser['role'] === 'admin' && $role !== 'admin') {
			$stats = getUserSummaryStats();
			if (($stats['admin_users'] ?? 0) <= 1) {
				header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=last_admin');
				exit();
			}
		}

		if (updateUserRoleAdmin($userId, $role)) {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=role_done');
		} else {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=error');
		}
		exit();

	case 'reset_password':
		$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

		if (!$userId) {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=invalid');
			exit();
		}

		if (!getUserById($userId)) {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=missing');
			exit();
		}

		if ($userId === (int) ($_SESSION['user_id'] ?? 0)) {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=self');
			exit();
		}

		$newPassword = resetUserPasswordAdmin($userId);
		if ($newPassword === false) {
			header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=error');
			exit();
		}

		$_SESSION['last_reset_password'] = [
			'user_id' => $userId,
			'password' => $newPassword,
			'timestamp' => time(),
		];

		header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=reset_done');
		exit();

	default:
		header('Location: ' . $redirect);
		exit();
}
?>
