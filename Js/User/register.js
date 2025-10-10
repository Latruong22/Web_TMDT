document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const passwordInput = document.querySelector('input[name="password"]');
  const confirmPasswordInput = document.querySelector(
    'input[name="confirm_password"]'
  );

  // Kiểm tra mật khẩu mạnh
  function isStrongPassword(password) {
    const minLength = password.length >= 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(
      password
    );

    return (
      minLength && hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar
    );
  }

  // Kiểm tra mật khẩu trùng khớp
  function passwordsMatch(password, confirm) {
    return password === confirm;
  }

  // Xác thực form trước khi submit
  form.addEventListener("submit", function (event) {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    // Kiểm tra mật khẩu mạnh
    if (!isStrongPassword(password)) {
      event.preventDefault();
      alert(
        "Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt!"
      );
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
  const phoneInput = document.querySelector('input[name="phone"]');
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
});
