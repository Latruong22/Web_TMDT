/**
 * Admin Email Management JavaScript
 * Xử lý gửi email, user selection, preview
 */

// ========================================
// KHỞI TẠO
// ========================================

document.addEventListener("DOMContentLoaded", function () {
  console.log("📧 Admin Email Management loaded");

  initializeRecipientToggle();
  initializeEditorButtons();
  initializeTemplateSelection();
  initializeSendEmail();
  initializePreview();
  initializeClearButton();
});

// ========================================
// RECIPIENT SELECTION
// ========================================

/**
 * Khởi tạo toggle giữa "Tất cả" và "Chọn user"
 */
function initializeRecipientToggle() {
  const recipientAll = document.getElementById("recipientAll");
  const recipientIndividual = document.getElementById("recipientIndividual");
  const userSelectionContainer = document.querySelector(
    ".user-selection-container"
  );

  recipientAll.addEventListener("change", function () {
    if (this.checked) {
      userSelectionContainer.style.display = "none";
    }
  });

  recipientIndividual.addEventListener("change", function () {
    if (this.checked) {
      userSelectionContainer.style.display = "block";
      loadUsersList();
    }
  });
}

/**
 * Load danh sách users từ server
 */
function loadUsersList() {
  const container = document.getElementById("userListContainer");

  fetch(
    "../../controller/controller_Admin/admin_email_controller.php?action=get_users"
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayUsersList(data.users);
      } else {
        container.innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        ${data.message}
                    </div>
                `;
      }
    })
    .catch((error) => {
      console.error("Lỗi load users:", error);
      container.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Lỗi kết nối server
                </div>
            `;
    });
}

/**
 * Hiển thị danh sách users với checkboxes
 */
