<?php
session_start();
require_once '../../model/database.php';

// Không bắt buộc login để check voucher (có thể check trước khi login)
// Nhưng tốt nhất nên login để tránh abuse

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'validate_voucher') {
    validateVoucher();
} else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}

/**
 * Validate voucher code
 */
function validateVoucher() {
    global $conn;
    
    $code = trim($_POST['code'] ?? '');
    
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá']);
        return;
    }
    
    // Tìm voucher trong database
    $stmt = $conn->prepare("SELECT voucher_id, code, discount, type, expiry_date, usage_limit, status 
                            FROM vouchers 
                            WHERE code = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        return;
    }
    
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $voucher = $result->fetch_assoc();
    $stmt->close();
    
    // Kiểm tra voucher có tồn tại
    if (!$voucher) {
        echo json_encode(['success' => false, 'message' => 'Mã giảm giá không tồn tại']);
        return;
    }
    
    // Kiểm tra status
    if ($voucher['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Mã giảm giá đã hết hiệu lực']);
        return;
    }
    
    // Kiểm tra expiry date
    $today = date('Y-m-d');
    if ($voucher['expiry_date'] < $today) {
        echo json_encode(['success' => false, 'message' => 'Mã giảm giá đã hết hạn']);
        return;
    }
    
    // Kiểm tra usage limit
    if ($voucher['usage_limit'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng']);
        return;
    }
    
    // Voucher hợp lệ
    echo json_encode([
        'success' => true,
        'message' => 'Mã giảm giá hợp lệ',
        'voucher_id' => $voucher['voucher_id'],
        'code' => $voucher['code'],
        'discount' => floatval($voucher['discount']),
        'type' => $voucher['type']
    ]);
}
?>