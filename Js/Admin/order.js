// ============================================
// ORDER MANAGEMENT - ENHANCED VERSION
// ============================================

document.addEventListener("DOMContentLoaded", () => {
  // Original functionality - Toggle order details
  const detailButtons = document.querySelectorAll(".toggle-details");
  detailButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const targetId = button.dataset.target;
      const row = document.getElementById(targetId);
      if (!row) return;
      row.classList.toggle("open");

      const icon = button.querySelector("i");
      if (row.classList.contains("open")) {
        button.innerHTML = '<i class="fas fa-eye-slash me-1"></i>Ẩn bớt';
      } else {
        button.innerHTML = '<i class="fas fa-eye me-1"></i>Chi tiết';
      }
    });
  });

  // Original functionality - Confirm order status update
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

  // ============================================
  // NEW: MODERN FILTER FUNCTIONALITY
  // ============================================

  const filterForm = document.getElementById("orderFilterForm");
  if (!filterForm) return; // Exit if no filter form

  const filterManager = new OrderFilterManager(filterForm);
});

// ============================================
// ORDER FILTER MANAGER CLASS
// ============================================
class OrderFilterManager {
  constructor(form) {
    this.form = form;
    this.searchInput = form.querySelector(".filter-search");
    this.selectInputs = form.querySelectorAll(".filter-select");
    this.dateInputs = form.querySelectorAll(".filter-date");
    this.searchSpinner = document.getElementById("searchSpinner");
    this.searchIcon = document.getElementById("searchIcon");
    this.filterLoading = document.getElementById("filterLoading");
    this.activeFiltersDiv = document.getElementById("activeFilters");
    this.activeFilterCount = document.getElementById("activeFilterCount");

    this.debounceTimeout = null;
    this.debounceDelay = 500; // 500ms delay for search

    this.statusLabels = {
      pending: "Chờ xác nhận",
      confirmed: "Đã xác nhận",
      shipping: "Đang giao",
      delivered: "Đã giao",
      cancelled: "Đã hủy",
    };

    this.init();
  }

  init() {
    this.loadCollapsedState();
    this.attachEventListeners();
    this.updateActiveFilters();
  }

  attachEventListeners() {
    // Toggle filter panel
    const toggleBtn = document.getElementById("toggleFilters");
    if (toggleBtn) {
      toggleBtn.addEventListener("click", () => this.toggleFilterPanel());
    }

    // Debounced search input
    if (this.searchInput) {
      this.searchInput.addEventListener("input", () => {
        this.showSearchSpinner();
        clearTimeout(this.debounceTimeout);

        this.debounceTimeout = setTimeout(() => {
          this.hideSearchSpinner();
          this.submitForm();
        }, this.debounceDelay);
      });
    }

    // Auto-submit on dropdown/date change
    this.selectInputs.forEach((select) => {
      select.addEventListener("change", () => this.submitForm());
    });

    this.dateInputs.forEach((date) => {
      date.addEventListener("change", () => this.submitForm());
    });

    // Prevent default form submit (we handle it manually)
    this.form.addEventListener("submit", (e) => {
      e.preventDefault();
      this.submitForm();
    });
  }

  toggleFilterPanel() {
    const filterBody = document.getElementById("filterBody");
    const toggleIcon = document.querySelector("#toggleFilters i");

    filterBody.classList.toggle("collapsed");

    if (filterBody.classList.contains("collapsed")) {
      toggleIcon.classList.remove("fa-chevron-up");
      toggleIcon.classList.add("fa-chevron-down");
      localStorage.setItem("orderFilterCollapsed", "true");
    } else {
      toggleIcon.classList.remove("fa-chevron-down");
      toggleIcon.classList.add("fa-chevron-up");
      localStorage.setItem("orderFilterCollapsed", "false");
    }
  }

