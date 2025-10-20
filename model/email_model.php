<?php
require_once 'database.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../controller/PHPMailer-master/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../controller/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../controller/PHPMailer-master/PHPMailer-master/src/SMTP.php';

/**
 * Gửi email bằng PHPMailer với SMTP
 * 
 * @param string $to Địa chỉ email người nhận
 * @param string $subject Tiêu đề email
 * @param string $message Nội dung email (HTML)
 * @param string|null $from_name Tên người gửi (tùy chọn)
 * @param string|null $reply_to Địa chỉ email trả lời (tùy chọn)
 * @return bool True nếu gửi thành công, False nếu thất bại
 */
function sendEmail($to, $subject, $message, $from_name = null, $reply_to = null) {
    $mail = new PHPMailer(true);
    
    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = getConfig('mail_host');
        $mail->SMTPAuth = true;
        $mail->Username = getConfig('mail_username');
        $mail->Password = getConfig('mail_password');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = getConfig('mail_port');
        $mail->CharSet = 'UTF-8';
        
        // Tắt SSL verification cho localhost (chỉ dùng trong development)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Người gửi và người nhận
        $mail->setFrom(
            getConfig('mail_username'), 
            $from_name ?? getConfig('site_name')
        );
        $mail->addAddress($to);
        
        // Reply-To (nếu có)
        if ($reply_to) {
            $mail->addReplyTo($reply_to);
        }
        
        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message); // Plain text version
        
        // Gửi email
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Log lỗi
        error_log("Lỗi gửi email đến $to: {$mail->ErrorInfo}");
        return false;
    }
}

