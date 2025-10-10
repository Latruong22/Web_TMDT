<?php
require_once '../../model/user_model.php';
session_start();

// Xử lý đăng ký
if (isset($_POST['register'])) {
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    
    // Kiểm tra mật khẩu mạnh
    $password_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    if (!preg_match($password_regex, $password)) {
        header('Location: ../../view/User/register.php?msg=password');
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
        // Gửi email xác nhận
        require_once '../../model/email_model.php';
        $verification_code = md5(time() . $email);
        saveVerificationCode($email, $verification_code);
        $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/Web_TMDT/controller/controller_User/email_controller.php?action=verify&code=" . $verification_code;
        sendVerificationEmail($email, $fullname, $verification_link);
        
        header('Location: ../../view/User/login.php?msg=verify');
        exit();
    } else {
        header('Location: ../../view/User/register.php?msg=' . $register_result);
        exit();
    }
}

// Xử lý đăng nhập
if (isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $login_result = validateLogin($email, $password);
    
    if (is_array($login_result)) {
        // Đăng nhập thành công, thiết lập session
        $_SESSION['user_id'] = $login_result['user_id'];
        $_SESSION['fullname'] = $login_result['fullname'];
        $_SESSION['role'] = $login_result['role'];
        $_SESSION['last_activity'] = time(); // Thêm để kiểm tra timeout phiên
        
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
        
        // Chuyển hướng theo quyền
        if ($login_result['role'] == 'admin') {
            header('Location: ../../view/Admin/admin_home.php');
        } else {
            header('Location: ../../view/User/home.php');
        }
        exit();
    } else {
        // Đăng nhập thất bại, hiển thị thông báo lỗi
        $error_message = 'fail';
        
        if ($login_result === 'pending') {
            $error_message = 'pending';
        } else if ($login_result === 'locked') {
            $error_message = 'locked';
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
        removeRememberToken($_SESSION['user_id']);
    }
    
    // Hủy session
    session_unset();
    session_destroy();
    
    header('Location: ../../index.php');
    exit();
}
?>