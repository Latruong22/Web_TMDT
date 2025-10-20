<?php
// Tắt hiển thị lỗi HTML để không làm hỏng JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once '../../model/database.php';
require_once '../../model/product_model.php';
require_once '../../model/user_model.php';
require_once '../../model/auth_middleware.php';

// Bắt buộc phải đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tiếp tục']);
    exit();
}

// Kiểm tra action
$action = $_POST['action'] ?? '';

if ($action === 'create_order') {
    createOrder();
} else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}

/**
 * Tạo đơn hàng mới
 */
function createOrder() {
    global $conn;
    
    // Lấy thông tin user
    $user_id = $_SESSION['user_id'];
    
    // Lấy dữ liệu từ POST
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $voucher_code = trim($_POST['voucher_code'] ?? '');
    $cart_json = $_POST['cart'] ?? '[]';
    $total = floatval($_POST['total'] ?? 0);
    
    // Validate dữ liệu
    if (empty($fullname) || empty($email) || empty($phone) || empty($shipping_address)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
        return;
    }
    
    // Validate phone (10-11 số, bắt đầu bằng 0)
    if (!preg_match('/^0[0-9]{9,10}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ']);
        return;
    }
    
    // Parse cart
    $cart = json_decode($cart_json, true);
    if (empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        return;
    }
    
    // Validate stock và giá cho từng sản phẩm
    foreach ($cart as $item) {
        $product = getProductById($item['id']);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => "Sản phẩm {$item['name']} không tồn tại"]);
            return;
        }
        
        // Kiểm tra stock
        if ($product['stock'] < $item['quantity']) {
            echo json_encode(['success' => false, 'message' => "Sản phẩm {$product['name']} không đủ số lượng trong kho"]);
            return;
        }
        
        // Tính giá sau giảm từ database
        $db_price = floatval($product['price']);
        $discount_percent = floatval($product['manual_discount']);
        if ($discount_percent > 0) {
            $db_price = $db_price * (1 - $discount_percent / 100);
        }
        
        // Kiểm tra giá (tránh manipulation từ client)
        // Cho phép sai số 1đ để tránh lỗi làm tròn
        $cart_price = floatval($item['price']);
        if (abs($db_price - $cart_price) > 1) {
            echo json_encode([
                'success' => false, 
                'message' => "Giá sản phẩm {$product['name']} đã thay đổi. Vui lòng làm mới giỏ hàng"
            ]);
            return;
        }
    }
    
    // Tính tổng tiền từ cart (tính lại để đảm bảo chính xác)
    $total_amount = 0;
    foreach ($cart as $item) {
        $total_amount += floatval($item['price']) * intval($item['quantity']);
    }
    
    // Xử lý voucher nếu có
    $voucher_id = null;
    $discount_amount = 0;
    $final_amount = $total_amount;
    
    if (!empty($voucher_code)) {
        $voucher = getVoucherByCode($voucher_code);
        if ($voucher && $voucher['status'] === 'active') {
            $voucher_id = $voucher['voucher_id'];
            
            // Tính giảm giá
            if ($voucher['type'] === 'percent') { // FIX: Đổi từ 'percentage' thành 'percent' để khớp với database
                $discount_amount = $total_amount * (floatval($voucher['discount']) / 100);
            } else { // fixed
                $discount_amount = floatval($voucher['discount']);
            }
            
            $final_amount = $total_amount - $discount_amount;
            if ($final_amount < 0) {
                $final_amount = 0;
            }
        }
    }
    
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    try {
        // 1. Tạo order (dùng final_amount sau khi áp dụng voucher)
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status, voucher_id, shipping_address, note, order_date) 
                                VALUES (?, ?, 'pending', ?, ?, ?, NOW())");
        $stmt->bind_param('idiss', $user_id, $final_amount, $voucher_id, $shipping_address, $note);
        
        if (!$stmt->execute()) {
            throw new Exception('Không thể tạo đơn hàng');
        }
        
        $order_id = $conn->insert_id;
        $stmt->close();
        
        // 2. Thêm order details và cập nhật stock
        $stmt_detail = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price, reviewed) 
                                        VALUES (?, ?, ?, ?, 0)");
        $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
        
        foreach ($cart as $item) {
            // Thêm order detail
            $stmt_detail->bind_param('iiid', $order_id, $item['id'], $item['quantity'], $item['price']);
            if (!$stmt_detail->execute()) {
                throw new Exception('Không thể thêm chi tiết đơn hàng');
            }
            
            // Cập nhật stock
            $stmt_stock->bind_param('ii', $item['quantity'], $item['id']);
            if (!$stmt_stock->execute()) {
                throw new Exception('Không thể cập nhật tồn kho');
            }
        }
        
        $stmt_detail->close();
        $stmt_stock->close();
        
        // 3. Cập nhật usage_limit của voucher nếu có
        if ($voucher_id) {
            $stmt_voucher = $conn->prepare("UPDATE vouchers SET usage_limit = usage_limit - 1 WHERE voucher_id = ?");
            $stmt_voucher->bind_param('i', $voucher_id);
            $stmt_voucher->execute();
            $stmt_voucher->close();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Gửi email xác nhận đơn hàng (không làm crash nếu email lỗi)
        $email_sent = false;
        try {
            require_once '../../model/email_model.php';
            $order_details_html = generateOrderDetailsHTML($order_id, $cart, $total_amount, $discount_amount, $final_amount);
            $email_sent = sendOrderConfirmationEmail($email, $fullname, $order_id, $order_details_html);
        } catch (Exception $email_error) {
            // Log lỗi nhưng không làm thất bại đơn hàng
            error_log("Lỗi gửi email cho đơn hàng #$order_id: " . $email_error->getMessage());
        }
        
        // Trả về kết quả thành công
        $message = 'Đặt hàng thành công!';
        if ($email_sent) {
            $message .= ' Email xác nhận đã được gửi.';
        } else {
            $message .= ' Bạn có thể kiểm tra đơn hàng trong lịch sử đơn hàng.';
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'order_id' => $order_id
        ]);
        
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
}

