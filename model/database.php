<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'snowboard_web'; // Thay bằng tên database của bạ n

// Tạo kết nối
$conn = new mysqli($host, $user, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập UTF-8
$conn->set_charset("utf8");

// Thông tin cấu hình chung
function getConfig($key) {
    $config = [
        'site_name' => 'Snowboard Web',
        'site_url' => 'http://localhost/Web_TMDT',
        'admin_email' => 'latruong22061012@gmail.com',
        'support_email' => 'latruong22061012@gmail.com',
        'mail_host' => 'smtp.gmail.com',
        'mail_port' => 587,
        'mail_username' => 'latruong22061012@gmail.com',
        'mail_password' => 'psxnkzjinbumxqol', // Gmail App Password
        'mail_encryption' => 'tls',
    ];
    
    return $config[$key] ?? null;
}

// Kiểm tra quyền truy cập
function checkAccess($required_role = 'user') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: /Web_TMDT/view/User/login.php');
        exit();
    }
    
    if ($required_role == 'admin' && $_SESSION['role'] != 'admin') {
        header('Location: /Web_TMDT/view/User/home.php');
        exit();
    }
    
    // Kiểm tra thời gian timeout phiên
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        // 30 phút không hoạt động
        session_unset();
        session_destroy();
        header('Location: /Web_TMDT/view/User/login.php?msg=timeout');
        exit();
    }
    
    // Cập nhật thời gian hoạt động cuối
    $_SESSION['last_activity'] = time();
}

// Hàm kiểm tra và làm sạch dữ liệu đầu vào
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
