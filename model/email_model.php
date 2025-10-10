<?php
require_once 'database.php';

/**
 * Gửi email bằng cách sử dụng hàm mail() của PHP
 * 
 * @param string $to Địa chỉ email người nhận
 * @param string $subject Tiêu đề email
 * @param string $message Nội dung email (HTML)
 * @param string|null $from_name Tên người gửi (tùy chọn)
 * @param string|null $reply_to Địa chỉ email trả lời (tùy chọn)
 * @return bool True nếu gửi thành công, False nếu thất bại
 */
function sendEmail($to, $subject, $message, $from_name = null, $reply_to = null) {
    // Headers
    $from_email = getConfig('mail_username') ?: 'no-reply@example.com';
    $from = ($from_name) ? "$from_name <$from_email>" : $from_email;
    
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: " . ($reply_to ?? $from_email) . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Gửi email
    $success = mail($to, $subject, $message, $headers);
    
    if (!$success) {
        error_log("Không thể gửi email đến: $to");
    }
    
    return $success;
}

// Chú thích: Hàm fallbackSendEmail đã được tích hợp vào hàm sendEmail

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
?>