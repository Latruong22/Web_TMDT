<?php
session_start();

// Kiểm tra đăng nhập tự động bằng cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require_once 'model/user_model.php';
    $user = loginByRememberToken($_COOKIE['remember_token']);
    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
    }
}

// Chuyển hướng dựa trên vai trò
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
    // Admin → Admin 
    header('Location: view/Admin/admin_home.php');
    exit();
} else {
    // User hoặc Guest → Trang home duy nhất
    header('Location: view/User/home.php');
    exit();
}
?>