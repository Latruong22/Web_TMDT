<?php
require_once 'database.php';

// Đăng ký người dùng mới
function registerUser($fullname, $email, $password, $phone, $address) {
    global $conn;
    
    // Kiểm tra email đã tồn tại
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if($check->get_result()->num_rows > 0) {
        return false; // Email đã tồn tại
    }
    
    // Mã hóa mật khẩu
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    
    // Thêm user mới với status active (bỏ qua xác nhận email)
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, phone, address, status, role) VALUES (?, ?, ?, ?, ?, 'active', 'user')");
    $stmt->bind_param("sssss", $fullname, $email, $hashed, $phone, $address);
    return $stmt->execute();
}

// Thêm bảng user_verification nếu chưa có
function createVerificationTable() {
    global $conn;
    $sql = "CREATE TABLE IF NOT EXISTS user_verification (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        verification_code VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(email)
    )";
    return $conn->query($sql);
}

// Lưu mã xác nhận email
function saveVerificationCode($email, $code) {
    global $conn;
    createVerificationTable();
    
    // Xóa mã cũ nếu có
    $stmt = $conn->prepare("DELETE FROM user_verification WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();
    
    // Lưu mã mới
    $stmt = $conn->prepare("INSERT INTO user_verification (email, verification_code) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $code);
    return $stmt->execute();
}

// Xác nhận email với mã xác nhận
function verifyEmail($verification_code) {
    global $conn;
    $stmt = $conn->prepare("SELECT email FROM user_verification WHERE verification_code = ?");
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $email = $row['email'];
        
        // Cập nhật trạng thái người dùng thành active
        $update_stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE email = ?");
        $update_stmt->bind_param("s", $email);
        if ($update_stmt->execute()) {
            // Xóa mã xác nhận đã sử dụng
            $delete_stmt = $conn->prepare("DELETE FROM user_verification WHERE email = ?");
            $delete_stmt->bind_param("s", $email);
            $delete_stmt->execute();
            return true;
        }
    }
    return false;
}

// Đăng nhập
function loginUser($email, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT user_id, fullname, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        // Chỉ kiểm tra locked, bỏ qua pending vì đã không dùng nữa
        if ($row['status'] === 'locked') {
            return 'locked'; // Tài khoản bị khóa
        }
        
        if (password_verify($password, $row['password'])) {
            // Kiểm tra xem trường last_login có tồn tại không
            $check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'last_login'");
            if ($check_column->num_rows > 0) {
                // Nếu trường tồn tại, cập nhật thời gian đăng nhập
                $update_stmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param("i", $row['user_id']);
                    $update_stmt->execute();
                }
            }
            return $row;
        }
    }
    return false;
}

// Kiểm tra đăng nhập đúng không
function validateLogin($email, $password) {
    $result = loginUser($email, $password);
    if ($result === 'locked') {
        return 'locked';
    } else if ($result === false) {
        return 'invalid';
    } else {
        return $result; // Trả về thông tin người dùng nếu đăng nhập thành công
    }
}

// Quên mật khẩu
function generateResetToken($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND status = 'active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        return false; // Email không tồn tại hoặc tài khoản chưa kích hoạt
    }
    
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 3600); // Hết hạn sau 1 giờ
    
    // Tạo bảng password_resets nếu chưa có
    $conn->query("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(email)
    )");
    
    // Xóa token cũ nếu có
    $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $delete_stmt->bind_param("s", $email);
    $delete_stmt->execute();
    
    // Lưu token mới
    $insert_stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("sss", $email, $token, $expires);
    
    if ($insert_stmt->execute()) {
        return $token;
    }
    return false;
}

// Đặt lại mật khẩu
function resetPassword($token, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $email = $row['email'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Cập nhật mật khẩu
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $hashedPassword, $email);
        
        if ($update_stmt->execute()) {
            // Xóa token đã sử dụng
            $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $delete_stmt->bind_param("s", $email);
            $delete_stmt->execute();
            return true;
        }
    }
    return false;
}

