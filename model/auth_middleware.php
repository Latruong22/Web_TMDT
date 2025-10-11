<?php
/**
 * Auth Middleware - Kiểm tra đăng nhập và phân quyền
 */

// Kiểm tra user đã đăng nhập chưa
function requireLogin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: login.php?msg=timeout');
        exit();
    }
}

// Chỉ cho phép user (không phải admin)
function requireUser() {
    requireLogin();
    if ($_SESSION['role'] !== 'user') {
        header('Location: ../Admin/admin_home.php');
        exit();
    }
}

// Chỉ cho phép admin
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: home.php?msg=forbidden');
        exit();
    }
}

// Kiểm tra session timeout (30 phút không hoạt động)
function checkSessionTimeout($timeout = 1800) {
    // Khởi tạo last_activity nếu chưa có (lần đầu login)
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return;
    }
    
    // Kiểm tra timeout
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive > $timeout) {
        session_unset();
        session_destroy();
        header('Location: login.php?msg=timeout');
        exit();
    }
    
    // Cập nhật thời gian hoạt động
    $_SESSION['last_activity'] = time();
}
?>
