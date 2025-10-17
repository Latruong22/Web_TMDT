<?php
session_start();
require_once '../../model/auth_middleware.php';
require_once '../../model/database.php';
require_once '../../model/review_model.php';

// Bắt buộc đăng nhập để submit review
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá sản phẩm']);
    exit();
}

checkSessionTimeout();

// Get action from POST or GET
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'submit_review') {
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    
    // Validation cơ bản
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
        exit();
    }
    
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Đánh giá phải từ 1-5 sao']);
        exit();
    }
    
    if (empty($content) || mb_strlen($content) < 10) {
        echo json_encode(['success' => false, 'message' => 'Nhận xét tối thiểu 10 ký tự']);
        exit();
    }
    
    if (mb_strlen($content) > 500) {
        echo json_encode(['success' => false, 'message' => 'Nhận xét tối đa 500 ký tự']);
        exit();
    }
    
    // Submit review qua model
    $result = submitReview($user_id, $product_id, $rating, $content);
    echo json_encode($result);
    exit();
}

if ($action === 'get_reviews') {
    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
        exit();
    }
    
    $reviews = getProductReviews($product_id, $limit, $offset);
    $rating = getProductRating($product_id);
    
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'rating' => $rating
    ]);
    exit();
}

if ($action === 'check_can_review') {
    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
    $user_id = $_SESSION['user_id'];
    
    if ($product_id <= 0) {
        echo json_encode([
            'success' => false, 
            'can_review' => false,
            'message' => 'Sản phẩm không hợp lệ'
        ]);
        exit();
    }
    
    $can_review = canUserReview($user_id, $product_id);
    
    if ($can_review) {
        echo json_encode([
            'success' => true, 
            'can_review' => true,
            'message' => 'Bạn có thể đánh giá sản phẩm này'
        ]);
    } else {
        // Check lý do không thể review
        global $conn;
        
        // Check đã mua chưa
        $sql = "SELECT COUNT(*) as count 
                FROM orders o
                JOIN order_details od ON o.order_id = od.order_id
                WHERE o.user_id = ? 
                    AND od.product_id = ?
                    AND o.status = 'delivered'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $user_id, $product_id);
        $stmt->execute();
        $purchased = $stmt->get_result()->fetch_assoc()['count'];
        $stmt->close();
        
        if ($purchased == 0) {
            $message = 'Bạn chưa mua sản phẩm này hoặc đơn hàng chưa được giao';
        } else {
            // Đã mua rồi, check đã review chưa
            $sql2 = "SELECT COUNT(*) as count FROM reviews WHERE user_id = ? AND product_id = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param('ii', $user_id, $product_id);
            $stmt2->execute();
            $reviewed = $stmt2->get_result()->fetch_assoc()['count'];
            $stmt2->close();
            
            if ($reviewed > 0) {
                $message = 'Bạn đã đánh giá sản phẩm này rồi';
            } else {
                $message = 'Bạn không thể đánh giá sản phẩm này';
            }
        }
        
        echo json_encode([
            'success' => true, 
            'can_review' => false,
            'message' => $message
        ]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
