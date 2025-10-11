// Login page interactive features
document.addEventListener("DOMContentLoaded", function () {
  // Toggle password visibility
  const togglePassword = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("password");
  const eyeIcon = document.getElementById("eyeIcon");

  if (togglePassword && passwordInput && eyeIcon) {
    togglePassword.addEventListener("click", function () {
      const type =
        passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);

      // Toggle eye icon
      if (type === "text") {
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
      } else {
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
      }
    });
  }

  // Auto-dismiss alerts after 8 seconds
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    setTimeout(() => {
      if (alert && alert.querySelector(".btn-close")) {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert.close();
      }
    }, 8000);
  });

  // Remember me functionality
  const rememberCheckbox = document.getElementById("remember");
  const emailInput = document.getElementById("email");

  // Load saved email if exists
  const savedEmail = localStorage.getItem("savedEmail");
  if (savedEmail && emailInput) {
    emailInput.value = savedEmail;
    if (rememberCheckbox) {
      rememberCheckbox.checked = true;
    }
  }

  // Form submission handler
  const loginForm = document.querySelector(".auth-form");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      // Save email if remember me is checked
      if (rememberCheckbox && rememberCheckbox.checked && emailInput) {
        localStorage.setItem("savedEmail", emailInput.value);
      } else {
        localStorage.removeItem("savedEmail");
      }

      // Show loading state (but DON'T disable button as it may prevent form submission)
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) {
        // Change button text to show loading
        submitBtn.innerHTML =
          '<i class="fas fa-spinner fa-spin me-2"></i>Đang đăng nhập...';
        // Don't disable - let the form submit naturally
      }

      // Form will submit automatically, no need to prevent or handle manually
    });
  }

  // Email validation
  if (emailInput) {
    emailInput.addEventListener("blur", function () {
      const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
      if (this.value && !emailRegex.test(this.value)) {
        this.classList.add("is-invalid");
      } else {
        this.classList.remove("is-invalid");
      }
    });
  }

  // Focus first empty input
  setTimeout(() => {
    if (emailInput && !emailInput.value) {
      emailInput.focus();
    } else if (passwordInput && !passwordInput.value) {
      passwordInput.focus();
    }
  }, 300);

  console.log("Login page loaded successfully!");
});
