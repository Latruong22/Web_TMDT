// ============================================
// PROFILE PAGE - JAVASCRIPT
// ============================================

document.addEventListener("DOMContentLoaded", function () {
  // ============================================
  // SMOOTH SCROLL FOR SIDEBAR MENU
  // ============================================

  const menuLinks = document.querySelectorAll('.profile-menu-link[href^="#"]');

  menuLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      const href = this.getAttribute("href");

      // Skip external links
      if (href.includes("admin") || href.includes(".php")) {
        return;
      }

      e.preventDefault();

      // Handle password link specially
      if (href === "#password") {
        showPasswordForm();
        return;
      }

      // Handle info link
      if (href === "#info") {
        hidePasswordForm();
        return;
      }

      // Remove active class from all links
      menuLinks.forEach((l) => l.classList.remove("active"));

      // Add active class to clicked link
      this.classList.add("active");

      // Scroll to target
      const target = document.querySelector(href);
      if (target) {
        const offsetTop = target.offsetTop - 100;
        window.scrollTo({
          top: offsetTop,
          behavior: "smooth",
        });
      }
    });
  });

  // ============================================
  // UPDATE PERSONAL INFO FORM
  // ============================================

  const updateInfoForm = document.getElementById("updateInfoForm");

  if (updateInfoForm) {
    updateInfoForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      formData.append("action", "update_info");

      // Validate fullname
      const fullname = formData.get("fullname").trim();
      if (!fullname || fullname.length < 2) {
        showAlert("error", "Họ tên phải có ít nhất 2 ký tự");
        return;
      }

      // Validate phone if provided
      const phone = formData.get("phone").trim();
      if (phone && !/^[0-9]{10,11}$/.test(phone)) {
        showAlert("error", "Số điện thoại không hợp lệ (10-11 chữ số)");
        return;
      }

      // Get submit button
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;

      // Disable button and show loading
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';

      // Submit via AJAX
      fetch("/Web_TMDT/controller/controller_User/profile_controller.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showAlert("success", data.message);

            // Update navbar username if changed
            const navbarUser = document.querySelector(
              ".navbar .dropdown-toggle"
            );
            if (navbarUser) {
              navbarUser.innerHTML =
                '<i class="fas fa-user me-1"></i>' + fullname;
            }

            // Update sidebar name
            const sidebarName = document.querySelector(".profile-avatar h5");
            if (sidebarName) {
              sidebarName.textContent = fullname;
            }
          } else {
            showAlert("error", data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showAlert("error", "Có lỗi xảy ra, vui lòng thử lại sau");
        })
        .finally(() => {
          // Re-enable button
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        });
    });
  }

  // ============================================
  // CHANGE PASSWORD FORM
  // ============================================

  const changePasswordForm = document.getElementById("changePasswordForm");

  if (changePasswordForm) {
    changePasswordForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      formData.append("action", "change_password");

      // Validate passwords
      const currentPassword = formData.get("current_password");
      const newPassword = formData.get("new_password");
      const confirmPassword = formData.get("confirm_password");

      if (!currentPassword || !newPassword || !confirmPassword) {
        showAlert("error", "Vui lòng điền đầy đủ thông tin");
        return;
      }

      if (newPassword.length < 6) {
        showAlert("error", "Mật khẩu mới phải có ít nhất 6 ký tự");
        return;
      }

      if (newPassword !== confirmPassword) {
        showAlert("error", "Mật khẩu mới không khớp");
        return;
      }

      if (currentPassword === newPassword) {
        showAlert("error", "Mật khẩu mới phải khác mật khẩu hiện tại");
        return;
      }

      // Confirm before changing password
      if (
        !confirm("⚠️ Bạn có chắc muốn đổi mật khẩu? Bạn sẽ cần đăng nhập lại.")
      ) {
        return;
      }

      // Get submit button
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;

      // Disable button and show loading
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';

      // Submit via AJAX
      fetch("/Web_TMDT/controller/controller_User/profile_controller.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showAlert("success", data.message);

            // Redirect to login after 2 seconds
            setTimeout(() => {
              window.location.href = data.redirect || "login.php";
            }, 2000);
          } else {
            showAlert("error", data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showAlert("error", "Có lỗi xảy ra, vui lòng thử lại sau");
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        });
    });
  }

  // ============================================
  // ALERT SYSTEM
  // ============================================

  function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll(".custom-alert");
    existingAlerts.forEach((alert) => alert.remove());

    // Create alert element
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${
      type === "success" ? "success" : "danger"
    } alert-dismissible fade show custom-alert`;
    alertDiv.style.position = "fixed";
    alertDiv.style.top = "20px";
    alertDiv.style.right = "20px";
    alertDiv.style.zIndex = "9999";
    alertDiv.style.minWidth = "300px";
    alertDiv.style.maxWidth = "500px";
    alertDiv.style.boxShadow = "0 4px 12px rgba(0,0,0,0.15)";

    const icon =
      type === "success"
        ? '<i class="fas fa-check-circle me-2"></i>'
        : '<i class="fas fa-exclamation-circle me-2"></i>';

    alertDiv.innerHTML = `
            ${icon}
            <span>${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    // Add to page
    document.body.appendChild(alertDiv);

    // Auto dismiss after 5 seconds
    setTimeout(() => {
      alertDiv.classList.remove("show");
      setTimeout(() => alertDiv.remove(), 150);
    }, 5000);
  }

  // ============================================
  // FORM VALIDATION HELPERS
  // ============================================

  // Real-time phone validation
  const phoneInput = document.querySelector('input[name="phone"]');
  if (phoneInput) {
    phoneInput.addEventListener("input", function () {
      this.value = this.value.replace(/[^0-9]/g, "");

      if (this.value.length > 0 && this.value.length < 10) {
        this.classList.add("is-invalid");
        this.classList.remove("is-valid");
      } else if (this.value.length >= 10 && this.value.length <= 11) {
        this.classList.add("is-valid");
        this.classList.remove("is-invalid");
      } else {
        this.classList.remove("is-valid", "is-invalid");
      }
    });
  }

  // Password strength indicator
  const newPasswordInput = document.getElementById("newPassword");
  if (newPasswordInput) {
    newPasswordInput.addEventListener("input", function () {
      const password = this.value;
      const strength = calculatePasswordStrength(password);

      // Remove existing strength indicator
      let strengthDiv = this.parentElement.querySelector(".password-strength");
      if (!strengthDiv) {
        strengthDiv = document.createElement("div");
        strengthDiv.className = "password-strength mt-2";
        this.parentElement.appendChild(strengthDiv);
      }

      if (password.length === 0) {
        strengthDiv.innerHTML = "";
        return;
      }

      let color, text;
      if (strength < 3) {
        color = "danger";
        text = "Yếu";
      } else if (strength < 4) {
        color = "warning";
        text = "Trung bình";
      } else {
        color = "success";
        text = "Mạnh";
      }

      strengthDiv.innerHTML = `
                <small class="text-${color}">
                    <i class="fas fa-shield-alt me-1"></i>Độ mạnh: ${text}
                </small>
            `;
    });
  }

  function calculatePasswordStrength(password) {
    let strength = 0;

    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    return strength;
  }

  // Confirm password match indicator
  const confirmPasswordInput = document.getElementById("confirmPassword");
  if (confirmPasswordInput && newPasswordInput) {
    confirmPasswordInput.addEventListener("input", function () {
      if (this.value.length === 0) {
        this.classList.remove("is-valid", "is-invalid");
        return;
      }

      if (this.value === newPasswordInput.value) {
        this.classList.add("is-valid");
        this.classList.remove("is-invalid");
      } else {
        this.classList.add("is-invalid");
        this.classList.remove("is-valid");
      }
    });

    newPasswordInput.addEventListener("input", function () {
      if (confirmPasswordInput.value.length > 0) {
        if (confirmPasswordInput.value === this.value) {
          confirmPasswordInput.classList.add("is-valid");
          confirmPasswordInput.classList.remove("is-invalid");
        } else {
          confirmPasswordInput.classList.add("is-invalid");
          confirmPasswordInput.classList.remove("is-valid");
        }
      }
    });
  }

  // ============================================
  // SCROLL SPY FOR SIDEBAR MENU
  // ============================================

  window.addEventListener("scroll", function () {
    const sections = document.querySelectorAll(".profile-section");
    const scrollPos = window.pageYOffset + 150;

    sections.forEach((section) => {
      const top = section.offsetTop;
      const height = section.offsetHeight;
      const id = section.getAttribute("id");

      if (scrollPos >= top && scrollPos < top + height) {
        menuLinks.forEach((link) => {
          link.classList.remove("active");
          if (link.getAttribute("href") === "#" + id) {
            link.classList.add("active");
          }
        });
      }
    });
  });

  // ============================================
  // CONSOLE INFO
  // ============================================

  console.log("👤 Profile Page Loaded");
  console.log("Forms initialized: Update Info, Change Password, Avatar Upload");
});

