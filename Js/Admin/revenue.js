document.addEventListener("DOMContentLoaded", () => {
  const rangeSelect = document.getElementById("range-select");
  const fromInput = document.getElementById("from-date");
  const toInput = document.getElementById("to-date");
  const form = document.getElementById("revenue-filter-form");

  const toggleDateInputs = () => {
    const isCustom = rangeSelect.value === "custom";
    [fromInput, toInput].forEach((input) => {
      input.disabled = !isCustom;
      input.required = isCustom;
    });
  };

  toggleDateInputs();
  rangeSelect.addEventListener("change", toggleDateInputs);

  const activateCustomRange = () => {
    if (rangeSelect.value !== "custom") {
      rangeSelect.value = "custom";
      toggleDateInputs();
    }
  };

  [fromInput, toInput].forEach((input) => {
    if (!input) return;
    input.addEventListener("focus", activateCustomRange);
    input.addEventListener("input", activateCustomRange);
  });

  if (form) {
    form.addEventListener("submit", (event) => {
      if (rangeSelect.value === "custom") {
        const fromValue = fromInput.value;
        const toValue = toInput.value;

        if (!fromValue || !toValue) {
          alert("Vui lòng chọn đầy đủ khoảng thời gian tùy chọn.");
          event.preventDefault();
          return;
        }

        if (fromValue > toValue) {
          alert("Ngày bắt đầu không được lớn hơn ngày kết thúc.");
          event.preventDefault();
        }
      }
    });
  }
});
