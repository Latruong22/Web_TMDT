<?php
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
// ...existing code...
?>
<link rel="stylesheet" href="../../Css/User/register.css">
<script src="../../Js/User/register.js"></script>
<form method="post" action="../../controller/controller_User/controller.php">
    <h2>Đăng ký</h2>
    <?php
    if ($msg == 'exists') echo '<p style="color:red;">Tài khoản đã tồn tại!</p>';
    if ($msg == 'password') echo '<p style="color:red;">Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt!</p>';
    ?>
    <input type="text" name="fullname" placeholder="Họ tên" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mật khẩu" required>
    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
    <input type="tel" name="phone" placeholder="Số điện thoại" required>
    <textarea name="address" placeholder="Địa chỉ" required></textarea>
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
    <button type="submit" name="register">Đăng ký</button>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
</form>