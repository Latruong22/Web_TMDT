/**
 * ORDER HISTORY PAGE JAVASCRIPT
 * Handles interactions and animations for order history page
 */

document.addEventListener("DOMContentLoaded", function () {
  initBackToTop();
  initOrderCardAnimations();
  initFilterHighlight();
  initTooltips();
});

/**
 * Back to Top Button
 */
function initBackToTop() {
  const backToTopBtn = document.getElementById("backToTopBtn");

  if (!backToTopBtn) return;

  // Show/hide button based on scroll position
  window.addEventListener("scroll", function () {
    if (window.pageYOffset > 300) {
      backToTopBtn.style.opacity = "1";
      backToTopBtn.style.visibility = "visible";
    } else {
      backToTopBtn.style.opacity = "0";
      backToTopBtn.style.visibility = "hidden";
    }
  });

  // Scroll to top on click
  backToTopBtn.addEventListener("click", function (e) {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });
}

/**
 * Order Card Animations
 */
function initOrderCardAnimations() {
  const orderCards = document.querySelectorAll(".order-card");

  // Intersection Observer for scroll animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -100px 0px",
  };

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  }, observerOptions);

  orderCards.forEach((card, index) => {
    card.style.opacity = "0";
    card.style.transform = "translateY(30px)";
    card.style.transition = `all 0.5s ease ${index * 0.1}s`;
    observer.observe(card);
  });
}

/**
 * Filter Tab Highlight
 */
function initFilterHighlight() {
  const filterButtons = document.querySelectorAll(".btn-filter");

  filterButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      // Show loading state
      const icon = this.querySelector("i");
      if (icon) {
        const originalClass = icon.className;
        icon.className = "fas fa-spinner fa-spin";

        // Restore icon after a brief moment
        setTimeout(() => {
          icon.className = originalClass;
        }, 300);
      }
    });

    // Hover effect enhancement
    button.addEventListener("mouseenter", function () {
      if (!this.classList.contains("active")) {
        this.style.transform = "translateY(-2px)";
      }
    });

    button.addEventListener("mouseleave", function () {
      if (!this.classList.contains("active")) {
        this.style.transform = "translateY(0)";
      }
    });
  });
}

/**
 * Initialize Bootstrap Tooltips
 */
function initTooltips() {
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );

  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
}

/**
 * Confirm Cancel Order
 */
function confirmCancelOrder(orderId) {
  return confirm(
    "Bạn có chắc chắn muốn hủy đơn hàng #" +
      orderId +
      "?\n\nHành động này không thể hoàn tác."
  );
}

/**
 * View Order Details (Modal or redirect)
 */
function viewOrderDetails(orderId) {
  window.location.href = `order_tracking.php?order_id=${orderId}`;
}

/**
 * Reorder Function
 */
function reorderItems(orderId) {
  if (confirm("Bạn muốn đặt lại đơn hàng #" + orderId + "?")) {
    window.location.href = `checkout.php?reorder=${orderId}`;
  }
}

/**
 * Filter by Status (with animation)
 */
function filterOrdersByStatus(status) {
  const url = new URL(window.location.href);
  url.searchParams.set("status", status);
  url.searchParams.delete("page"); // Reset to page 1

  // Add loading overlay
  showLoadingOverlay();

  // Redirect with small delay for UX
  setTimeout(() => {
    window.location.href = url.toString();
  }, 300);
}

/**
 * Show Loading Overlay
 */
function showLoadingOverlay() {
  const overlay = document.createElement("div");
  overlay.className = "loading-overlay";
  overlay.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Đang tải...</span>
        </div>
    `;
  overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;
  document.body.appendChild(overlay);
}

/**
 * Format Currency
 */
function formatCurrency(amount) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
}

/**
 * Copy Order ID to Clipboard
 */
function copyOrderId(orderId) {
  const textArea = document.createElement("textarea");
  textArea.value = orderId;
  textArea.style.position = "fixed";
  textArea.style.left = "-9999px";
  document.body.appendChild(textArea);
  textArea.select();

  try {
    document.execCommand("copy");
    showNotification("Đã sao chép mã đơn hàng #" + orderId, "success");
  } catch (err) {
    showNotification("Không thể sao chép", "error");
  }

  document.body.removeChild(textArea);
}

/**
 * Show Notification Toast
 */
function showNotification(message, type = "info") {
  const toast = document.createElement("div");
  toast.className = `alert alert-${type} notification-toast`;
  toast.textContent = message;
  toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 250px;
        animation: slideInRight 0.3s ease-out;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    `;

  document.body.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = "slideOutRight 0.3s ease-in";
    setTimeout(() => {
      document.body.removeChild(toast);
    }, 300);
  }, 3000);
}

/**
 * Smooth Scroll to Element
 */
function smoothScrollTo(elementId) {
  const element = document.getElementById(elementId);
  if (element) {
    element.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }
}

/**
 * Print Order Details
 */
function printOrder(orderId) {
  window.print();
}

/**
 * Share Order (via Web Share API)
 */
function shareOrder(orderId) {
  if (navigator.share) {
    navigator
      .share({
        title: "Đơn hàng #" + orderId,
        text: "Xem chi tiết đơn hàng của tôi",
        url: window.location.href,
      })
      .then(() => {
        console.log("Shared successfully");
      })
      .catch((err) => {
        console.log("Share failed:", err);
      });
  } else {
    // Fallback: Copy link
    copyOrderId(orderId);
  }
}

/**
 * Pagination Animation
 */
document.querySelectorAll(".pagination .page-link").forEach((link) => {
  link.addEventListener("click", function (e) {
    if (
      !this.parentElement.classList.contains("disabled") &&
      !this.parentElement.classList.contains("active")
    ) {
      showLoadingOverlay();
    }
  });
});

/**
 * Add keyboard shortcuts
 */
document.addEventListener("keydown", function (e) {
  // Ctrl/Cmd + P: Print
  if ((e.ctrlKey || e.metaKey) && e.key === "p") {
    e.preventDefault();
    window.print();
  }

  // Escape: Cuộn lên đầu trang
  if (e.key === "Escape") {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  }
});

/**
 * Add CSS for animations
 */
const style = document.createElement("style");
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-toast {
        animation: slideInRight 0.3s ease-out;
    }
`;
document.head.appendChild(style);

/**
 * Log page view (optional analytics)
 */
console.log("Order History Page Loaded");
console.log(
  "Total orders displayed:",
  document.querySelectorAll(".order-card").length
);