// Thêm bảng remember_tokens nếu chưa có
function createRememberTokensTable() {
    global $conn;
    $sql = "CREATE TABLE IF NOT EXISTS remember_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(user_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    return $conn->query($sql);
}

// Thêm bảng login_history nếu chưa có
function createLoginHistoryTable() {
    global $conn;
    $sql = "CREATE TABLE IF NOT EXISTS login_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        ip_address VARCHAR(50),
        user_agent TEXT,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    return $conn->query($sql);
}

// Lưu token "Ghi nhớ đăng nhập"
function saveRememberToken($user_id, $token) {
    global $conn;
    createRememberTokensTable();
    
    $expires = date('Y-m-d H:i:s', time() + 30*24*60*60); // 30 ngày
    
    // Xóa token cũ nếu có
    $delete_stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();
    
    // Lưu token mới
    $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $token, $expires);
    return $stmt->execute();
}

// Xóa token "Ghi nhớ đăng nhập"
function removeRememberToken($user_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

// Lưu lịch sử đăng nhập
function saveLoginHistory($user_id, $ip_address) {
    global $conn;
    
    // Kiểm tra xem bảng login_history đã tồn tại chưa
    $table_exists = $conn->query("SHOW TABLES LIKE 'login_history'");
    if ($table_exists->num_rows == 0) {
        createLoginHistoryTable();
    }
    
    try {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt = $conn->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iss", $user_id, $ip_address, $user_agent);
            return $stmt->execute();
        }
    } catch (Exception $e) {
        error_log("Lỗi lưu lịch sử đăng nhập: " . $e->getMessage());
    }
    return false;
}

// Kiểm tra đăng nhập tự động bằng cookie
function loginByRememberToken($token) {
    global $conn;
    $stmt = $conn->prepare("SELECT user_id FROM remember_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        
        // Lấy thông tin người dùng
        $user_stmt = $conn->prepare("SELECT user_id, fullname, role, status FROM users WHERE user_id = ? AND status = 'active'");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_result && $user = $user_result->fetch_assoc()) {
            return $user;
        }
    }
    return false;
}

// Lấy thông tin người dùng theo email
function getUserByEmail($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT user_id, fullname, email, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row;
    }
    return false;
}

// Cập nhật thông tin người dùng
function updateUserProfile($user_id, $fullname, $phone, $address) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET fullname = ?, phone = ?, address = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $fullname, $phone, $address, $user_id);
    return $stmt->execute();
}