// ============================================
// AVATAR UPLOAD FUNCTION (outside DOMContentLoaded)
// ============================================

function handleAvatarChange(input) {
  const file = input.files[0];

  if (!file) {
    return;
  }

  // Validate file type
  const allowedTypes = [
    "image/jpeg",
    "image/jpg",
    "image/png",
    "image/gif",
    "image/webp",
  ];
  if (!allowedTypes.includes(file.type)) {
    alert("❌ Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)");
    input.value = "";
    return;
  }

  // Validate file size (max 5MB)
  const maxSize = 5 * 1024 * 1024; // 5MB
  if (file.size > maxSize) {
    alert("❌ Kích thước ảnh không được vượt quá 5MB");
    input.value = "";
    return;
  }

  // Get avatar image element
  const avatarImg = document.getElementById("avatarImage");

  // Don't preview - just show loading and upload directly
  // This avoids confusion with preview vs actual uploaded image

  // Upload to server
  const formData = new FormData();
  formData.append("action", "upload_avatar");
  formData.append("avatar", file);

  // Add loading overlay (instead of replacing entire wrapper)
  const avatarWrapper = document.querySelector(".avatar-wrapper");
  const loadingOverlay = document.createElement("div");
  loadingOverlay.className = "loading-overlay";
  loadingOverlay.innerHTML = `
    <div class="spinner-border text-light" role="status">
      <span class="visually-hidden">Đang tải...</span>
    </div>
    <div class="text-white mt-2 small">Đang tải lên...</div>
  `;
  loadingOverlay.style.cssText = `
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  `;
  avatarWrapper.appendChild(loadingOverlay);

  fetch("../../controller/controller_User/profile_controller.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Upload response:", data); // Debug log

      // Remove loading overlay
      loadingOverlay.remove();

      if (data.success) {
        // Update avatar with new image (add cache buster)
        const newAvatarPath = "../../" + data.avatar_path + "?v=" + Date.now();
        console.log("Setting new avatar:", newAvatarPath); // Debug log

        // Set new image
        avatarImg.src = newAvatarPath;

        // Remove any onerror handler to prevent fallback
        avatarImg.onerror = function () {
          console.error("Failed to load new avatar:", newAvatarPath);
        };

        // Wait for image to load before showing notification
        avatarImg.onload = function () {
          console.log("New avatar loaded successfully!");
          showNotification("✅ Cập nhật ảnh đại diện thành công!", "success");
        };

        // Reload page after 2 seconds to ensure image is visible
        setTimeout(() => {
          location.reload();
        }, 2000);
      } else {
        // Show error
        showNotification(
          "❌ " + (data.message || "Không thể tải ảnh lên"),
          "danger"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      // Remove loading overlay
      loadingOverlay.remove();
      showNotification("❌ Có lỗi xảy ra, vui lòng thử lại!", "danger");
    });
}

