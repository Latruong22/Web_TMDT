<?php
$token = isset($_GET['token']) ? $_GET['token'] : '';
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

if (empty($token)) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="../../Css/User/forgot_password.css">
</head>
<body>
    <div class="container">
        <form method="post" action="../../controller/controller_User/email_controller.php">
            <h2>Đặt lại mật khẩu</h2>
            
            <?php if ($msg == 'expired'): ?>
                <div class="error-msg">
                    <p>Link đặt lại mật khẩu đã hết hạn hoặc không hợp lệ.</p>
                    <p>Vui lòng <a href="forgot_password.php">yêu cầu lại link mới</a>.</p>
                </div>
            <?php elseif ($msg == 'mismatch'): ?>
                <div class="error-msg">
                    <p>Mật khẩu xác nhận không khớp. Vui lòng thử lại.</p>
                </div>
            <?php elseif ($msg == 'weak_password'): ?>
                <div class="error-msg">
                    <p>Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt!</p>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Mật khẩu mới" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu mới" required>
                </div>
                
                <div class="password-requirements">
                    <p>Mật khẩu phải có:</p>
                    <ul>
                        <li>Ít nhất 8 ký tự</li>
                        <li>Ít nhất 1 chữ hoa</li>
                        <li>Ít nhất 1 chữ thường</li>
                        <li>Ít nhất 1 số</li>
                        <li>Ít nhất 1 ký tự đặc biệt</li>
                    </ul>
                </div>
                
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <button type="submit" name="reset_password">Đặt lại mật khẩu</button>
            <?php endif; ?>
            
            <div class="links">
                <a href="login.php">Quay lại đăng nhập</a>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const passwordInput = document.querySelector('input[name="password"]');
        const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
        
        // Kiểm tra mật khẩu mạnh
        function isStrongPassword(password) {
            const minLength = password.length >= 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
            
            return minLength && hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar;
        }
        
        // Xác thực form trước khi submit
        form.addEventListener('submit', function(event) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Kiểm tra mật khẩu mạnh
            if (!isStrongPassword(password)) {
                event.preventDefault();
                alert('Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt!');
                return;
            }
            
            // Kiểm tra mật khẩu trùng khớp
            if (password !== confirmPassword) {
                event.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return;
            }
        });
    });
    </script>
</body>
</html>