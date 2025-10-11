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
    <title>Đặt lại mật khẩu - Snowboard Shop</title>
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Css/User/forgot_password.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="forgot-password-container">
        <div class="row g-0 h-100">
            <!-- Left Side - Brand/Image -->
            <div class="col-lg-6 d-none d-lg-block">
                <div class="brand-side">
                    <div class="brand-overlay"></div>
                    <div class="brand-content">
                        <img src="../../Images/logo/logo.jpg" alt="Snowboard Shop Logo" class="brand-logo animate-logo">
                        <h1 class="brand-title">SNOWBOARD SHOP</h1>
                        <p class="brand-subtitle">Tạo mật khẩu mới và an toàn</p>
                        <div class="brand-features">
                            <div class="feature-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Bảo mật tối đa</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-key"></i>
                                <span>Mật khẩu mạnh</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Dễ dàng & Nhanh</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Reset Password Form -->
            <div class="col-lg-6">
                <div class="form-side">
                    <div class="form-container">
                        <!-- Mobile Logo -->
                        <div class="mobile-logo d-lg-none text-center mb-4">
                            <img src="../../Images/logo/logo.jpg" alt="Logo" class="img-fluid rounded-3" style="max-width: 120px;">
                            <h4 class="mt-3 fw-bold">SNOWBOARD SHOP</h4>
                        </div>

                        <!-- Back to Login -->
                        <div class="back-to-home mb-4">
                            <a href="login.php" class="text-decoration-none text-dark">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại đăng nhập
                            </a>
                        </div>

                        <?php if ($msg == 'expired'): ?>
                            <!-- Expired Token -->
                            <div class="text-center mb-4">
                                <div class="error-icon mb-3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h2 class="form-title">Link đã hết hạn</h2>
                                <p class="form-subtitle text-muted">Link đặt lại mật khẩu không còn hiệu lực</p>
                            </div>

                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-times-circle me-2"></i>
                                Link đặt lại mật khẩu đã <strong>hết hạn</strong> hoặc không hợp lệ.
                            </div>

                            <div class="text-center mt-4">
                                <a href="forgot_password.php" class="btn btn-dark btn-lg w-100 mb-3">
                                    <i class="fas fa-redo me-2"></i>Yêu cầu link mới
                                </a>
                                <a href="login.php" class="btn btn-outline-dark btn-lg w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Về trang đăng nhập
                                </a>
                            </div>

                        <?php else: ?>
                            <!-- Reset Password Form -->
                            <div class="text-center mb-4">
                                <div class="reset-icon mb-3">
                                    <i class="fas fa-lock-open"></i>
                                </div>
                                <h2 class="form-title">Đặt lại mật khẩu</h2>
                                <p class="form-subtitle text-muted">Tạo mật khẩu mới và mạnh hơn</p>
                            </div>

                            <!-- Alert Messages -->
                            <?php if ($msg == 'mismatch'): ?>
                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Mật khẩu xác nhận không khớp. Vui lòng thử lại.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif ($msg == 'weak_password'): ?>
                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <i class="fas fa-key me-2"></i>
                                    Mật khẩu không đủ mạnh! Vui lòng đáp ứng các yêu cầu bên dưới.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="post" action="../../controller/controller_User/email_controller.php" class="auth-form" id="resetPasswordForm">
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Mật khẩu mới
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control form-control-lg" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Nhập mật khẩu mới" 
                                               required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye" id="eyeIcon"></i>
                                        </button>
                                    </div>
                                    <div id="passwordStrength" class="mt-2"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control form-control-lg" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               placeholder="Nhập lại mật khẩu mới" 
                                               required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye" id="eyeIconConfirm"></i>
                                        </button>
                                    </div>
                                    <div id="passwordMatch" class="mt-2"></div>
                                </div>

                                <!-- Password Requirements Card -->
                                <div class="card border-0 bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="fas fa-info-circle me-2"></i>Yêu cầu mật khẩu:
                                        </h6>
                                        <ul class="list-unstyled mb-0 password-checklist">
                                            <li id="check-length"><i class="fas fa-circle text-muted me-2"></i>Ít nhất 8 ký tự</li>
                                            <li id="check-upper"><i class="fas fa-circle text-muted me-2"></i>Ít nhất 1 chữ hoa</li>
                                            <li id="check-lower"><i class="fas fa-circle text-muted me-2"></i>Ít nhất 1 chữ thường</li>
                                            <li id="check-number"><i class="fas fa-circle text-muted me-2"></i>Ít nhất 1 số</li>
                                            <li id="check-special"><i class="fas fa-circle text-muted me-2"></i>Ít nhất 1 ký tự đặc biệt (!@#$%^&*)</li>
                                        </ul>
                                    </div>
                                </div>

                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                
                                <button type="submit" name="reset_password" class="btn btn-dark btn-lg w-100 mb-3">
                                    <i class="fas fa-check me-2"></i>Đặt lại mật khẩu
                                </button>

                                <div class="text-center">
                                    <p class="text-muted mb-0">
                                        Nhớ mật khẩu? 
                                        <a href="login.php" class="fw-bold text-dark text-decoration-none">Đăng nhập ngay</a>
                                    </p>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const form = document.getElementById('resetPasswordForm');

            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    eyeIcon.classList.toggle('fa-eye');
                    eyeIcon.classList.toggle('fa-eye-slash');
                });
            }

            // Toggle confirm password visibility
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const eyeIconConfirm = document.getElementById('eyeIconConfirm');

            if (toggleConfirmPassword && confirmPasswordInput) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPasswordInput.setAttribute('type', type);
                    eyeIconConfirm.classList.toggle('fa-eye');
                    eyeIconConfirm.classList.toggle('fa-eye-slash');
                });
            }

            // Real-time password strength checker
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    updatePasswordChecklist(password);
                    updatePasswordStrength(password);
                });
            }

            // Real-time password match checker
            if (confirmPasswordInput && passwordInput) {
                confirmPasswordInput.addEventListener('input', function() {
                    const passwordMatch = document.getElementById('passwordMatch');
                    if (this.value === '') {
                        passwordMatch.innerHTML = '';
                    } else if (this.value === passwordInput.value) {
                        passwordMatch.innerHTML = '<small class="text-success"><i class="fas fa-check-circle me-1"></i>Mật khẩu khớp</small>';
                    } else {
                        passwordMatch.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Mật khẩu không khớp</small>';
                    }
                });
            }

            // Update password checklist
            function updatePasswordChecklist(password) {
                const checks = {
                    'check-length': password.length >= 8,
                    'check-upper': /[A-Z]/.test(password),
                    'check-lower': /[a-z]/.test(password),
                    'check-number': /[0-9]/.test(password),
                    'check-special': /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
                };

                Object.keys(checks).forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        const icon = element.querySelector('i');
                        if (checks[id]) {
                            element.classList.add('valid');
                            icon.classList.remove('fa-circle', 'text-muted');
                            icon.classList.add('fa-check-circle', 'text-success');
                        } else {
                            element.classList.remove('valid');
                            icon.classList.remove('fa-check-circle', 'text-success');
                            icon.classList.add('fa-circle', 'text-muted');
                        }
                    }
                });
            }

            // Update password strength indicator
            function updatePasswordStrength(password) {
                const strengthDiv = document.getElementById('passwordStrength');
                if (!strengthDiv) return;

                let strength = 0;
                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength++;

                const levels = [
                    { text: 'Rất yếu', class: 'text-danger', width: '20%' },
                    { text: 'Yếu', class: 'text-warning', width: '40%' },
                    { text: 'Trung bình', class: 'text-info', width: '60%' },
                    { text: 'Mạnh', class: 'text-primary', width: '80%' },
                    { text: 'Rất mạnh', class: 'text-success', width: '100%' }
                ];

                const level = levels[strength - 1] || levels[0];
                
                if (password.length > 0) {
                    strengthDiv.innerHTML = `
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar ${level.class.replace('text-', 'bg-')}" style="width: ${level.width}"></div>
                        </div>
                        <small class="${level.class} fw-bold mt-1 d-block">Độ mạnh: ${level.text}</small>
                    `;
                } else {
                    strengthDiv.innerHTML = '';
                }
            }

            // Form submission
            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = passwordInput.value;
                    const confirmPassword = confirmPasswordInput.value;

                    // Validate password strength
                    if (!isStrongPassword(password)) {
                        e.preventDefault();
                        alert('Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt!');
                        return;
                    }

                    // Validate password match
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Mật khẩu xác nhận không khớp!');
                        return;
                    }

                    // Show loading state
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
                        submitBtn.disabled = true;

                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 5000);
                    }
                });
            }

            // Check if password is strong
            function isStrongPassword(password) {
                const minLength = password.length >= 8;
                const hasUpperCase = /[A-Z]/.test(password);
                const hasLowerCase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
                
                return minLength && hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar;
            }

            // Auto-dismiss alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert && alert.querySelector('.btn-close')) {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                        bsAlert.close();
                    }
                }, 8000);
            });
        });
    </script>
</body>
</html>