  loadCollapsedState() {
    const isCollapsed = localStorage.getItem("orderFilterCollapsed") === "true";
    if (isCollapsed) {
      const filterBody = document.getElementById("filterBody");
      const toggleIcon = document.querySelector("#toggleFilters i");

      filterBody.classList.add("collapsed");
      toggleIcon.classList.remove("fa-chevron-up");
      toggleIcon.classList.add("fa-chevron-down");
    }
  }

  showSearchSpinner() {
    if (this.searchSpinner) this.searchSpinner.style.display = "block";
    if (this.searchIcon) this.searchIcon.style.display = "none";
  }

  hideSearchSpinner() {
    if (this.searchSpinner) this.searchSpinner.style.display = "none";
    if (this.searchIcon) this.searchIcon.style.display = "block";
  }

  submitForm() {
    // Show loading overlay
    if (this.filterLoading) {
      this.filterLoading.style.display = "flex";
    }

    // Submit the form
    this.form.submit();
  }

  updateActiveFilters() {
    const activeFilters = this.getActiveFilters();
    const count = activeFilters.length;

    // Update badge count
    if (this.activeFilterCount) {
      if (count > 0) {
        this.activeFilterCount.textContent = count;
        this.activeFilterCount.style.display = "inline-block";
      } else {
        this.activeFilterCount.style.display = "none";
      }
    }

    // Render filter chips
    if (this.activeFiltersDiv) {
      if (count > 0) {
        this.activeFiltersDiv.style.display = "block";
        this.renderFilterChips(activeFilters);
      } else {
        this.activeFiltersDiv.style.display = "none";
      }
    }
  }

  getActiveFilters() {
    const filters = [];
    const formData = new FormData(this.form);

    formData.forEach((value, key) => {
      if (value && value !== "all" && value !== "") {
        filters.push({ key, value });
      }
    });

    return filters;
  }

  renderFilterChips(filters) {
    const chipsHTML = filters
      .map((f) => {
        const label = this.getFilterLabel(f.key, f.value);
        return `
        <span class="filter-chip">
          <strong>${label.label}:</strong> ${label.value}
          <button type="button" class="btn-close" data-filter="${f.key}" aria-label="Xóa bộ lọc"></button>
        </span>
      `;
      })
      .join("");

    const clearAllBtn = `
      <button type="button" class="btn btn-sm btn-outline-danger" id="clearAllFilters">
        <i class="fas fa-times me-1"></i>Xóa tất cả
      </button>
    `;

    this.activeFiltersDiv.innerHTML = chipsHTML + clearAllBtn;

    // Attach event listeners to remove buttons
    this.activeFiltersDiv.querySelectorAll(".btn-close").forEach((btn) => {
      btn.addEventListener("click", () => {
        const filterKey = btn.dataset.filter;
        this.removeFilter(filterKey);
      });
    });

    // Attach event listener to clear all button
    const clearBtn = document.getElementById("clearAllFilters");
    if (clearBtn) {
      clearBtn.addEventListener("click", () => this.clearAllFilters());
    }
  }

  getFilterLabel(key, value) {
    const labels = {
      status: "Trạng thái",
      search: "Tìm kiếm",
      from: "Từ ngày",
      to: "Đến ngày",
    };

    let displayValue = value;

    if (key === "status" && this.statusLabels[value]) {
      displayValue = this.statusLabels[value];
    } else if (key === "from" || key === "to") {
      // Format date to dd/mm/yyyy
      const date = new Date(value);
      displayValue = date.toLocaleDateString("vi-VN");
    }

    return {
      label: labels[key] || key,
      value: displayValue,
    };
  }

  removeFilter(key) {
    const input = this.form.querySelector(`[name="${key}"]`);
    if (input) {
      if (input.tagName === "SELECT") {
        input.value = "all";
      } else {
        input.value = "";
      }
      this.submitForm();
    }
  }

  clearAllFilters() {
    // Reset all inputs
    this.form.querySelectorAll("input, select").forEach((input) => {
      if (input.tagName === "SELECT") {
        input.value = "all";
      } else {
        input.value = "";
      }
    });

    this.submitForm();
  }
}
