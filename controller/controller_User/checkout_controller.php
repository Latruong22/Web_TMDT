<?php
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
                'message' => "Giá sản phẩm {$product['name']} đã thay đổi. Vui lòng làm mới giỏ hàng",
                'debug' => [
                    'db_price' => $db_price,
                    'cart_price' => $cart_price,
                    'difference' => abs($db_price - $cart_price)
                ]
            ]);
            return;
        }
    }
    
    // Xử lý voucher nếu có
    $voucher_id = null;
    if (!empty($voucher_code)) {
        $voucher = getVoucherByCode($voucher_code);
        if ($voucher && $voucher['status'] === 'active') {
            $voucher_id = $voucher['voucher_id'];
        }
    }
    
    // Bắt đầu transaction
    $conn->begin_transaction();
    
    try {
        // 1. Tạo order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status, voucher_id, shipping_address, note, order_date) 
                                VALUES (?, ?, 'pending', ?, ?, ?, NOW())");
        $stmt->bind_param('idiss', $user_id, $total, $voucher_id, $shipping_address, $note);
        
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
        
        // TODO: Gửi email xác nhận (sẽ tích hợp sau)
        // sendOrderConfirmationEmail($email, $fullname, $order_id);
        
        // Trả về kết quả thành công
        echo json_encode([
            'success' => true,
            'message' => 'Đặt hàng thành công',
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
?>