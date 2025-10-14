document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector(
    'form[action*="admin_product_controller.php"]'
  );
  if (form) {
    form.addEventListener("submit", function (e) {
      const name = form.querySelector('input[name="name"]').value.trim();
      const price = parseFloat(form.querySelector('input[name="price"]').value);
      const discountInput = form.querySelector('input[name="manual_discount"]');
      const discount = discountInput
        ? parseFloat(discountInput.value || "0")
        : 0;
      const stock = parseInt(
        form.querySelector('input[name="stock"]').value,
        10
      );
      const category = form.querySelector('select[name="category_id"]').value;
      const action = form.querySelector('input[name="action"]').value;
      const imageInput = form.querySelector('input[name="image"]');

      let errors = [];
      if (name.length === 0) errors.push("Vui lòng nhập tên sản phẩm");
      if (isNaN(price) || price < 0) errors.push("Giá không hợp lệ");
      if (isNaN(discount) || discount < 0 || discount > 100)
        errors.push("Khuyến mãi phải từ 0 đến 100");
      if (isNaN(stock) || stock < 0) errors.push("Tồn kho không hợp lệ");
      if (!category) errors.push("Vui lòng chọn danh mục");
      if (action === "add" && imageInput && imageInput.files.length === 0)
        errors.push("Vui lòng chọn ảnh sản phẩm");

      if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join("\n"));
      }
    });
  }

  // Initialize ProductFilterManager
  if (document.getElementById("productFilterForm")) {
    window.productFilterManager = new ProductFilterManager();
  }
});

// ========== MODERN PRODUCT FILTER MANAGER ==========
class ProductFilterManager {
  constructor() {
    this.form = document.getElementById("productFilterForm");
    this.searchInput = document.getElementById("searchInput");
    this.selectInputs = this.form.querySelectorAll(".filter-select");
    this.priceInputs = this.form.querySelectorAll(".filter-price");
    this.debounceDelay = 500;
    this.debounceTimer = null;

    this.statusLabels = {
      active: "Đang bán",
      inactive: "Ngừng bán",
    };

    this.init();
  }

  init() {
    this.attachEventListeners();
    this.updateActiveFilters();

    // Restore filter panel state from localStorage
    const isCollapsed =
      localStorage.getItem("productFilterCollapsed") === "true";
    if (isCollapsed) {
      document.getElementById("filterBody").classList.add("collapsed");
      document.querySelector(".filter-header").classList.add("collapsed");
    }
  }

  attachEventListeners() {
    // Debounced search
    if (this.searchInput) {
      this.searchInput.addEventListener("input", () => {
        document.getElementById("searchSpinner").style.display = "flex";

        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
          this.submitForm();
        }, this.debounceDelay);
      });
    }

    // Auto-submit on dropdown change
    this.selectInputs.forEach((select) => {
      select.addEventListener("change", () => {
        this.submitForm();
      });
    });

    // Debounced price input
    this.priceInputs.forEach((input) => {
      input.addEventListener("input", () => {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
          this.submitForm();
        }, this.debounceDelay);
      });
    });
  }

  toggleFilterPanel() {
    const filterBody = document.getElementById("filterBody");
    const filterHeader = document.querySelector(".filter-header");
    const isCollapsed = filterBody.classList.toggle("collapsed");
    filterHeader.classList.toggle("collapsed");

    // Save state to localStorage
    localStorage.setItem("productFilterCollapsed", isCollapsed);
  }

  submitForm() {
    document.getElementById("filterLoading").style.display = "flex";
    this.form.submit();
  }

  updateActiveFilters() {
    const filters = this.getActiveFilters();
    const container = document.getElementById("activeFilters");
    const badge = document.getElementById("filterBadge");

    badge.textContent = filters.length;

    if (filters.length === 0) {
      container.innerHTML = "";
      container.style.display = "none";
      return;
    }

    container.style.display = "flex";
    container.innerHTML = filters
      .map(
        (filter) =>
          `<div class="filter-chip">
        <span>${filter.label}: <strong>${filter.value}</strong></span>
        <span class="remove-chip" onclick="productFilterManager.removeFilter('${filter.key}')">&times;</span>
      </div>`
      )
      .join("");
  }

  getActiveFilters() {
    const filters = [];
    const formData = new FormData(this.form);

    // Category filter
    const category = formData.get("category");
    if (category && category !== "all") {
      const categorySelect = this.form.querySelector('[name="category"]');
      const selectedOption =
        categorySelect.options[categorySelect.selectedIndex];
      filters.push({
        key: "category",
        label: "Danh mục",
        value: selectedOption.text,
      });
    }

    // Status filter
    const status = formData.get("status");
    if (status && status !== "all") {
      filters.push({
        key: "status",
        label: "Trạng thái",
        value: this.statusLabels[status] || status,
      });
    }

    // Search filter
    const search = formData.get("search");
    if (search && search.trim()) {
      filters.push({
        key: "search",
        label: "Tìm kiếm",
        value: search.trim(),
      });
    }

    // Price min filter
    const priceMin = formData.get("price_min");
    if (priceMin && priceMin.trim()) {
      filters.push({
        key: "price_min",
        label: "Giá từ",
        value: new Intl.NumberFormat("vi-VN", {
          style: "currency",
          currency: "VND",
        }).format(priceMin),
      });
    }

    // Price max filter
    const priceMax = formData.get("price_max");
    if (priceMax && priceMax.trim()) {
      filters.push({
        key: "price_max",
        label: "Giá đến",
        value: new Intl.NumberFormat("vi-VN", {
          style: "currency",
          currency: "VND",
        }).format(priceMax),
      });
    }

    return filters;
  }

  removeFilter(key) {
    // Reset the specific filter
    if (key === "category" || key === "status") {
      this.form.querySelector(`[name="${key}"]`).value = "all";
    } else if (key === "search") {
      this.searchInput.value = "";
    } else if (key === "price_min" || key === "price_max") {
      this.form.querySelector(`[name="${key}"]`).value = "";
    }

    // Submit form to apply changes
    this.submitForm();
  }

  clearAllFilters() {
    // Reset all filters to default
    this.form.querySelector('[name="category"]').value = "all";
    this.form.querySelector('[name="status"]').value = "all";
    this.searchInput.value = "";
    this.form.querySelector('[name="price_min"]').value = "";
    this.form.querySelector('[name="price_max"]').value = "";

    // Submit form
    this.submitForm();
  }
}
