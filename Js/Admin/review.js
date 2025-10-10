document.addEventListener("DOMContentLoaded", () => {
  const confirmables = document.querySelectorAll("[data-confirm]");
  confirmables.forEach((element) => {
    element.addEventListener("submit", (event) => {
      const message = element.getAttribute("data-confirm");
      if (
        !window.confirm(
          message || "Bạn có chắc chắn muốn thực hiện thao tác này?"
        )
      ) {
        event.preventDefault();
      }
    });
  });

  const deleteLinks = document.querySelectorAll("[data-confirm-delete]");
  deleteLinks.forEach((link) => {
    link.addEventListener("click", (event) => {
      const message = link.getAttribute("data-confirm-delete");
      if (
        !window.confirm(message || "Bạn có chắc chắn muốn xóa đánh giá này?")
      ) {
        event.preventDefault();
      }
    });
  });
});