function displayUsersList(users) {
  const container = document.getElementById("userListContainer");

  if (users.length === 0) {
    container.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="fas fa-users-slash fa-2x mb-2"></i>
                <p class="mb-0">Không có user nào</p>
            </div>
        `;
    return;
  }

  // Thêm checkbox "Select All"
  let html = `
        <div class="user-item border-bottom pb-2 mb-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAll">
                <label class="form-check-label fw-bold" for="selectAll">
                    <i class="fas fa-check-double me-2"></i>Chọn tất cả (${users.length} users)
                </label>
            </div>
        </div>
    `;

  // Thêm từng user
  users.forEach((user) => {
    html += `
            <div class="user-item">
                <div class="form-check">
                    <input class="form-check-input user-checkbox" type="checkbox" 
                           value="${user.user_id}" id="user_${user.user_id}">
                    <label class="form-check-label" for="user_${user.user_id}">
                        <div class="user-info">
                            <span class="user-name">
                                <i class="fas fa-user me-1"></i>${user.fullname}
                            </span>
                            <span class="user-email">${user.email}</span>
                        </div>
                    </label>
                </div>
            </div>
        `;
  });

  container.innerHTML = html;

  // Xử lý "Select All"
  document.getElementById("selectAll").addEventListener("change", function () {
    const checkboxes = document.querySelectorAll(".user-checkbox");
    checkboxes.forEach((cb) => (cb.checked = this.checked));
  });
}

// ========================================
// EDITOR FUNCTIONS
// ========================================

/**
 * Khởi tạo các button trong editor toolbar
 */
function initializeEditorButtons() {
  // Focus vào editor khi click
  const editor = document.getElementById("emailEditor");
  editor.addEventListener("focus", function () {
    this.classList.add("focused");
  });

  editor.addEventListener("blur", function () {
    this.classList.remove("focused");
  });
}

/**
 * Insert variable vào vị trí cursor
 */
function insertVariable(variable) {
  const editor = document.getElementById("emailEditor");
  editor.focus();

  // Insert text tại vị trí cursor
  document.execCommand("insertText", false, variable);
}

// ========================================
// TEMPLATE SELECTION
// ========================================

/**
 * Khởi tạo template selection
 */
function initializeTemplateSelection() {
  const templateSelect = document.getElementById("templateSelect");
  const templateItems = document.querySelectorAll(".template-item");

  // Khi chọn template từ dropdown
  templateSelect.addEventListener("change", function () {
    if (this.value !== "custom") {
      loadTemplate(this.value);
    }
  });

  // Khi click vào template card
  templateItems.forEach((item) => {
    item.addEventListener("click", function () {
      const templateType = this.dataset.template;
      loadTemplate(templateType);
      templateSelect.value = templateType;
    });
  });
}

/**
 * Load template content
 */
function loadTemplate(templateType) {
  const subjectInput = document.getElementById("emailSubject");
  const editor = document.getElementById("emailEditor");

  if (templateType === "general") {
    subjectInput.value = "Thông báo quan trọng từ Snowboard Shop";
    editor.innerHTML = `
            <p>Kính gửi <strong>{fullname}</strong>,</p>
            <p>Chúng tôi xin gửi đến bạn thông báo quan trọng về...</p>
            <p>[Nội dung thông báo của bạn]</p>
            <p>Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi.</p>
            <p>Trân trọng,<br>Snowboard Shop Team</p>
        `;
  } else if (templateType === "promo") {
    subjectInput.value = "🎉 Khuyến mãi đặc biệt dành cho bạn!";
    editor.innerHTML = `
            <p>Xin chào <strong>{fullname}</strong>,</p>
            <p>Chúng tôi có tin vui dành cho bạn!</p>
            <h3 style="color: #ff6b6b;">🎁 GIẢM GIÁ ĐẶC BIỆT</h3>
            <p><strong>Mã giảm giá:</strong> <code style="font-size: 1.2em; color: #ff6b6b;">PROMO2025</code></p>
            <p><strong>Giảm:</strong> 20%</p>
            <p><strong>Có hiệu lực đến:</strong> 31/12/2025</p>
            <p>Đừng bỏ lỡ cơ hội tuyệt vời này!</p>
            <p><a href="http://localhost/Web_TMDT/view/User/product_list.php" style="background-color: #ff6b6b; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Mua Sắm Ngay</a></p>
        `;
  }

  // Add animation
  editor.style.animation = "fadeIn 0.3s ease";
  setTimeout(() => (editor.style.animation = ""), 300);
}

// ========================================
// SEND EMAIL
// ========================================

/**
 * Khởi tạo gửi email
 */
function initializeSendEmail() {
  const sendBtn = document.getElementById("sendEmailBtn");

  sendBtn.addEventListener("click", function () {
    if (validateForm()) {
      showConfirmDialog();
    }
  });
}

/**
 * Validate form trước khi gửi
 */
function validateForm() {
  const subject = document.getElementById("emailSubject").value.trim();
  const editor = document.getElementById("emailEditor");
  const message = editor.innerText.trim();
  const recipientType = document.querySelector(
    'input[name="recipient_type"]:checked'
  ).value;

  if (!subject) {
    showAlert("Vui lòng nhập tiêu đề email", "warning");
    document.getElementById("emailSubject").focus();
    return false;
  }

  if (!message) {
    showAlert("Vui lòng nhập nội dung email", "warning");
    editor.focus();
    return false;
  }

  if (recipientType === "individual") {
    const selectedUsers = document.querySelectorAll(".user-checkbox:checked");
    if (selectedUsers.length === 0) {
      showAlert("Vui lòng chọn ít nhất một user", "warning");
      return false;
    }
  }

  return true;
}

/**
 * Hiển thị confirm dialog trước khi gửi
 */
function showConfirmDialog() {
  const recipientType = document.querySelector(
    'input[name="recipient_type"]:checked'
  ).value;
  let recipientText = "tất cả users";

  if (recipientType === "individual") {
    const selectedCount = document.querySelectorAll(
      ".user-checkbox:checked"
    ).length;
    recipientText = `${selectedCount} user được chọn`;
  }

  if (confirm(`Bạn có chắc muốn gửi email đến ${recipientText}?`)) {
    sendEmail();
  }
}

/**
 * Gửi email đến server
 */
function sendEmail() {
  const sendBtn = document.getElementById("sendEmailBtn");
  const subject = document.getElementById("emailSubject").value.trim();
  const editor = document.getElementById("emailEditor");
  const message = editor.innerHTML;
  const recipientType = document.querySelector(
    'input[name="recipient_type"]:checked'
  ).value;

  // Disable button và show loading
  sendBtn.disabled = true;
  sendBtn.classList.add("btn-loading");
  sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang gửi...';

  // Prepare form data
  const formData = new FormData();
  formData.append("action", "send_email");
  formData.append("subject", subject);
  formData.append("message", message);
  formData.append("recipient_type", recipientType);

  if (recipientType === "individual") {
    const selectedUsers = [];
    document.querySelectorAll(".user-checkbox:checked").forEach((cb) => {
      selectedUsers.push(cb.value);
    });
    formData.append("user_ids", JSON.stringify(selectedUsers));
  }

  // Send request
  fetch("../../controller/controller_Admin/admin_email_controller.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert(data.message, "success");
        clearForm();
      } else {
        showAlert(data.message, "danger");
      }
    })
    .catch((error) => {
      console.error("Lỗi gửi email:", error);
      showAlert("Lỗi kết nối server. Vui lòng thử lại.", "danger");
    })
    .finally(() => {
      // Restore button
      sendBtn.disabled = false;
      sendBtn.classList.remove("btn-loading");
      sendBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Gửi Email';
    });
}

// ========================================
// PREVIEW
// ========================================

/**
 * Khởi tạo preview
 */
function initializePreview() {
  const previewBtn = document.getElementById("previewBtn");

  previewBtn.addEventListener("click", function () {
    showPreview();
  });
}

/**
 * Hiển thị preview modal
 */
function showPreview() {
  const subject = document.getElementById("emailSubject").value.trim();
  const editor = document.getElementById("emailEditor");
  const message = editor.innerHTML;

  if (!subject || !message) {
    showAlert("Vui lòng nhập tiêu đề và nội dung trước", "warning");
    return;
  }

  // Replace variables với sample data
  const previewContent = message
    .replace(/{fullname}/g, "<strong>Nguyễn Văn A</strong>")
    .replace(/{email}/g, "<em>nguyenvana@example.com</em>");

  const previewDiv = document.getElementById("previewContent");
  previewDiv.innerHTML = `
        <div style="border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden;">
            <div style="background-color: #007bff; color: white; padding: 15px; text-align: center;">
                <h4 style="margin: 0;">${subject}</h4>
            </div>
            <div style="padding: 20px; background-color: white;">
                ${previewContent}
            </div>
        </div>
    `;

  const modal = new bootstrap.Modal(document.getElementById("previewModal"));
  modal.show();
}

// ========================================
// UTILITIES
// ========================================

/**
 * Hiển thị alert floating
 */
function showAlert(message, type = "info") {
  // Remove old alerts
  document.querySelectorAll(".alert-floating").forEach((el) => el.remove());

  const alert = document.createElement("div");
  alert.className = `alert alert-${type} alert-dismissible fade show alert-floating`;
  alert.innerHTML = `
        <i class="fas fa-${
          type === "success"
            ? "check-circle"
            : type === "danger"
            ? "exclamation-circle"
            : "info-circle"
        } me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

  document.body.appendChild(alert);

  // Auto remove sau 5 giây
  setTimeout(() => {
    alert.classList.remove("show");
    setTimeout(() => alert.remove(), 150);
  }, 5000);
}

/**
 * Khởi tạo nút Clear
 */
function initializeClearButton() {
  const clearBtn = document.getElementById("clearBtn");

  clearBtn.addEventListener("click", function () {
    if (confirm("Bạn có chắc muốn xóa toàn bộ nội dung?")) {
      clearForm();
    }
  });
}

/**
 * Clear toàn bộ form
 */
function clearForm() {
  document.getElementById("emailSubject").value = "";
  document.getElementById("emailEditor").innerHTML = "";
  document.getElementById("templateSelect").value = "custom";
  document.getElementById("recipientAll").checked = true;
  document.querySelector(".user-selection-container").style.display = "none";

  // Uncheck all users
  document
    .querySelectorAll(".user-checkbox")
    .forEach((cb) => (cb.checked = false));
  if (document.getElementById("selectAll")) {
    document.getElementById("selectAll").checked = false;
  }
}

// ========================================
// GLOBAL FUNCTIONS (cho inline onclick)
// ========================================

window.insertVariable = insertVariable;
