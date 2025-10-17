/**
 * ORDER HISTORY PAGE JAVASCRIPT
 * Handles interactions and animations for order history page
 */

document.addEventListener("DOMContentLoaded", function () {
  initBackToTop();
  initOrderCardAnimations();
  initFilterHighlight();
  initTooltips();
  initReviewModalInteractions();
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
 * Open Review Modal for Order (History Page)
 */
function openOrderReviewModal(orderId) {
  // Fetch order products
  fetch(
    `../../controller/controller_User/order_controller.php?action=get_order_items&order_id=${orderId}`
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.items && data.items.length > 0) {
        const products = data.items.map((item) => ({
          id: item.product_id,
          name: item.product_name,
          image: getProductImageUrl(item.product_id, item.product_image),
        }));

        if (products.length === 1) {
          // Single product - open review modal directly
          showReviewModal(
            products[0].id,
            products[0].name,
            products[0].image,
            orderId
          );
        } else {
          // Multiple products - show selection modal
          showProductSelectionModal(products, orderId);
        }
      } else {
        showNotification("Không tìm thấy sản phẩm trong đơn hàng", "error");
      }
    })
    .catch((error) => {
      console.error("Error fetching order items:", error);
      showNotification("Lỗi khi tải thông tin đơn hàng", "error");
    });
}

/**
 * Get Product Image URL
 */
function getProductImageUrl(productId, fallbackImage) {
  // If no fallback image, try folder structure
  if (!fallbackImage) {
    return `/Web_TMDT/Images/product/Sp${productId}/main.jpg`;
  }

  // If already full path with /Web_TMDT/ or http
  if (
    fallbackImage.startsWith("http") ||
    fallbackImage.startsWith("/Web_TMDT/")
  ) {
    return fallbackImage;
  }

  // If starts with "Images/" - add /Web_TMDT/ prefix
  if (fallbackImage.startsWith("Images/")) {
    return `/Web_TMDT/${fallbackImage}`;
  }

  // Otherwise assume it's in product folder
  return `/Web_TMDT/Images/product/Sp${productId}/${fallbackImage}`;
}

/**
 * Show Product Selection Modal
 */
