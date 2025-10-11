<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to prevent header issues
ob_start();

require_once '../../model/user_model.php';
session_start();

// Debug: Log all POST data
if (!empty($_POST)) {
    error_log("POST data received: " . print_r($_POST, true));
    error_log("Session ID at controller start: " . session_id());
}

// Xử lý đăng ký
if (isset($_POST['register'])) {
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    
    // Kiểm tra độ dài mật khẩu (tối thiểu 6 ký tự)
    if (strlen($password) < 6) {
        header('Location: ../../view/User/register.php?msg=password_short');
        exit();
    }
    
    // Kiểm tra mật khẩu trùng khớp
    if ($password !== $confirm_password) {
        header('Location: ../../view/User/register.php?msg=mismatch');
        exit();
    }
    
    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../../view/User/register.php?msg=email');
        exit();
    }
    
    // Kiểm tra định dạng số điện thoại Việt Nam
    if (!preg_match('/^(0|\+84)[3|5|7|8|9][0-9]{8}$/', $phone)) {
        header('Location: ../../view/User/register.php?msg=phone');
        exit();
    }
    
    // Tiến hành đăng ký
    $register_result = registerUser($fullname, $email, $password, $phone, $address);
    if ($register_result === true) {
        // Đăng ký thành công, chuyển về trang đăng nhập
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Location: ../../view/User/login.php?msg=success');
        exit();
    } else {
        // Đăng ký thất bại (email đã tồn tại)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Location: ../../view/User/register.php?msg=exists');
        exit();
    }
}

// Xử lý đăng nhập
if (isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Debug: Log login attempt
    error_log("Login attempt for: " . $email);
    
    $login_result = validateLogin($email, $password);
    
    // Debug: Log result
    error_log("Login result: " . print_r($login_result, true));
    
    if (is_array($login_result)) {
        // Đăng nhập thành công, thiết lập session
        $_SESSION['user_id'] = $login_result['user_id'];
        $_SESSION['fullname'] = $login_result['fullname'];
        $_SESSION['role'] = $login_result['role'];
        $_SESSION['last_activity'] = time(); // Thêm để kiểm tra timeout phiên
        
        // Debug logging
        error_log("Session set successfully - user_id: " . $_SESSION['user_id'] . ", role: " . $_SESSION['role']);
        
        // Lưu cookie nếu người dùng chọn "Ghi nhớ đăng nhập"
        if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + 30*24*60*60, '/'); // Cookie hết hạn sau 30 ngày
            
            // Lưu token vào cơ sở dữ liệu
            saveRememberToken($login_result['user_id'], $token);
        }
        
        // Lưu lịch sử đăng nhập (nếu bảng đã tồn tại)
        if (function_exists('saveLoginHistory')) {
            saveLoginHistory($login_result['user_id'], $_SERVER['REMOTE_ADDR']);
        }
        
        // Chuyển hướng dựa trên vai trò
        if ($login_result['role'] == 'admin') {
            $redirect_url = '../../view/Admin/admin_home.php';
        } else {
            $redirect_url = '../../view/User/home.php';
        }
        
        // Đảm bảo session được ghi vào disk trước khi redirect
        session_write_close();
        
        // Clean output buffer nếu có
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header("Location: " . $redirect_url);
        exit();
    } else {
        // Đăng nhập thất bại, hiển thị thông báo lỗi
        $error_message = 'fail';
        
        if ($login_result === 'locked') {
            $error_message = 'locked';
        }
        
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header("Location: ../../view/User/login.php?msg=$error_message");
        exit();
    }
}

// Xử lý đăng xuất
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Xóa cookie nếu có
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
        if (isset($_SESSION['user_id'])) {
            removeRememberToken($_SESSION['user_id']);
        }
    }
    
    // Hủy session
    session_unset();
    session_destroy();
    
    // Đăng xuất → Vẫn ở trang home, chỉ là chưa login
    header('Location: ../../view/User/home.php');
    exit();
}

// Nếu không có action nào, redirect về trang chủ
header('Location: ../../view/User/home.php');
exit();
?>