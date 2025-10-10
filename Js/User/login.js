document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const emailInput = document.querySelector('input[name="email"]');
  const passwordInput = document.querySelector('input[name="password"]');

  // Kiểm tra định dạng email
  emailInput.addEventListener("blur", function () {
    const email = emailInput.value;
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

    if (!emailRegex.test(email)) {
      emailInput.setCustomValidity("Email không hợp lệ");
    } else {
      emailInput.setCustomValidity("");
    }
  });

  // Hiển thị/ẩn thông báo lỗi sau 5 giây
  const messages = document.querySelectorAll(
    ".success-msg, .error-msg, .info-msg"
  );
  if (messages.length > 0) {
    setTimeout(function () {
      messages.forEach(function (message) {
        message.style.opacity = "0";
        setTimeout(function () {
          message.style.display = "none";
        }, 1000);
      });
    }, 5000);
  }

  // Lưu email trong local storage nếu chọn "Ghi nhớ đăng nhập"
  const rememberCheckbox = document.querySelector("#remember");

  // Lấy email đã lưu (nếu có)
  const savedEmail = localStorage.getItem("savedEmail");
  if (savedEmail) {
    emailInput.value = savedEmail;
    rememberCheckbox.checked = true;
  }

  form.addEventListener("submit", function () {
    if (rememberCheckbox.checked) {
      localStorage.setItem("savedEmail", emailInput.value);
    } else {
      localStorage.removeItem("savedEmail");
    }
  });
});
