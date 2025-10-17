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
 * Open Review Modal with Product Selection
 */
function openReviewModal() {
  // Get order products from the table
  const orderTable = document.querySelector(".table-hover tbody");
  if (!orderTable) {
    showNotification("Không tìm thấy sản phẩm trong đơn hàng", "error");
    return;
  }

  const rows = orderTable.querySelectorAll("tr");
  const products = [];

  rows.forEach((row) => {
    const img = row.querySelector("img");
    const nameCell = row.cells[1];
    const priceCell = row.cells[2];

    if (img && nameCell && priceCell) {
      const productId =
        img.closest("tr").getAttribute("data-product-id") ||
        extractProductId(img.src);
      const productName =
        nameCell.querySelector("p")?.textContent.trim() ||
        nameCell.textContent.trim();
      const productImage = img.src;

      products.push({
        id: productId,
        name: productName,
        image: productImage,
      });
    }
  });

  if (products.length === 0) {
    showNotification("Không có sản phẩm nào để đánh giá", "error");
    return;
  }

  // If only one product, open review modal directly
  if (products.length === 1) {
    showReviewModal(products[0].id, products[0].name, products[0].image);
    return;
  }

  // If multiple products, show selection interface
  showProductSelectionModal(products);
}

/**
 * Extract product ID from image URL
 */
function extractProductId(imageUrl) {
  const match = imageUrl.match(/Sp(\d+)/);
  return match ? match[1] : "";
}

/**
 * Show Product Selection Modal for Multiple Products
 */
function showProductSelectionModal(products) {
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
                        onclick="selectProductForReview('${
                          product.id
                        }', '${escapeHtml(product.name)}', '${product.image}')">
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

  // Remove existing modal if any
  const existingModal = document.getElementById("productSelectionModal");
  if (existingModal) existingModal.remove();

  // Add modal to DOM
  document.body.insertAdjacentHTML("beforeend", html);

  // Show modal
  const modal = new bootstrap.Modal(
    document.getElementById("productSelectionModal")
  );
  modal.show();
}

/**
 * Select Product for Review (called from selection modal)
 */
function selectProductForReview(productId, productName, productImage) {
  // Close selection modal
  const selectionModal = bootstrap.Modal.getInstance(
    document.getElementById("productSelectionModal")
  );
  if (selectionModal) selectionModal.hide();

  // Open review modal
  setTimeout(() => {
    showReviewModal(productId, productName, productImage);
  }, 300);
}

/**
 * Show Review Modal for a Specific Product
 */
function showReviewModal(productId, productName, productImage) {
  // Check if user can review this product
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

    // Set product ID
    document.getElementById("review_product_id").value = productId;

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
 * Check if User Can Review Product
 */
function checkCanReview(productId, callback) {
  fetch(
    `../../controller/controller_User/review_controller.php?action=check_can_review&product_id=${productId}`
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        callback(data.can_review, data.message || "");
      } else {
        callback(false, data.message || "Không thể kiểm tra quyền đánh giá");
      }
    })
    .catch((error) => {
      console.error("Error checking review permission:", error);
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

  // Disable submit button
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

  // Submit review
  fetch("../../controller/controller_User/review_controller.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showNotification(
          data.message || "Đánh giá của bạn đã được gửi thành công!",
          "success"
        );

        // Close modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("reviewModal")
        );
        if (modal) modal.hide();

        // Reset form
        document.getElementById("reviewForm").reset();

        // Reload page after short delay
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
      console.error("Error submitting review:", error);
      showNotification("Lỗi kết nối, vui lòng thử lại", "error");
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalHtml;
    });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
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
 * Initialize Review Modal Interactions
 */
function initReviewModalInteractions() {
  // Star rating interaction
  document.addEventListener("click", function (e) {
    if (e.target.closest("#reviewModal .star-rating-input i")) {
      const star = e.target.closest("i");
      const rating = parseInt(star.getAttribute("data-rating"));

      // Set hidden input
      document.getElementById("review_rating").value = rating;

      // Update star display
      const stars = document.querySelectorAll(
        "#reviewModal .star-rating-input i"
      );
      stars.forEach((s, index) => {
        if (index < rating) {
          s.className = "fas fa-star text-warning";
        } else {
          s.className = "far fa-star";
        }
      });
    }
  });

  // Star hover effect
  document.addEventListener("mouseover", function (e) {
    if (e.target.closest("#reviewModal .star-rating-input i")) {
      const star = e.target.closest("i");
      const rating = parseInt(star.getAttribute("data-rating"));

      const stars = document.querySelectorAll(
        "#reviewModal .star-rating-input i"
      );
      stars.forEach((s, index) => {
        if (index < rating) {
          s.style.color = "#ffc107";
        }
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
      const count = e.target.value.length;
      document.getElementById("char_count").textContent = count;
    }
  });
}

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

// ============================================
// CANCEL ORDER FUNCTIONALITY
// ============================================

/**
 * Toggle other reason textarea when "Lý do khác" is selected
 */
document.addEventListener("DOMContentLoaded", function () {
  const cancelReasonRadios = document.querySelectorAll(
    'input[name="cancel_reason"]'
  );
  const otherReasonTextarea = document.getElementById("otherReason");

  if (cancelReasonRadios.length > 0 && otherReasonTextarea) {
    cancelReasonRadios.forEach((radio) => {
      radio.addEventListener("change", function () {
        if (this.id === "reason5") {
          otherReasonTextarea.style.display = "block";
          otherReasonTextarea.required = true;
          otherReasonTextarea.focus();
        } else {
          otherReasonTextarea.style.display = "none";
          otherReasonTextarea.required = false;
          otherReasonTextarea.value = "";
        }
      });
    });
  }
});

/**
 * Submit cancel order request
 */
function submitCancelOrder() {
  const selectedReason = document.querySelector(
    'input[name="cancel_reason"]:checked'
  );

  // Validate: must select a reason
  if (!selectedReason) {
    alert("❌ Vui lòng chọn lý do hủy đơn");
    return;
  }

  let cancelReason = selectedReason.value;

  // If "Lý do khác" selected, use textarea value
  if (selectedReason.id === "reason5") {
    const otherReasonText = document.getElementById("otherReason").value.trim();
    if (!otherReasonText) {
      alert("❌ Vui lòng nhập lý do hủy đơn");
      document.getElementById("otherReason").focus();
      return;
    }
    cancelReason = "Lý do khác: " + otherReasonText;
  }

  // Final confirmation
  if (!confirm("Bạn có chắc chắn muốn hủy đơn hàng này?")) {
    return;
  }

  // Get order_id from URL
  const urlParams = new URLSearchParams(window.location.search);
  const orderId = urlParams.get("order_id");

  if (!orderId) {
    alert("❌ Không tìm thấy mã đơn hàng");
    return;
  }

  // Prepare form data
  const formData = new FormData();
  formData.append("action", "cancel_order");
  formData.append("order_id", orderId);
  formData.append("cancel_reason", cancelReason);

  // Show loading state
  const submitBtn = event.target;
  const originalText = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';

  // AJAX request to cancel order
  fetch("../../controller/controller_User/cancel_order_controller.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Close modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("cancelOrderModal")
        );
        if (modal) modal.hide();

        // Show success message
        alert("✅ " + data.message);

        // Reload page to show cancelled status
        setTimeout(() => {
          location.reload();
        }, 500);
      } else {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;

        // Show error
        alert("❌ " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);

      // Restore button state
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;

      alert("❌ Có lỗi xảy ra, vui lòng thử lại!");
    });
}

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
