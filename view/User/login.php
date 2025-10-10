<?php
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
// ...existing code...
?>
<!-- filepath: c:\xampp\htdocs\Web_TMDT\view\User\login.php -->
<link rel="stylesheet" href="../../Css/User/login.css">
<script src="../../Js/User/login.js"></script>
<form method="post" action="../../controller/controller_User/controller.php">
    <h2>Đăng nhập</h2>
    <?php
    if ($msg == 'success') echo '<p class="success-msg">Đăng ký thành công! Vui lòng đăng nhập.</p>';
    if ($msg == 'fail') echo '<p class="error-msg">Tài khoản hoặc mật khẩu không đúng!</p>'; 
    if ($msg == 'verify') echo '<p class="info-msg">Vui lòng kiểm tra email để xác nhận tài khoản trước khi đăng nhập.</p>';
    if ($msg == 'verified') echo '<p class="success-msg">Email đã được xác nhận! Bạn có thể đăng nhập ngay bây giờ.</p>';
    if ($msg == 'pending') echo '<p class="error-msg">Tài khoản chưa được xác nhận! Vui lòng kiểm tra email.</p>';
    if ($msg == 'locked') echo '<p class="error-msg">Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.</p>';
    if ($msg == 'timeout') echo '<p class="info-msg">Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.</p>';
    if ($msg == 'reset_success') echo '<p class="success-msg">Mật khẩu đã được đặt lại thành công! Vui lòng đăng nhập.</p>';
    if ($msg == 'resent') echo '<p class="info-msg">Email xác nhận đã được gửi lại. Vui lòng kiểm tra hộp thư đến của bạn.</p>';
    ?>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mật khẩu" required>
    
    <div class="remember-forgot">
        <div class="remember-me">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Ghi nhớ đăng nhập</label>
        </div>
        <div class="forgot-password">
            <a href="forgot_password.php">Quên mật khẩu?</a>
        </div>
    </div>
    
    <button type="submit" name="login">Đăng nhập</button>
    
    <div class="register-link">
        <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </div>
    
    <?php if ($msg == 'pending'): ?>
    <div class="resend-verification">
        <p>Chưa nhận được email xác nhận?</p>
        <form method="post" action="../../controller/controller_User/email_controller.php">
            <input type="hidden" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
            <button type="submit" name="resend_verification">Gửi lại email xác nhận</button>
        </form>
    </div>
    <?php endif; ?>
</form>