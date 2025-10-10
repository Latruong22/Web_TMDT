document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("[data-confirm]").forEach((element) => {
    element.addEventListener("click", (event) => {
      const message =
        element.getAttribute("data-confirm") || "Xác nhận thao tác này?";
      if (!confirm(message)) {
        event.preventDefault();
      }
    });
  });

  document.querySelectorAll("form.inline-form").forEach((form) => {
    form.addEventListener("submit", (event) => {
      const button = form.querySelector("button[data-confirm]");
      if (button) {
        const message =
          button.getAttribute("data-confirm") || "Xác nhận thao tác này?";
        if (!confirm(message)) {
          event.preventDefault();
        }
      }
    });
  });
});
