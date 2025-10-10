document.addEventListener("DOMContentLoaded", () => {
  const detailButtons = document.querySelectorAll(".toggle-details");
  detailButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const targetId = button.dataset.target;
      const row = document.getElementById(targetId);
      if (!row) return;
      row.classList.toggle("open");
      button.textContent = row.classList.contains("open")
        ? "Ẩn bớt"
        : "Chi tiết";
    });
  });

  document.querySelectorAll("form.inline-form").forEach((form) => {
    form.addEventListener("submit", (event) => {
      const action = form.querySelector("input[name='action']")?.value;
      let message = "Xác nhận thao tác?";
      if (action === "update_status") {
        const status = form.querySelector("select[name='status']")?.value;
        if (status === "locked") {
          message = "Bạn chắc chắn muốn khóa tài khoản này?";
        } else if (status === "pending") {
          message = "Chuyển người dùng về trạng thái chờ kích hoạt?";
        } else {
          message = "Mở khóa và kích hoạt tài khoản này?";
        }
      } else if (action === "update_role") {
        const role = form.querySelector("select[name='role']")?.value;
        message =
          role === "admin"
            ? "Thêm quyền quản trị cho người dùng này?"
            : "Chuyển người dùng về vai trò khách hàng?";
      } else if (action === "reset_password") {
        message = "Đặt lại mật khẩu và tạo mật khẩu tạm thời mới?";
      }

      if (!confirm(message)) {
        event.preventDefault();
      }
    });
  });
});