// Thay đổi mật khẩu
function changePassword($user_id, $current_password, $new_password) {
    global $conn;
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        if (password_verify($current_password, $row['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            return $update_stmt->execute();
        }
    }
    return false;
}

// Kiểm tra trạng thái có thuộc nhóm chờ kích hoạt hay không
function isPendingStatusValue($status) {
    if ($status === null) {
        return true;
    }
    $normalized = strtolower(trim((string) $status));
    if ($normalized === '') {
        return true;
    }
    return !in_array($normalized, ['active', 'locked'], true);
}

// Lấy danh sách người dùng cho trang quản trị
function getAdminUsers(array $filters = []) {
    global $conn;

    // Đảm bảo bảng lịch sử đăng nhập tồn tại để có thể lấy thông tin lần đăng nhập cuối
    createLoginHistoryTable();

    $sql = "SELECT u.user_id, u.fullname, u.email, u.phone, u.address, u.role, u.status, u.created_at,
                   lh.last_login
            FROM users u
            LEFT JOIN (
                SELECT user_id, MAX(login_time) AS last_login
                FROM login_history
                GROUP BY user_id
            ) lh ON lh.user_id = u.user_id
            WHERE 1 = 1";

    $types = '';
    $params = [];

    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        if ($filters['status'] === 'pending') {
            $sql .= " AND (u.status IS NULL OR u.status = '' OR LOWER(u.status) NOT IN ('active', 'locked'))";
        } else {
            $sql .= " AND LOWER(u.status) = ?";
            $types .= 's';
            $params[] = strtolower($filters['status']);
        }
    }

    if (!empty($filters['role']) && $filters['role'] !== 'all') {
        $sql .= " AND u.role = ?";
        $types .= 's';
        $params[] = $filters['role'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (u.fullname LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
        $types .= 'sss';
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($filters['from'])) {
        $sql .= " AND DATE(u.created_at) >= ?";
        $types .= 's';
        $params[] = $filters['from'];
    }

    if (!empty($filters['to'])) {
        $sql .= " AND DATE(u.created_at) <= ?";
        $types .= 's';
        $params[] = $filters['to'];
    }

    $sql .= " ORDER BY u.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('getAdminUsers prepare failed: ' . $conn->error);
        return [];
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    $stmt->close();
    return $users;
}

// Lấy thống kê nhanh về người dùng cho dashboard quản trị
function getUserSummaryStats() {
    global $conn;

    $sql = "SELECT
                COUNT(*) AS total_users,
                SUM(CASE WHEN LOWER(status) = 'active' THEN 1 ELSE 0 END) AS active_users,
                SUM(CASE WHEN LOWER(status) = 'locked' THEN 1 ELSE 0 END) AS locked_users,
                SUM(CASE WHEN status IS NULL OR status = '' OR LOWER(status) NOT IN ('active','locked') THEN 1 ELSE 0 END) AS pending_users,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admin_users
            FROM users";

    $result = $conn->query($sql);
    if (!$result) {
        return [
            'total_users' => 0,
            'active_users' => 0,
            'locked_users' => 0,
            'pending_users' => 0,
            'admin_users' => 0,
        ];
    }

    $row = $result->fetch_assoc();
    return [
        'total_users' => (int) ($row['total_users'] ?? 0),
        'active_users' => (int) ($row['active_users'] ?? 0),
        'locked_users' => (int) ($row['locked_users'] ?? 0),
        'pending_users' => (int) ($row['pending_users'] ?? 0),
        'admin_users' => (int) ($row['admin_users'] ?? 0),
    ];
}

// Lấy thông tin người dùng theo ID
function getUserById($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT user_id, fullname, email, phone, address, avatar, role, status, created_at FROM users WHERE user_id = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $user;
}

// Cập nhật trạng thái người dùng từ trang quản trị
function updateUserStatusAdmin($user_id, $status) {
    global $conn;
    $allowed = ['active', 'locked', 'pending'];
    if (!in_array($status, $allowed, true)) {
        return false;
    }
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('si', $status, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Cập nhật vai trò người dùng từ trang quản trị
function updateUserRoleAdmin($user_id, $role) {
    global $conn;
    $allowed = ['user', 'admin'];
    if (!in_array($role, $allowed, true)) {
        return false;
    }
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('si', $role, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Lấy lịch sử đăng nhập gần đây của người dùng
function getUserLoginHistory($user_id, $limit = 5) {
    global $conn;

    $tableExists = $conn->query("SHOW TABLES LIKE 'login_history'");
    if (!$tableExists || $tableExists->num_rows === 0) {
        return [];
    }
    if ($tableExists instanceof mysqli_result) {
        $tableExists->free();
    }

    $stmt = $conn->prepare("SELECT login_time, ip_address, user_agent
                             FROM login_history
                             WHERE user_id = ?
                             ORDER BY login_time DESC
                             LIMIT ?");
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('ii', $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
    }
    $stmt->close();
    return $history;
}

// Đặt lại mật khẩu cho người dùng từ trang quản trị, trả về mật khẩu mới dạng thuần
function resetUserPasswordAdmin($user_id) {
    global $conn;

    try {
        $rawPassword = bin2hex(random_bytes(4)) . random_int(100, 999);
    } catch (Exception $e) {
        $rawPassword = substr(md5(microtime(true)), 0, 10);
    }

    $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('si', $hashedPassword, $user_id);
    $result = $stmt->execute();
    $stmt->close();

    return $result ? $rawPassword : false;
}
?>