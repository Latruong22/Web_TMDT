<?php
session_start();
require_once '../../model/auth_middleware.php';
require_once '../../model/database.php';
require_once '../../model/user_model.php';

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

checkSessionTimeout();

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'update_info':
        $fullname = trim($_POST['fullname'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        // Validation
        if (empty($fullname)) {
            echo json_encode(['success' => false, 'message' => 'Họ tên không được để trống']);
            exit();
        }
        
        if (mb_strlen($fullname) < 2) {
            echo json_encode(['success' => false, 'message' => 'Họ tên phải có ít nhất 2 ký tự']);
            exit();
        }
        
        if (!empty($phone) && !preg_match('/^[0-9]{10,11}$/', $phone)) {
            echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ (10-11 số)']);
            exit();
        }
        
        // Update database
        if (updateUserProfile($user_id, $fullname, $phone, $address)) {
            // Update session
            $_SESSION['fullname'] = $fullname;
            echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại']);
        }
        break;
        
    case 'change_password':
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($current) || empty($new) || empty($confirm)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
            exit();
        }
        
        if ($new !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới không khớp']);
            exit();
        }
        
        if (strlen($new) < 6) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự']);
            exit();
        }
        
        if ($current === $new) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải khác mật khẩu hiện tại']);
            exit();
        }
        
        // Change password using model function
        if (changePassword($user_id, $current, $new)) {
            // Destroy session to force re-login
            session_destroy();
            echo json_encode([
                'success' => true, 
                'message' => 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại',
                'redirect' => 'login.php'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng']);
        }
        break;
        
    case 'upload_avatar':
        // Check if file was uploaded
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Không có file được tải lên']);
            exit();
        }
        
        $file = $_FILES['avatar'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)']);
            exit();
        }
        
        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'Kích thước ảnh không được vượt quá 5MB']);
            exit();
        }
        
        // Validate image dimensions
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            echo json_encode(['success' => false, 'message' => 'File không phải là ảnh hợp lệ']);
            exit();
        }
        
        // Create upload directory if not exists
        $uploadDir = '../../Images/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        $dbPath = 'Images/avatars/' . $filename;
        
        // Get old avatar to delete
        require_once '../../model/database.php';
        global $conn;
        
        $stmt = $conn->prepare("SELECT avatar FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $oldAvatar = $result->fetch_assoc()['avatar'] ?? '';
        $stmt->close();
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            echo json_encode(['success' => false, 'message' => 'Không thể lưu file. Vui lòng thử lại']);
            exit();
        }
        
        // Update database
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
        $stmt->bind_param("si", $dbPath, $user_id);
        
        if ($stmt->execute()) {
            // Delete old avatar if exists
            if (!empty($oldAvatar) && file_exists('../../' . $oldAvatar)) {
                unlink('../../' . $oldAvatar);
            }
            
            $stmt->close();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Cập nhật ảnh đại diện thành công',
                'avatar_path' => $dbPath
            ]);
        } else {
            // Delete uploaded file if database update fails
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            
            $stmt->close();
            
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật cơ sở dữ liệu']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
