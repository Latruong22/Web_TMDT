/**
 * ORDER TRACKING PAGE JAVASCRIPT
 * Handles interactions and animations for order tracking page
 */

document.addEventListener("DOMContentLoaded", function () {
  initBackToTop();
  initTimelineAnimations();
  updateCartBadge();
  initPrintButton();
  initCopyOrderId();
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
 * Timeline Animations
 */
function initTimelineAnimations() {
  const timelineItems = document.querySelectorAll(".timeline-item");

  // Intersection Observer for scroll animations
  const observerOptions = {
    threshold: 0.2,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateX(0)";
      }
    });
  }, observerOptions);

  timelineItems.forEach((item, index) => {
    // Only animate non-completed items
    if (!item.classList.contains("completed")) {
      item.style.transform = "translateX(-30px)";
      item.style.transition = `all 0.5s ease ${index * 0.1}s`;
      observer.observe(item);
    }
  });
}

/**
 * Update Cart Badge Count
 */
function updateCartBadge() {
  const cartBadge = document.getElementById("cartBadge");
  if (!cartBadge) return;

  // Get cart count from localStorage or API
  fetch("../../controller/controller_User/cart_api.php?action=get_count")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        cartBadge.textContent = data.count;
        if (data.count > 0) {
          cartBadge.classList.remove("d-none");
        } else {
          cartBadge.classList.add("d-none");
        }
      }
    })
    .catch((error) => {
      console.log("Error fetching cart count:", error);
      // Fallback to localStorage
      const cart = JSON.parse(localStorage.getItem("cart") || "[]");
      cartBadge.textContent = cart.length;
    });
}

/**
 * Print Button Enhancement
 */
function initPrintButton() {
  // Add keyboard shortcut
  document.addEventListener("keydown", function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === "p") {
      e.preventDefault();
      window.print();
    }
  });

  // Show print confirmation
  window.addEventListener("beforeprint", function () {
    console.log("Preparing to print order...");
  });

  window.addEventListener("afterprint", function () {
    console.log("Print completed or cancelled");
  });
}

/**
 * Copy Order ID to Clipboard
 */
function initCopyOrderId() {
  const orderTitle = document.querySelector(".page-title");
  if (!orderTitle) return;

  // Add click to copy functionality
  orderTitle.style.cursor = "pointer";
  orderTitle.title = "Click để sao chép mã đơn hàng";

  orderTitle.addEventListener("click", function () {
    const orderId = this.textContent.trim();
    copyToClipboard(orderId);
  });
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard
      .writeText(text)
      .then(() => {
        showNotification("Đã sao chép " + text, "success");
      })
      .catch((err) => {
        console.error("Copy failed:", err);
        fallbackCopy(text);
      });
  } else {
    fallbackCopy(text);
  }
}

/**
 * Fallback copy method for older browsers
 */
function fallbackCopy(text) {
  const textArea = document.createElement("textarea");
  textArea.value = text;
  textArea.style.position = "fixed";
  textArea.style.left = "-9999px";
  document.body.appendChild(textArea);
  textArea.select();

  try {
    document.execCommand("copy");
    showNotification("Đã sao chép " + text, "success");
  } catch (err) {
    showNotification("Không thể sao chép", "error");
  }

  document.body.removeChild(textArea);
}

/**
 * Show Notification Toast
 */
function showNotification(message, type = "info") {
  // Remove existing toast if any
  const existingToast = document.querySelector(".notification-toast");
  if (existingToast) {
    existingToast.remove();
  }

  const toast = document.createElement("div");
  toast.className = `alert alert-${type} notification-toast`;
  toast.innerHTML = `
        <i class="fas fa-${
          type === "success" ? "check-circle" : "exclamation-circle"
        } me-2"></i>
        ${message}
    `;
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
      toast.remove();
    }, 300);
  }, 3000);
}

/**
 * Open Review Modal (placeholder)
 */
function openReviewModal() {
  // This function will be implemented when review system is ready
  alert("Chức năng đánh giá sẽ được cập nhật sớm!");
}

/**
 * Enhanced Table Interactions
 */
document.querySelectorAll(".table-hover tbody tr").forEach((row) => {
  row.addEventListener("click", function () {
    // Add visual feedback
    this.style.backgroundColor = "#e9ecef";
    setTimeout(() => {
      this.style.backgroundColor = "";
    }, 200);
  });
});

/**
 * Smooth Scroll for Anchor Links
 */
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  });
});

/**
 * Auto-refresh order status (optional)
 */
function autoRefreshStatus() {
  const urlParams = new URLSearchParams(window.location.search);
  const orderId = urlParams.get("order_id");

  if (!orderId) return;

  // Refresh every 30 seconds if order is in progress
  const currentStatus = document.querySelector(".status-badge");
  if (
    currentStatus &&
    !currentStatus.textContent.includes("Đã giao") &&
    !currentStatus.textContent.includes("Đã hủy")
  ) {
    setInterval(() => {
      // Show subtle loading indicator
      const indicator = document.createElement("div");
      indicator.className = "position-fixed top-0 end-0 m-3";
      indicator.innerHTML = `
                <span class="badge bg-info">
                    <i class="fas fa-sync-alt fa-spin me-1"></i>
                    Đang cập nhật...
                </span>
            `;
      document.body.appendChild(indicator);

      // Reload page
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    }, 30000); // 30 seconds
  }
}

// Uncomment to enable auto-refresh
// autoRefreshStatus();

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
    
    @media (max-width: 576px) {
        .notification-toast {
            min-width: calc(100vw - 40px);
            left: 20px;
            right: 20px;
        }
    }
`;
document.head.appendChild(style);

/**
 * Log page view
 */
console.log("Order Tracking Page Loaded");
console.log(
  "Timeline items:",
  document.querySelectorAll(".timeline-item").length
);
console.log(
  "Order items:",
  document.querySelectorAll(".table tbody tr").length
);
