<?php
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="../../Css/User/forgot_password.css">
</head>
<body>
    <div class="container">
        <form method="post" action="../../controller/controller_User/email_controller.php">
            <h2>Quên mật khẩu</h2>
            
            <?php if ($msg == 'sent'): ?>
                <div class="success-msg">
                    <p>Link khôi phục mật khẩu đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư đến.</p>
                    <p>Nếu bạn không nhận được email, hãy kiểm tra thư mục spam hoặc thử lại.</p>
                </div>
            <?php else: ?>
                <p class="instruction">Vui lòng nhập địa chỉ email đã đăng ký. Chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.</p>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <button type="submit" name="forgot_password">Gửi link khôi phục</button>
            <?php endif; ?>
            
            <div class="links">
                <a href="login.php">Quay lại đăng nhập</a>
            </div>
        </form>
    </div>
</body>
</html>