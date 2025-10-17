<?php
/**
 * ORDER CONTROLLER
 * Handles order-related AJAX requests
 */

session_start();
require_once '../../model/database.php';
require_once '../../model/order_model.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập'
    ]);
    exit();
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'get_order_items':
        getOrderItemsAction($user_id);
        break;
    
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
        break;
}

/**
 * Get Order Items for Review
 */
function getOrderItemsAction($user_id) {
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    
    if (!$order_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Mã đơn hàng không hợp lệ'
        ]);
        return;
    }
    
    // Verify order belongs to user
    $order = getOrderById($order_id);
    if (!$order || $order['user_id'] != $user_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không có quyền truy cập đơn hàng này'
        ]);
        return;
    }
    
    // Get order items
    $items = getOrderItems($order_id);
    
    if (empty($items)) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm trong đơn hàng'
        ]);
        return;
    }
    
    // Format items for response
    $formatted_items = array_map(function($item) {
        return [
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'product_image' => $item['product_image'] ?? '',
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ];
    }, $items);
    
    echo json_encode([
        'success' => true,
        'items' => $formatted_items,
        'order_id' => $order_id
    ]);
}
