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

  // Initialize ReviewFilterManager
  if (document.getElementById("reviewFilterForm")) {
    window.reviewFilterManager = new ReviewFilterManager();
  }
});

// ========== MODERN REVIEW FILTER MANAGER ==========
class ReviewFilterManager {
  constructor() {
    this.form = document.getElementById("reviewFilterForm");
    this.searchInput = document.getElementById("searchInput");
    this.selectInputs = this.form.querySelectorAll(".filter-select");
    this.dateInputs = this.form.querySelectorAll(".filter-date");
    this.debounceDelay = 500;
    this.debounceTimer = null;

    this.statusLabels = {
      all: "Tất cả",
      pending: "Chờ duyệt",
      approved: "Đã duyệt",
      rejected: "Bị từ chối",
    };

    this.init();
  }

  init() {
    this.attachEventListeners();
    this.updateActiveFilters();

    // Restore filter panel state from localStorage
    const isCollapsed =
      localStorage.getItem("reviewFilterCollapsed") === "true";
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

    // Auto-submit on date change
    this.dateInputs.forEach((input) => {
      input.addEventListener("change", () => {
        this.submitForm();
      });
    });
  }

  toggleFilterPanel() {
    const filterBody = document.getElementById("filterBody");
    const filterHeader = document.querySelector(".filter-header");
    const isCollapsed = filterBody.classList.toggle("collapsed");
    filterHeader.classList.toggle("collapsed");

    // Save state to localStorage
    localStorage.setItem("reviewFilterCollapsed", isCollapsed);
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
        <span class="remove-chip" onclick="reviewFilterManager.removeFilter('${filter.key}')">&times;</span>
      </div>`
      )
      .join("");
  }

  getActiveFilters() {
    const filters = [];
    const formData = new FormData(this.form);

    // Status filter
    const status = formData.get("status");
    if (status && status !== "all") {
      filters.push({
        key: "status",
        label: "Trạng thái",
        value: this.statusLabels[status] || status,
      });
    }

    // Rating filter
    const rating = formData.get("rating");
    if (rating && rating !== "all") {
      filters.push({
        key: "rating",
        label: "Đánh giá",
        value: rating + " sao",
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

    // Date from filter
    const from = formData.get("from");
    if (from) {
      filters.push({
        key: "from",
        label: "Từ ngày",
        value: new Date(from).toLocaleDateString("vi-VN"),
      });
    }

    // Date to filter
    const to = formData.get("to");
    if (to) {
      filters.push({
        key: "to",
        label: "Đến ngày",
        value: new Date(to).toLocaleDateString("vi-VN"),
      });
    }

    return filters;
  }

  removeFilter(key) {
    // Reset the specific filter
    if (key === "status" || key === "rating") {
      this.form.querySelector(`[name="${key}"]`).value = "all";
    } else if (key === "search") {
      this.searchInput.value = "";
    } else if (key === "from" || key === "to") {
      this.form.querySelector(`[name="${key}"]`).value = "";
    }

    // Submit form to apply changes
    this.submitForm();
  }

  clearAllFilters() {
    // Reset all filters to default
    this.form.querySelector('[name="status"]').value = "all";
    this.form.querySelector('[name="rating"]').value = "all";
    this.searchInput.value = "";
    this.form.querySelector('[name="from"]').value = "";
    this.form.querySelector('[name="to"]').value = "";

    // Submit form
    this.submitForm();
  }
}
