<?php
require_once 'database.php';

// Kiểm tra xem cột last_login đã tồn tại trong bảng users chưa
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'last_login'");
if ($check_column->num_rows == 0) {
    // Nếu chưa có cột last_login, thêm vào
    $add_column = $conn->query("ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER created_at");
    
    if ($add_column) {
        echo "Đã thêm cột 'last_login' vào bảng users thành công.";
    } else {
        echo "Lỗi khi thêm cột 'last_login': " . $conn->error;
    }
} else {
    echo "Cột 'last_login' đã tồn tại trong bảng users.";
}

// Tạo các bảng bổ sung nếu chưa có

// 1. Bảng user_verification
$create_verification_table = $conn->query("
CREATE TABLE IF NOT EXISTS user_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    verification_code VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(email)
)");

// 2. Bảng password_resets
$create_resets_table = $conn->query("
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(email)
)");

// 3. Bảng remember_tokens
$create_tokens_table = $conn->query("
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)");

// 4. Bảng login_history
$create_history_table = $conn->query("
CREATE TABLE IF NOT EXISTS login_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(50),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)");

// Đảm bảo cột status trong bảng users hỗ trợ trạng thái 'pending'
$statusColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
if ($statusColumn && $statusInfo = $statusColumn->fetch_assoc()) {
    if (strpos($statusInfo['Type'], "'pending'") === false) {
        $alterStatus = $conn->query("ALTER TABLE users MODIFY COLUMN status ENUM('pending','active','locked') NOT NULL DEFAULT 'pending'");
        if ($alterStatus) {
            echo "<br>Đã cập nhật cột 'status' cho phép giá trị pending.";
        } else {
            echo "<br>Lỗi khi cập nhật cột status: " . $conn->error;
        }
    }
}

if ($create_verification_table && $create_resets_table && $create_tokens_table && $create_history_table) {
    echo "<br>Đã tạo các bảng bổ sung thành công.";
} else {
    echo "<br>Lỗi khi tạo các bảng bổ sung: " . $conn->error;
}

// Đảm bảo bảng products có cột manual_discount để quản lý khuyến mãi thủ công
$productDiscountColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'manual_discount'");
if ($productDiscountColumn && $productDiscountColumn->num_rows === 0) {
    $addDiscount = $conn->query("ALTER TABLE products ADD COLUMN manual_discount DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER price");
    if ($addDiscount) {
        echo "<br>Đã thêm cột 'manual_discount' vào bảng products.";
    } else {
        echo "<br>Lỗi khi thêm cột manual_discount: " . $conn->error;
    }
}

$conn->close();
?>