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

  // NEW: Initialize status lock for delivered/cancelled orders
  initializeStatusLock();

  // Original functionality - Confirm order status update (ENHANCED)
  document.querySelectorAll(".order-update-form").forEach((form) => {
    form.addEventListener("submit", (event) => {
      const statusSelect = form.querySelector("select[name='status']");
      const status = statusSelect.value;
      const currentStatus =
        statusSelect.dataset.currentStatus ||
        statusSelect.querySelector("option[selected]")?.value;

      // Check if trying to change from locked status
      if (
        (currentStatus === "delivered" || currentStatus === "cancelled") &&
        status !== currentStatus
      ) {
        event.preventDefault();
        alert("⚠️ Không thể thay đổi trạng thái đơn hàng đã giao hoặc đã hủy!");
        return;
      }

      let message = "Cập nhật trạng thái đơn hàng?";
      let extraWarning = "";

      if (status === "cancelled") {
        message = "⚠️ Xác nhận HỦY đơn hàng này?";
        extraWarning =
          "\n\nLưu ý: Sau khi hủy, không thể thay đổi trạng thái nữa. Khách hàng sẽ nhận được email thông báo.";
      } else if (status === "delivered") {
        message = "✓ Xác nhận đơn hàng ĐÃ GIAO?";
        extraWarning =
          "\n\nLưu ý: Sau khi đánh dấu đã giao, không thể thay đổi trạng thái nữa.";
      }

      if (!confirm(message + extraWarning)) {
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

// ============================================
// STATUS LOCK FUNCTIONALITY
// ============================================
/**
 * Khởi tạo khóa trạng thái cho đơn hàng đã giao hoặc đã hủy
 * Khi trạng thái là 'delivered' hoặc 'cancelled', disable các option khác
 */
function initializeStatusLock() {
  const allForms = document.querySelectorAll(".order-update-form");

  allForms.forEach((form) => {
    const statusSelect = form.querySelector('select[name="status"]');
    if (!statusSelect) return;

    // Lưu trạng thái hiện tại
    const currentStatus = statusSelect.value;
    statusSelect.dataset.currentStatus = currentStatus;

    // Nếu trạng thái là delivered hoặc cancelled, khóa form
    if (currentStatus === "delivered" || currentStatus === "cancelled") {
      lockOrderStatus(form, statusSelect, currentStatus);
    }

    // Lắng nghe sự kiện thay đổi
    statusSelect.addEventListener("change", function (e) {
      const newStatus = this.value;
      const oldStatus = this.dataset.currentStatus;

      // Nếu đang thay đổi TỪ delivered/cancelled
      if (
        (oldStatus === "delivered" || oldStatus === "cancelled") &&
        newStatus !== oldStatus
      ) {
        e.preventDefault();
        alert("⚠️ Không thể thay đổi trạng thái đơn hàng đã giao hoặc đã hủy!");
        this.value = oldStatus; // Reset về trạng thái cũ
        return;
      }

      // Nếu đang thay đổi SANG delivered/cancelled, hiển thị cảnh báo
      if (newStatus === "delivered" || newStatus === "cancelled") {
        const warning =
          newStatus === "delivered"
            ? "⚠️ Lưu ý: Sau khi đánh dấu ĐÃ GIAO, bạn sẽ không thể thay đổi trạng thái nữa!"
            : "⚠️ Lưu ý: Sau khi HỦY đơn, bạn sẽ không thể thay đổi trạng thái nữa!\n\nKhách hàng sẽ nhận được email thông báo đơn hàng đã bị hủy.";

        // Hiển thị warning badge
        showStatusWarning(form, warning);
      } else {
        // Xóa warning nếu chọn trạng thái khác
        removeStatusWarning(form);
      }
    });
  });
}

/**
 * Khóa form cập nhật trạng thái
 */
function lockOrderStatus(form, statusSelect, lockedStatus) {
  // Disable tất cả options khác
  const allOptions = statusSelect.querySelectorAll("option");
  allOptions.forEach((option) => {
    if (option.value !== lockedStatus) {
      option.disabled = true;
    }
  });

  // Thêm tooltip/notice
  const lockMessage =
    lockedStatus === "delivered"
      ? "🔒 Đơn hàng đã giao - không thể thay đổi trạng thái"
      : "🔒 Đơn hàng đã hủy - không thể thay đổi trạng thái";

  // Thêm badge thông báo
  const existingBadge = form.querySelector(".status-lock-badge");
  if (!existingBadge) {
    const badge = document.createElement("div");
    badge.className = "alert alert-info status-lock-badge mt-2 mb-2";
    badge.style.cssText = "font-size: 0.875rem; padding: 0.5rem;";
    badge.innerHTML = `<i class="fas fa-lock me-2"></i>${lockMessage}`;

    statusSelect.parentElement.appendChild(badge);
  }

  // Disable submit button
  const submitBtn = form.querySelector('button[type="submit"]');
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.classList.add("disabled");
    submitBtn.title = "Không thể thay đổi trạng thái đã khóa";
  }

  // Disable các trường khác
  const textareas = form.querySelectorAll("textarea");
  textareas.forEach((textarea) => {
    textarea.disabled = true;
    textarea.style.backgroundColor = "#f8f9fa";
  });
}

/**
 * Hiển thị warning khi chọn delivered/cancelled
 */
function showStatusWarning(form, message) {
  // Xóa warning cũ nếu có
  removeStatusWarning(form);

  const badge = document.createElement("div");
  badge.className = "alert alert-warning status-warning-badge mt-2 mb-2";
  badge.style.cssText = "font-size: 0.875rem; padding: 0.5rem;";
  badge.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${message.replace(
    /\n/g,
    "<br>"
  )}`;

  const statusSelect = form.querySelector('select[name="status"]');
  statusSelect.parentElement.appendChild(badge);
}

/**
 * Xóa warning badge
 */
function removeStatusWarning(form) {
  const existingWarning = form.querySelector(".status-warning-badge");
  if (existingWarning) {
    existingWarning.remove();
  }
}
