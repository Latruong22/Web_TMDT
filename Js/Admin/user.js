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

  // Initialize UserFilterManager
  if (document.getElementById("userFilterForm")) {
    window.userFilterManager = new UserFilterManager();
  }
});

// ========== MODERN USER FILTER MANAGER ==========
class UserFilterManager {
  constructor() {
    this.form = document.getElementById("userFilterForm");
    this.searchInput = document.getElementById("searchInput");
    this.selectInputs = this.form.querySelectorAll(".filter-select");
    this.dateInputs = this.form.querySelectorAll(".filter-date");
    this.debounceDelay = 500;
    this.debounceTimer = null;

    this.statusLabels = {
      active: "Đang hoạt động",
      pending: "Chờ kích hoạt",
      locked: "Đang bị khóa",
    };

    this.roleLabels = {
      user: "Khách hàng",
      admin: "Quản trị",
    };

    this.init();
  }

  init() {
    this.attachEventListeners();
    this.updateActiveFilters();

    // Restore filter panel state from localStorage
    const isCollapsed = localStorage.getItem("userFilterCollapsed") === "true";
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
    localStorage.setItem("userFilterCollapsed", isCollapsed);
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
        <span class="remove-chip" onclick="userFilterManager.removeFilter('${filter.key}')">&times;</span>
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

    // Role filter
    const role = formData.get("role");
    if (role && role !== "all") {
      filters.push({
        key: "role",
        label: "Vai trò",
        value: this.roleLabels[role] || role,
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
    if (key === "status" || key === "role") {
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
    this.form.querySelector('[name="role"]').value = "all";
    this.searchInput.value = "";
    this.form.querySelector('[name="from"]').value = "";
    this.form.querySelector('[name="to"]').value = "";

    // Submit form
    this.submitForm();
  }
}
