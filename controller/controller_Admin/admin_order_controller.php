<?php
require_once '../../model/database.php';
require_once '../../model/order_model.php';
session_start();
checkAccess('admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$allowedStatuses = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];

switch ($action) {
    case 'update_status':
        $order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
        $status = $_POST['status'] ?? '';
        $shipping_address = trim($_POST['shipping_address'] ?? '');
        $note = trim($_POST['note'] ?? '');
        $redirect = '../../view/Admin/admin_order.php';
        $return_url = $_POST['return_url'] ?? '';

        if ($return_url) {
            // Chỉ cho phép quay lại trong cùng ứng dụng để tránh open redirect
            $parsed = parse_url($return_url);
            if (empty($parsed['host']) && isset($parsed['path']) && str_starts_with($parsed['path'], '/Web_TMDT/')) {
                $redirect = $return_url;
            }
        }

        if (!$order_id || !in_array($status, $allowedStatuses, true)) {
            header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=invalid');
            exit();
        }

        // Giữ dữ liệu cũ nếu trường trống hoàn toàn
        $order = getOrderById($order_id);
        if (!$order) {
            header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=notfound');
            exit();
        }

        $shipping_address = $shipping_address !== '' ? $shipping_address : ($order['shipping_address'] ?? '');
        $note = $note !== '' ? $note : ($order['note'] ?? '');

        if (updateOrderStatus($order_id, $status, $shipping_address, $note)) {
            header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=updated');
        } else {
            header('Location: ' . $redirect . (str_contains($redirect, '?') ? '&' : '?') . 'msg=error');
        }
        exit();

    default:
        header('Location: ../../view/Admin/admin_order.php');
        exit();
}
?>