/**
 * Lấy thông tin voucher theo code
 */
function getVoucherByCode($code) {
    global $conn;
    $stmt = $conn->prepare("SELECT voucher_id, code, discount, type, expiry_date, usage_limit, status 
                            FROM vouchers 
                            WHERE code = ? AND status = 'active' AND expiry_date >= CURDATE() AND usage_limit > 0");
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $voucher = $result->fetch_assoc();
    $stmt->close();
    return $voucher;
}

/**
 * Tạo HTML hiển thị chi tiết đơn hàng cho email
 */
function generateOrderDetailsHTML($order_id, $cart, $total_amount, $discount_amount, $final_amount) {
    $html = '<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif;">';
    $html .= '<thead>';
    $html .= '<tr style="background-color: #f8f9fa;">';
    $html .= '<th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Sản phẩm</th>';
    $html .= '<th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Size</th>';
    $html .= '<th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Số lượng</th>';
    $html .= '<th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Đơn giá</th>';
    $html .= '<th style="padding: 12px; text-align: right; border-bottom: 2px solid #dee2e6;">Thành tiền</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    
    foreach ($cart as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $size = isset($item['size']) ? htmlspecialchars($item['size']) : 'N/A';
        
        $html .= '<tr>';
        $html .= '<td style="padding: 12px; border-bottom: 1px solid #dee2e6;">' . htmlspecialchars($item['name']) . '</td>';
        $html .= '<td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">' . $size . '</td>';
        $html .= '<td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6;">' . $item['quantity'] . '</td>';
        $html .= '<td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">' . number_format($item['price'], 0, ',', '.') . ' ₫</td>';
        $html .= '<td style="padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6;">' . number_format($subtotal, 0, ',', '.') . ' ₫</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody>';
    $html .= '<tfoot>';
    $html .= '<tr>';
    $html .= '<td colspan="4" style="padding: 12px; text-align: right; font-weight: bold;">Tạm tính:</td>';
    $html .= '<td style="padding: 12px; text-align: right;">' . number_format($total_amount, 0, ',', '.') . ' ₫</td>';
    $html .= '</tr>';
    
    if ($discount_amount > 0) {
        $html .= '<tr>';
        $html .= '<td colspan="4" style="padding: 12px; text-align: right; font-weight: bold; color: #28a745;">Giảm giá:</td>';
        $html .= '<td style="padding: 12px; text-align: right; color: #28a745;">-' . number_format($discount_amount, 0, ',', '.') . ' ₫</td>';
        $html .= '</tr>';
    }
    
    $html .= '<tr style="background-color: #f8f9fa;">';
    $html .= '<td colspan="4" style="padding: 12px; text-align: right; font-weight: bold; font-size: 16px;">Tổng cộng:</td>';
    $html .= '<td style="padding: 12px; text-align: right; font-weight: bold; font-size: 16px; color: #dc3545;">' . number_format($final_amount, 0, ',', '.') . ' ₫</td>';
    $html .= '</tr>';
    $html .= '</tfoot>';
    $html .= '</table>';
    
    return $html;
}
?>