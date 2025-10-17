<?php
session_start();
require_once '../../model/auth_middleware.php';
require_once '../../model/database.php';

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

checkSessionTimeout();

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'cancel_order':
        $order_id = intval($_POST['order_id'] ?? 0);
        $cancel_reason = trim($_POST['cancel_reason'] ?? '');
        
        // Validation
        if ($order_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Order ID không hợp lệ']);
            exit();
        }
        
        if (empty($cancel_reason)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn lý do hủy đơn']);
            exit();
        }
        
        // Check order belongs to user and get current status
        global $conn;
        $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền truy cập']);
            exit();
        }
        
        $order = $result->fetch_assoc();
        $stmt->close();
        
        // Only allow cancel if order status is pending
        if ($order['status'] !== 'pending') {
            $status_text = [
                'confirmed' => 'đã xác nhận',
                'shipping' => 'đang giao hàng',
                'delivered' => 'đã giao hàng',
                'cancelled' => 'đã bị hủy'
            ];
            $current_status = $status_text[$order['status']] ?? $order['status'];
            echo json_encode([
                'success' => false, 
                'message' => "Không thể hủy đơn hàng đã {$current_status}. Chỉ có thể hủy đơn hàng đang chờ xử lý."
            ]);
            exit();
        }
        
        // Update order status to cancelled and save reason
        $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', cancel_reason = ? WHERE order_id = ?");
        $stmt->bind_param("si", $cancel_reason, $order_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode([
                'success' => true, 
                'message' => 'Đã hủy đơn hàng thành công',
                'order_id' => $order_id
            ]);
        } else {
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật đơn hàng. Vui lòng thử lại']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