// Gửi email xác nhận đăng ký
function sendVerificationEmail($email, $fullname, $verification_link) {
    $subject = "Xác nhận đăng ký tài khoản - " . getConfig('site_name');
    
    $message = "
    <html>
    <head>
        <title>Xác nhận đăng ký tài khoản</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Xác nhận đăng ký tài khoản</h1>
            </div>
            <div class='content'>
                <p>Xin chào $fullname,</p>
                <p>Cảm ơn bạn đã đăng ký tài khoản tại " . getConfig('site_name') . ".</p>
                <p>Vui lòng nhấn vào nút bên dưới để xác nhận địa chỉ email của bạn:</p>
                <p><a class='button' href='$verification_link'>Xác nhận Email</a></p>
                <p>Hoặc bạn có thể copy đường link sau vào trình duyệt:</p>
                <p>$verification_link</p>
                <p>Liên kết xác nhận này sẽ hết hạn sau 24 giờ.</p>
                <p>Nếu bạn không thực hiện yêu cầu đăng ký này, vui lòng bỏ qua email này.</p>
                <p>Trân trọng,<br>" . getConfig('site_name') . " Team</p>
            </div>
            <div class='footer'>
                <p>Email này được gửi tự động. Vui lòng không trả lời.</p>
                <p>&copy; " . date('Y') . " " . getConfig('site_name') . ". Đã đăng ký bản quyền.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

// Gửi email khôi phục mật khẩu
function sendResetPasswordEmail($email, $fullname, $reset_link) {
    $subject = "Khôi phục mật khẩu - " . getConfig('site_name');
    
    $message = "
    <html>
    <head>
        <title>Khôi phục mật khẩu</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .header { background-color: #3498db; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; }
            .button { display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Khôi phục mật khẩu</h1>
            </div>
            <div class='content'>
                <p>Xin chào $fullname,</p>
                <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại " . getConfig('site_name') . ".</p>
                <p>Nhấn vào nút bên dưới để đặt lại mật khẩu của bạn:</p>
                <p><a class='button' href='$reset_link'>Đặt lại mật khẩu</a></p>
                <p>Hoặc bạn có thể copy đường link sau vào trình duyệt:</p>
                <p>$reset_link</p>
                <p>Liên kết đặt lại mật khẩu này sẽ hết hạn sau 1 giờ.</p>
                <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi nếu bạn có bất kỳ câu hỏi nào.</p>
                <p>Trân trọng,<br>" . getConfig('site_name') . " Team</p>
            </div>
            <div class='footer'>
                <p>Email này được gửi tự động. Vui lòng không trả lời.</p>
                <p>&copy; " . date('Y') . " " . getConfig('site_name') . ". Đã đăng ký bản quyền.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

// Gửi email thông báo đơn hàng cho khách hàng
function sendOrderConfirmationEmail($email, $fullname, $order_id, $order_details) {
    $subject = "Xác nhận đơn hàng #$order_id - " . getConfig('site_name');
    
    $message = "
    <html>
    <head>
        <title>Xác nhận đơn hàng</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .header { background-color: #ff9800; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; }
            .button { display: inline-block; padding: 10px 20px; background-color: #ff9800; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Xác nhận đơn hàng</h1>
            </div>
            <div class='content'>
                <p>Xin chào $fullname,</p>
                <p>Cảm ơn bạn đã đặt hàng tại " . getConfig('site_name') . ".</p>
                <p>Đơn hàng của bạn đã được xác nhận. Dưới đây là thông tin chi tiết:</p>
                
                <h3>Thông tin đơn hàng #$order_id</h3>
                $order_details
                
                <p>Bạn có thể theo dõi đơn hàng của mình tại trang <a href='" . getConfig('site_url') . "/view/User/order_tracking.php?order_id=$order_id'>Theo dõi đơn hàng</a>.</p>
                <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.</p>
                <p>Trân trọng,<br>" . getConfig('site_name') . " Team</p>
            </div>
            <div class='footer'>
                <p>Email này được gửi tự động. Vui lòng không trả lời.</p>
                <p>&copy; " . date('Y') . " " . getConfig('site_name') . ". Đã đăng ký bản quyền.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

// ========================================
// ADMIN EMAIL MANAGEMENT FUNCTIONS
// ========================================

/**
 * Lấy danh sách tất cả users active (để gửi email)
 * 
 * @return array Danh sách users với user_id, fullname, email
 */
function getActiveUsers() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT user_id, fullname, email 
                            FROM users 
                            WHERE status = 'active' AND role = 'user'
                            ORDER BY fullname ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    $stmt->close();
    return $users;
}

/**
 * Gửi email hàng loạt cho nhiều users
 * 
 * @param array $user_emails Mảng các email cần gửi
 * @param string $subject Tiêu đề email
 * @param string $message Nội dung email (HTML)
 * @return array Kết quả gửi: ['success' => int, 'failed' => int, 'details' => array]
 */
function sendBulkEmail($user_emails, $subject, $message) {
    $results = [
        'success' => 0,
        'failed' => 0,
        'details' => []
    ];
    
    foreach ($user_emails as $email_data) {
        $email = $email_data['email'];
        $fullname = $email_data['fullname'] ?? '';
        
        // Thay thế variables trong message
        $personalized_message = str_replace(
            ['{fullname}', '{email}'],
            [$fullname, $email],
            $message
        );
        
        $sent = sendEmail($email, $subject, $personalized_message);
        
        if ($sent) {
            $results['success']++;
            $results['details'][] = [
                'email' => $email,
                'status' => 'success',
                'message' => 'Đã gửi thành công'
            ];
        } else {
            $results['failed']++;
            $results['details'][] = [
                'email' => $email,
                'status' => 'failed',
                'message' => 'Gửi thất bại'
            ];
        }
        
        // Delay nhỏ để tránh spam (100ms)
        usleep(100000);
    }
    
    return $results;
}

/**
 * Gửi email cho một user cụ thể
 * 
 * @param int $user_id ID của user
 * @param string $subject Tiêu đề email
 * @param string $message Nội dung email (HTML)
 * @return bool True nếu gửi thành công
 */
function sendEmailToUser($user_id, $subject, $message) {
    global $conn;
    
    // Lấy thông tin user
    $stmt = $conn->prepare("SELECT fullname, email 
                            FROM users 
                            WHERE user_id = ? AND status = 'active'");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        return false;
    }
    
    // Thay thế variables trong message
    $personalized_message = str_replace(
        ['{fullname}', '{email}'],
        [$user['fullname'], $user['email']],
        $message
    );
    
    return sendEmail($user['email'], $subject, $personalized_message);
}

/**
 * Tạo HTML cho email template - Thông báo chung
 * 
 * @param string $title Tiêu đề email
 * @param string $content Nội dung chính
 * @param string $button_text Text của button (optional)
 * @param string $button_link Link của button (optional)
 * @return string HTML đầy đủ
 */
function createGeneralEmailTemplate($title, $content, $button_text = null, $button_link = null) {
    $button_html = '';
    if ($button_text && $button_link) {
        $button_html = "<p style='text-align: center;'><a class='button' href='$button_link'>$button_text</a></p>";
    }
    
    $html = "
    <html>
    <head>
        <title>$title</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .header { background-color: #007bff; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .button { display: inline-block; padding: 12px 30px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; padding: 15px; background-color: #f1f1f1; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>$title</h1>
            </div>
            <div class='content'>
                <p>Xin chào {fullname},</p>
                $content
                $button_html
                <p>Trân trọng,<br>" . getConfig('site_name') . " Team</p>
            </div>
            <div class='footer'>
                <p>Email này được gửi tự động. Vui lòng không trả lời.</p>
                <p>&copy; " . date('Y') . " " . getConfig('site_name') . ". Đã đăng ký bản quyền.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return $html;
}

/**
 * Tạo HTML cho email template - Khuyến mãi
 * 
 * @param string $promo_title Tiêu đề khuyến mãi
 * @param string $promo_content Nội dung khuyến mãi
 * @param string $promo_code Mã giảm giá
 * @param string $discount Mức giảm giá
 * @param string $expiry_date Ngày hết hạn
 * @return string HTML đầy đủ
 */
function createPromoEmailTemplate($promo_title, $promo_content, $promo_code, $discount, $expiry_date) {
    $html = "
    <html>
    <head>
        <title>$promo_title</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .header { background-color: #ff6b6b; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; background-color: #fff5f5; }
            .promo-box { background-color: #ff6b6b; color: white; padding: 20px; text-align: center; border-radius: 10px; margin: 20px 0; }
            .promo-code { font-size: 28px; font-weight: bold; letter-spacing: 3px; background-color: white; color: #ff6b6b; padding: 15px; border-radius: 5px; display: inline-block; }
            .button { display: inline-block; padding: 12px 30px; background-color: #ff6b6b; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; padding: 15px; background-color: #f1f1f1; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 $promo_title</h1>
            </div>
            <div class='content'>
                <p>Xin chào {fullname},</p>
                <p>$promo_content</p>
                
                <div class='promo-box'>
                    <h2 style='margin: 0; color: white;'>GIẢM GIÁ $discount</h2>
                    <p style='margin: 10px 0;'>Sử dụng mã giảm giá:</p>
                    <div class='promo-code'>$promo_code</div>
                    <p style='margin-top: 15px; font-size: 14px;'>Có hiệu lực đến: $expiry_date</p>
                </div>
                
                <p style='text-align: center;'>
                    <a class='button' href='" . getConfig('site_url') . "/view/User/product_list.php'>Mua Sắm Ngay</a>
                </p>
                
                <p><strong>Lưu ý:</strong> Mã giảm giá chỉ áp dụng cho đơn hàng trên website.</p>
                
                <p>Trân trọng,<br>" . getConfig('site_name') . " Team</p>
            </div>
            <div class='footer'>
                <p>Email này được gửi tự động. Vui lòng không trả lời.</p>
                <p>&copy; " . date('Y') . " " . getConfig('site_name') . ". Đã đăng ký bản quyền.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return $html;
}
?>