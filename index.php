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

// Chuyển hướng dựa trên trạng thái đăng nhập và vai trò
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: view/Admin/admin_home.php');
    } else {
        header('Location: view/User/home.php');
    }
    exit();
} else {
    header('Location: view/User/home.php');
    exit();
}
?>