function showProductSelectionModal(products, orderId) {
  const html = `
    <div class="modal fade" id="productSelectionModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
              <i class="fas fa-box-open me-2"></i>Chọn sản phẩm để đánh giá
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted mb-3">Vui lòng chọn sản phẩm bạn muốn đánh giá:</p>
            <div class="list-group">
              ${products
                .map(
                  (product) => `
                <button type="button" class="list-group-item list-group-item-action" 
                        onclick="selectProductForReview(${
                          product.id
                        }, '${escapeHtml(product.name)}', '${
                    product.image
                  }', ${orderId})">
                  <div class="d-flex align-items-center">
                    <img src="${product.image}" alt="${escapeHtml(
                    product.name
                  )}" 
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" class="me-3">
                    <div class="flex-grow-1">
                      <h6 class="mb-1">${escapeHtml(product.name)}</h6>
                      <small class="text-muted">Nhấp để đánh giá</small>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                  </div>
                </button>
              `
                )
                .join("")}
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  // Remove existing modal
  const existingModal = document.getElementById("productSelectionModal");
  if (existingModal) existingModal.remove();

  // Add and show modal
  document.body.insertAdjacentHTML("beforeend", html);
  const modal = new bootstrap.Modal(
    document.getElementById("productSelectionModal")
  );
  modal.show();
}

/**
 * Select Product for Review
 */
function selectProductForReview(productId, productName, productImage, orderId) {
  // Close selection modal
  const selectionModal = bootstrap.Modal.getInstance(
    document.getElementById("productSelectionModal")
  );
  if (selectionModal) selectionModal.hide();

  // Open review modal
  setTimeout(() => {
    showReviewModal(productId, productName, productImage, orderId);
  }, 300);
}

/**
 * Show Review Modal
 */
function showReviewModal(productId, productName, productImage, orderId) {
  // Check if user can review
  checkCanReview(productId, (canReview, message) => {
    if (!canReview) {
      showNotification(message, "warning");
      return;
    }

    // Populate product info
    document.getElementById("reviewProductInfo").innerHTML = `
      <div class="d-flex align-items-center">
        <img src="${productImage}" alt="${escapeHtml(productName)}" 
             style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" class="me-3">
        <div>
          <h6 class="mb-0">${escapeHtml(productName)}</h6>
          <small class="text-muted">Sản phẩm bạn đã mua</small>
        </div>
      </div>
    `;

    // Set form values
    document.getElementById("review_product_id").value = productId;
    document.getElementById("review_order_id").value = orderId;

    // Reset form
    document.getElementById("reviewForm").reset();
    document.getElementById("review_rating").value = "";
    document.getElementById("char_count").textContent = "0";

    // Reset stars
    document
      .querySelectorAll("#reviewModal .star-rating-input i")
      .forEach((star) => {
        star.className = "far fa-star";
      });

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById("reviewModal"));
    modal.show();
  });
}

/**
 * Check if User Can Review
 */
function checkCanReview(productId, callback) {
  fetch(
    `../../controller/controller_User/review_controller.php?action=check_can_review&product_id=${productId}`
  )
    .then((response) => response.json())
    .then((data) => {
      callback(data.success && data.can_review, data.message || "");
    })
    .catch((error) => {
      console.error("Error:", error);
      callback(false, "Lỗi kết nối");
    });
}

/**
 * Submit Review
 */
function submitReview() {
  const productId = document.getElementById("review_product_id").value;
  const orderId = document.getElementById("review_order_id").value;
  const rating = document.getElementById("review_rating").value;
  const content = document.getElementById("review_content").value.trim();

  // Validation
  if (!rating || rating < 1 || rating > 5) {
    showNotification("Vui lòng chọn số sao đánh giá", "warning");
    return;
  }

  if (!content || content.length < 10) {
    showNotification("Vui lòng nhập ít nhất 10 ký tự nhận xét", "warning");
    return;
  }

  if (content.length > 500) {
    showNotification("Nhận xét không được vượt quá 500 ký tự", "warning");
    return;
  }

  // Disable button
  const submitBtn = document.querySelector(
    "#reviewModal .modal-footer .btn-primary"
  );
  const originalHtml = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML =
    '<i class="fas fa-spinner fa-spin me-1"></i>Đang gửi...';

  // Prepare form data
  const formData = new FormData();
  formData.append("action", "submit_review");
  formData.append("product_id", productId);
  formData.append("order_id", orderId);
  formData.append("rating", rating);
  formData.append("content", content);

  // Submit
  fetch("../../controller/controller_User/review_controller.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(
          data.message || "Đánh giá đã được gửi thành công!",
          "success"
        );

        // Close modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("reviewModal")
        );
        if (modal) modal.hide();

        // Reset form
        document.getElementById("reviewForm").reset();

        // Reload page
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        showNotification(data.message || "Không thể gửi đánh giá", "error");
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Lỗi kết nối, vui lòng thử lại", "error");
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHtml;
    });
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Initialize Review Modal Interactions
 */
function initReviewModalInteractions() {
  // Star rating click
  document.addEventListener("click", function (e) {
    if (e.target.closest("#reviewModal .star-rating-input i")) {
      const star = e.target.closest("i");
      const rating = parseInt(star.getAttribute("data-rating"));

      document.getElementById("review_rating").value = rating;

      const stars = document.querySelectorAll(
        "#reviewModal .star-rating-input i"
      );
      stars.forEach((s, index) => {
        s.className =
          index < rating ? "fas fa-star text-warning" : "far fa-star";
      });
    }
  });

  // Star hover
  document.addEventListener("mouseover", function (e) {
    if (e.target.closest("#reviewModal .star-rating-input i")) {
      const star = e.target.closest("i");
      const rating = parseInt(star.getAttribute("data-rating"));

      const stars = document.querySelectorAll(
        "#reviewModal .star-rating-input i"
      );
      stars.forEach((s, index) => {
        if (index < rating) s.style.color = "#ffc107";
      });
    }
  });

  document.addEventListener("mouseout", function (e) {
    if (e.target.closest("#reviewModal .star-rating-input")) {
      const currentRating =
        parseInt(document.getElementById("review_rating").value) || 0;
      const stars = document.querySelectorAll(
        "#reviewModal .star-rating-input i"
      );

      stars.forEach((s, index) => {
        if (index < currentRating) {
          s.className = "fas fa-star text-warning";
        } else {
          s.className = "far fa-star";
          s.style.color = "";
        }
      });
    }
  });

  // Character counter
  document.addEventListener("input", function (e) {
    if (e.target.id === "review_content") {
      document.getElementById("char_count").textContent = e.target.value.length;
    }
  });
}

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

// ============================================
// CANCEL ORDER - REDIRECT TO ORDER TRACKING
// ============================================

/**
 * Redirect to order tracking page to cancel order
 * Order tracking page has the cancel modal
 */
function redirectToCancelOrder(orderId) {
  if (confirm("Bạn muốn hủy đơn hàng #" + orderId + "?")) {
    // Redirect to order tracking page where cancel modal is available
    window.location.href = "order_tracking.php?order_id=" + orderId;
  }
}

/**
 * Log page view (optional analytics)
 */
console.log("Order History Page Loaded");
console.log(
  "Total orders displayed:",
  document.querySelectorAll(".order-card").length
);
