// Register page interactive features
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("registerForm");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm_password");

  // Kiểm tra mật khẩu đơn giản (chỉ cần tối thiểu 6 ký tự)
  function isStrongPassword(password) {
    return password.length >= 6;
  }

  // Kiểm tra mật khẩu trùng khớp
  function passwordsMatch(password, confirm) {
    return password === confirm;
  }

  // Xác thực form trước khi submit
  form.addEventListener("submit", function (event) {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    // Kiểm tra độ dài mật khẩu
    if (!isStrongPassword(password)) {
      event.preventDefault();
      alert("Mật khẩu phải có ít nhất 6 ký tự!");
      return;
    }

    // Kiểm tra mật khẩu trùng khớp
    if (!passwordsMatch(password, confirmPassword)) {
      event.preventDefault();
      alert("Mật khẩu xác nhận không khớp!");
      return;
    }
  });

  // Kiểm tra định dạng email
  const emailInput = document.querySelector('input[name="email"]');
  emailInput.addEventListener("blur", function () {
    const email = emailInput.value;
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

    if (!emailRegex.test(email)) {
      emailInput.setCustomValidity("Email không hợp lệ");
    } else {
      emailInput.setCustomValidity("");
    }
  });

  // Kiểm tra định dạng số điện thoại
  const phoneInput = document.getElementById("phone");
  if (phoneInput) {
    phoneInput.addEventListener("blur", function () {
      const phone = phoneInput.value;
      const phoneRegex = /^(0|\+84)[3|5|7|8|9][0-9]{8}$/;

      if (!phoneRegex.test(phone)) {
        phoneInput.setCustomValidity(
          "Số điện thoại không hợp lệ (phải là số điện thoại Việt Nam)"
        );
      } else {
        phoneInput.setCustomValidity("");
      }
    });
  }

  // Toggle password visibility
  const togglePassword = document.getElementById("togglePassword");
  const eyeIcon = document.getElementById("eyeIcon");

  if (togglePassword && passwordInput && eyeIcon) {
    togglePassword.addEventListener("click", function () {
      const type =
        passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);
      eyeIcon.classList.toggle("fa-eye");
      eyeIcon.classList.toggle("fa-eye-slash");
    });
  }

  // Toggle confirm password visibility
  const toggleConfirmPassword = document.getElementById(
    "toggleConfirmPassword"
  );
  const eyeIconConfirm = document.getElementById("eyeIconConfirm");

  if (toggleConfirmPassword && confirmPasswordInput && eyeIconConfirm) {
    toggleConfirmPassword.addEventListener("click", function () {
      const type =
        confirmPasswordInput.getAttribute("type") === "password"
          ? "text"
          : "password";
      confirmPasswordInput.setAttribute("type", type);
      eyeIconConfirm.classList.toggle("fa-eye");
      eyeIconConfirm.classList.toggle("fa-eye-slash");
    });
  }

  // Real-time password strength checker
  if (passwordInput) {
    passwordInput.addEventListener("input", function () {
      const password = this.value;
      updatePasswordChecklist(password);
      updatePasswordStrength(password);
    });
  }

  // Real-time password match checker
  if (confirmPasswordInput && passwordInput) {
    confirmPasswordInput.addEventListener("input", function () {
      const passwordMatch = document.getElementById("passwordMatch");
      if (this.value === "") {
        passwordMatch.innerHTML = "";
      } else if (this.value === passwordInput.value) {
        passwordMatch.innerHTML =
          '<small class="text-success"><i class="fas fa-check-circle me-1"></i>Mật khẩu khớp</small>';
      } else {
        passwordMatch.innerHTML =
          '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Mật khẩu không khớp</small>';
      }
    });
  }

  // Update password checklist
  function updatePasswordChecklist(password) {
    const checks = {
      "check-length": password.length >= 8,
      "check-upper": /[A-Z]/.test(password),
      "check-lower": /[a-z]/.test(password),
      "check-number": /[0-9]/.test(password),
      "check-special": /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password),
    };

    Object.keys(checks).forEach((id) => {
      const element = document.getElementById(id);
      if (element) {
        const icon = element.querySelector("i");
        if (checks[id]) {
          element.classList.add("valid");
          icon.classList.remove("fa-circle", "text-muted");
          icon.classList.add("fa-check-circle", "text-success");
        } else {
          element.classList.remove("valid");
          icon.classList.remove("fa-check-circle", "text-success");
          icon.classList.add("fa-circle", "text-muted");
        }
      }
    });
  }

  // Update password strength indicator
  function updatePasswordStrength(password) {
    const strengthDiv = document.getElementById("passwordStrength");
    if (!strengthDiv) return;

    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength++;

    const levels = [
      { text: "Rất yếu", class: "text-danger", width: "20%" },
      { text: "Yếu", class: "text-warning", width: "40%" },
      { text: "Trung bình", class: "text-info", width: "60%" },
      { text: "Mạnh", class: "text-primary", width: "80%" },
      { text: "Rất mạnh", class: "text-success", width: "100%" },
    ];

    const level = levels[strength - 1] || levels[0];

    if (password.length > 0) {
      strengthDiv.innerHTML = `
        <div class="progress" style="height: 8px;">
          <div class="progress-bar ${level.class.replace(
            "text-",
            "bg-"
          )}" style="width: ${level.width}"></div>
        </div>
        <small class="${level.class} fw-bold mt-1 d-block">Độ mạnh: ${
        level.text
      }</small>
      `;
    } else {
      strengthDiv.innerHTML = "";
    }
  }

  // Form submission with loading state
  if (form) {
    form.addEventListener("submit", function (e) {
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) {
        // Show loading text but don't disable to allow form submission
        submitBtn.innerHTML =
          '<i class="fas fa-spinner fa-spin me-2"></i>Đang đăng ký...';
        // Don't disable - let the form submit naturally
      }
    });
  }

  // Auto-dismiss alerts
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    setTimeout(() => {
      if (alert && alert.querySelector(".btn-close")) {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert.close();
      }
    }, 8000);
  });

  console.log("Register page loaded successfully!");
});
