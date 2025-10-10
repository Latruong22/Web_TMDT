<?php
require_once '../../model/database.php';
require_once '../../model/revenue_model.php';

session_start();
checkAccess('admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$redirect = '../../view/Admin/admin_revenue.php';

switch ($action) {
	case 'export_csv':
		$filters = [
			'range' => $_POST['range'] ?? 'last_30',
			'from' => $_POST['from'] ?? '',
			'to' => $_POST['to'] ?? '',
		];

		$filters = normalizeRevenueFilters($filters);
		$rows = getRevenueReportRows($filters);

		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename=revenue-report-' . $filters['from'] . '-to-' . $filters['to'] . '.csv');
		header('Pragma: no-cache');
		header('Expires: 0');

		$output = fopen('php://output', 'w');
		if ($output === false) {
			header('Location: ' . $redirect . '?msg=export_error');
			exit();
		}

		fputcsv($output, ['Mã đơn', 'Ngày đặt', 'Khách hàng', 'Email', 'Voucher', 'Trạng thái', 'Tổng tiền']);
		foreach ($rows as $row) {
			fputcsv($output, [
				$row['order_id'],
				$row['order_date'],
				$row['fullname'],
				$row['email'],
				$row['voucher_code'],
				$row['status'],
				$row['total'],
			]);
		}

		fclose($output);
		exit();

	default:
		header('Location: ' . $redirect);
		exit();
}
?>
