<?php
/**
 * Admin Email Controller
 * Xử lý gửi email từ admin đến users
 */

session_start();
require_once '../../model/database.php';
require_once '../../model/email_model.php';
require_once '../../model/auth_middleware.php';

// Kiểm tra quyền admin
requireAdmin();

// Đặt header JSON cho tất cả responses
header('Content-Type: application/json');

// Lấy action từ request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_users':
        getUsersList();
        break;
        
    case 'send_email':
        sendEmailAction();
        break;
        
    case 'preview_template':
        previewTemplate();
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
        break;
}

/**
 * Lấy danh sách tất cả users active
 */
function getUsersList() {
    try {
        $users = getActiveUsers();
        
        echo json_encode([
            'success' => true,
            'users' => $users,
            'total' => count($users)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách users: ' . $e->getMessage()
        ]);
    }
}

/**
 * Gửi email đến users
 * POST data:
 * - recipient_type: 'all' hoặc 'individual'
 * - user_ids: Array của user IDs (nếu individual)
 * - subject: Tiêu đề email
 * - message: Nội dung email (HTML)
 * - template_type: 'custom', 'general', 'promo' (optional)
 */
function sendEmailAction() {
    try {
        // Validate input
        $recipient_type = $_POST['recipient_type'] ?? '';
        $subject = trim($_POST['subject'] ?? '');
        $message = $_POST['message'] ?? '';
        
        if (empty($subject)) {
            throw new Exception('Tiêu đề email không được để trống');
        }
        
        if (empty($message)) {
            throw new Exception('Nội dung email không được để trống');
        }
        
        $results = [];
        
        if ($recipient_type === 'all') {
            // Gửi cho tất cả users
            $users = getActiveUsers();
            
            if (empty($users)) {
                throw new Exception('Không có user nào để gửi email');
            }
            
            $results = sendBulkEmail($users, $subject, $message);
            
        } elseif ($recipient_type === 'individual') {
            // Gửi cho users cụ thể
            $user_ids = $_POST['user_ids'] ?? [];
            
            if (empty($user_ids)) {
                throw new Exception('Vui lòng chọn ít nhất một user');
            }
            
            // Chuyển string thành array nếu cần
            if (is_string($user_ids)) {
                $user_ids = json_decode($user_ids, true);
            }
            
            $results = [
                'success' => 0,
                'failed' => 0,
                'details' => []
            ];
            
            foreach ($user_ids as $user_id) {
                $sent = sendEmailToUser($user_id, $subject, $message);
                
                if ($sent) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                }
            }
            
        } else {
            throw new Exception('Loại người nhận không hợp lệ');
        }
        
        // Log activity (optional - có thể thêm vào database)
        error_log("Admin {$_SESSION['user_id']} sent email: {$results['success']} success, {$results['failed']} failed");
        
        echo json_encode([
            'success' => true,
            'message' => "Đã gửi thành công {$results['success']} email" . 
                         ($results['failed'] > 0 ? ", {$results['failed']} email thất bại" : ""),
            'results' => $results
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Preview email template
 * GET params:
 * - template_type: 'general' hoặc 'promo'
 */
function previewTemplate() {
    try {
        $template_type = $_GET['template_type'] ?? '';
        
        if ($template_type === 'general') {
            $html = createGeneralEmailTemplate(
                'Tiêu đề email mẫu',
                '<p>Đây là nội dung email mẫu. Bạn có thể thay đổi nội dung này.</p><p>Sử dụng <strong>{fullname}</strong> để hiển thị tên người nhận.</p>',
                'Xem chi tiết',
                getConfig('site_url')
            );
        } elseif ($template_type === 'promo') {
            $html = createPromoEmailTemplate(
                'Khuyến mãi đặc biệt',
                'Chúng tôi có ưu đãi đặc biệt dành riêng cho bạn!',
                'SUMMER2025',
                '20%',
                '31/12/2025'
            );
        } else {
            throw new Exception('Loại template không hợp lệ');
        }
        
        echo json_encode([
            'success' => true,
            'html' => $html
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>