// Show notification toast
function showNotification(message, type = "info") {
  const toast = document.createElement("div");
  toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
  toast.style.cssText = "z-index: 9999; min-width: 300px;";
  toast.textContent = message;

  document.body.appendChild(toast);

  setTimeout(() => {
    toast.remove();
  }, 3000);
}

// ============================================
// SHOW/HIDE PASSWORD FORM
// ============================================

function showPasswordForm() {
  // Hide info section
  const infoSection = document.getElementById("info");
  if (infoSection) {
    infoSection.style.display = "none";
  }

  // Show password section
  const passwordSection = document.getElementById("password");
  if (passwordSection) {
    passwordSection.style.display = "block";

    // Smooth scroll to password section
    const offsetTop = passwordSection.offsetTop - 100;
    window.scrollTo({
      top: offsetTop,
      behavior: "smooth",
    });

    // Update sidebar menu
    const menuLinks = document.querySelectorAll(".profile-menu-link");
    menuLinks.forEach((link) => {
      if (link.getAttribute("href") === "#password") {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });

    // Focus on first input
    setTimeout(() => {
      const firstInput = passwordSection.querySelector(
        'input[type="password"]'
      );
      if (firstInput) {
        firstInput.focus();
      }
    }, 400);
  }
}

function hidePasswordForm() {
  // Show info section
  const infoSection = document.getElementById("info");
  if (infoSection) {
    infoSection.style.display = "block";
  }

  // Hide password section
  const passwordSection = document.getElementById("password");
  if (passwordSection) {
    passwordSection.style.display = "none";

    // Reset form
    const form = passwordSection.querySelector("form");
    if (form) {
      form.reset();
    }

    // Smooth scroll to info section
    const offsetTop = infoSection.offsetTop - 100;
    window.scrollTo({
      top: offsetTop,
      behavior: "smooth",
    });

    // Update sidebar menu
    const menuLinks = document.querySelectorAll(".profile-menu-link");
    menuLinks.forEach((link) => {
      if (link.getAttribute("href") === "#info") {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });
  }
}
