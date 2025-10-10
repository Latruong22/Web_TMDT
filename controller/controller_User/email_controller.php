<?php
require_once '../../model/user_model.php';
require_once '../../model/email_model.php';
session_start();

// Xác nhận email
if (isset($_GET['action']) && $_GET['action'] == 'verify') {
    $code = $_GET['code'] ?? '';
    if (empty($code)) {
        header('Location: ../../view/User/login.php?msg=invalid_code');
        exit();
    }
    
    if (verifyEmail($code)) {
        header('Location: ../../view/User/login.php?msg=verified');
        exit();
    } else {
        header('Location: ../../view/User/login.php?msg=verify_fail');
        exit();
    }
}

// Gửi lại email xác nhận
if (isset($_POST['resend_verification'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Kiểm tra email có tồn tại và chưa kích hoạt
    $user = getUserByEmail($email);
    if ($user && $user['status'] == 'pending') {
        $verification_code = md5(time() . $email);
        saveVerificationCode($email, $verification_code);
        $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/Web_TMDT/controller/controller_User/email_controller.php?action=verify&code=" . $verification_code;
        sendVerificationEmail($email, $user['fullname'], $verification_link);
        
        header('Location: ../../view/User/login.php?msg=resent');
        exit();
    } else {
        header('Location: ../../view/User/login.php?msg=email_not_found');
        exit();
    }
}

// Quên mật khẩu - yêu cầu đặt lại
if (isset($_POST['forgot_password'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Kiểm tra email có tồn tại
    $user = getUserByEmail($email);
    if ($user) {
        $reset_token = generateResetToken($email);
        if ($reset_token) {
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/Web_TMDT/view/User/reset_password.php?token=" . $reset_token;
            sendResetPasswordEmail($email, $user['fullname'], $reset_link);
            
            header('Location: ../../view/User/forgot_password.php?msg=sent');
            exit();
        }
    }
    
    // Luôn trả về thành công kể cả khi email không tồn tại (bảo mật)
    header('Location: ../../view/User/forgot_password.php?msg=sent');
    exit();
}

// Đặt lại mật khẩu
if (isset($_POST['reset_password'])) {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Kiểm tra mật khẩu trùng khớp
    if ($password !== $confirm_password) {
        header('Location: ../../view/User/reset_password.php?token=' . $token . '&msg=mismatch');
        exit();
    }
    
    // Kiểm tra mật khẩu mạnh
    $password_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    if (!preg_match($password_regex, $password)) {
        header('Location: ../../view/User/reset_password.php?token=' . $token . '&msg=weak_password');
        exit();
    }
    
    // Đặt lại mật khẩu
    if (resetPassword($token, $password)) {
        header('Location: ../../view/User/login.php?msg=reset_success');
        exit();
    } else {
        header('Location: ../../view/User/reset_password.php?token=' . $token . '&msg=expired');
        exit();
    }
}

// Liên hệ qua email
if (isset($_POST['contact'])) {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));
    
    $full_message = "
    <h3>Thông tin liên hệ:</h3>
    <p><strong>Tên:</strong> $name</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Tiêu đề:</strong> $subject</p>
    <p><strong>Nội dung:</strong><br>$message</p>
    ";
    
    $admin_email = getConfig('admin_email');
    if (sendEmail($admin_email, "Liên hệ từ website: " . $subject, $full_message, "Website Contact", $email)) {
        header('Location: ../../view/User/contact.php?msg=sent');
    } else {
        header('Location: ../../view/User/contact.php?msg=error');
    }
    exit();
}
?>