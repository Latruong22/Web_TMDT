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

  document.querySelectorAll(".order-update-form").forEach((form) => {
    form.addEventListener("submit", (event) => {
      const status = form.querySelector("select[name='status']").value;
      const message =
        status === "cancelled"
          ? "Xác nhận hủy đơn hàng này?"
          : status === "delivered"
          ? "Đánh dấu đơn hàng đã giao?"
          : "Cập nhật trạng thái đơn hàng?";
      if (!confirm(message)) {
        event.preventDefault();
      }
    });
  });
});
