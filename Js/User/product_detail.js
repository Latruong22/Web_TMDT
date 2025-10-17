// ============================================
// PRODUCT DETAIL PAGE - JAVASCRIPT
// ============================================

document.addEventListener("DOMContentLoaded", function () {
  // ============================================
  // IMAGE GALLERY & ZOOM
  // ============================================

  const mainImage = document.getElementById("mainImage");
  const mainImageContainer = document.querySelector(".main-image-container");
  const zoomLens = document.getElementById("zoomLens");
  const thumbnails = document.querySelectorAll(".thumbnail-item");
  const fullscreenBtn = document.getElementById("fullscreenBtn");
  const fullscreenModal = document.getElementById("fullscreenModal");
  const fullscreenImage = document.getElementById("fullscreenImage");
  const closeFullscreen = document.getElementById("closeFullscreen");

  // Thumbnail Click - Change Main Image
  thumbnails.forEach((thumbnail) => {
    thumbnail.addEventListener("click", function () {
      const newImageSrc = this.getAttribute("data-image");

      // Update active state
      thumbnails.forEach((t) => t.classList.remove("active"));
      this.classList.add("active");

      // Change main image with fade effect
      mainImage.style.opacity = "0";
      setTimeout(() => {
        mainImage.src = newImageSrc;
        mainImage.style.opacity = "1";
      }, 200);
    });
  });

  // Zoom Lens Effect
  if (mainImageContainer && zoomLens) {
    mainImageContainer.addEventListener("mousemove", function (e) {
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;

      // Position lens
      const lensX = x - zoomLens.offsetWidth / 2;
      const lensY = y - zoomLens.offsetHeight / 2;

      zoomLens.style.left = lensX + "px";
      zoomLens.style.top = lensY + "px";
    });

    mainImageContainer.addEventListener("mouseleave", function () {
      zoomLens.style.opacity = "0";
    });

    mainImageContainer.addEventListener("mouseenter", function () {
      zoomLens.style.opacity = "1";
    });
  }

  // Fullscreen Image
  if (fullscreenBtn) {
    fullscreenBtn.addEventListener("click", function () {
      fullscreenImage.src = mainImage.src;
      fullscreenModal.classList.add("active");
      document.body.style.overflow = "hidden";
    });
  }

  if (closeFullscreen) {
    closeFullscreen.addEventListener("click", closeFullscreenModal);
  }

  if (fullscreenModal) {
    fullscreenModal.addEventListener("click", function (e) {
      if (e.target === this) {
        closeFullscreenModal();
      }
    });
  }

  function closeFullscreenModal() {
    fullscreenModal.classList.remove("active");
    document.body.style.overflow = "";
  }

  // ESC key to close fullscreen
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && fullscreenModal.classList.contains("active")) {
      closeFullscreenModal();
    }
  });

  // ============================================
  // SIZE SELECTOR (for shoes and snowboards)
  // ============================================

  const sizeOptions = document.getElementById("sizeOptions");
  let selectedSize = null;

  if (sizeOptions) {
    const sizeRadios = sizeOptions.querySelectorAll(".size-radio");

    sizeRadios.forEach((radio) => {
      radio.addEventListener("change", function () {
        if (this.disabled) return;

        // Update selected size
        selectedSize = this.value;

        // Update size label display
        const sizeLabel = document.querySelector(".size-placeholder");
        if (sizeLabel) {
          sizeLabel.textContent = selectedSize;
        }

        // Add animation to the label
        const label = this.closest(".size-option-label");
        if (label) {
          label.style.transform = "scale(1.05)";
          setTimeout(() => {
            label.style.transform = "";
          }, 200);
        }
      });
    });
  }

  // ============================================
  // QUANTITY SELECTOR
  // ============================================

  const qtyInput = document.getElementById("quantity");
  const qtyMinus = document.getElementById("qtyMinus");
  const qtyPlus = document.getElementById("qtyPlus");

  if (qtyMinus && qtyPlus && qtyInput) {
    qtyMinus.addEventListener("click", function () {
      let currentValue = parseInt(qtyInput.value);
      if (currentValue > 1) {
        qtyInput.value = currentValue - 1;
        animateButton(this);
      }
    });

    qtyPlus.addEventListener("click", function () {
      let currentValue = parseInt(qtyInput.value);
      let maxValue = parseInt(qtyInput.max);
      if (currentValue < maxValue) {
        qtyInput.value = currentValue + 1;
        animateButton(this);
      }
    });
  }

  function animateButton(button) {
    button.style.transform = "scale(0.9)";
    setTimeout(() => {
      button.style.transform = "scale(1)";
    }, 100);
  }

  // ============================================
  // ADD TO CART
  // ============================================

  const addToCartBtn = document.getElementById("addToCartBtn");

  if (addToCartBtn) {
    addToCartBtn.addEventListener("click", function () {
      // Check if user is logged in
      if (!productData.isLoggedIn) {
        showToast(
          "Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!",
          "warning"
        );
        setTimeout(() => {
          window.location.href =
            "login.php?redirect=" +
            encodeURIComponent(
              window.location.pathname + window.location.search
            );
        }, 1500);
        return;
      }

      // Validate size for products that need size selection (snowboards and shoes)
      if (productData.needsSize && !selectedSize) {
        const message = productData.isShoe
          ? "Vui lòng chọn size giày!"
          : "Vui lòng chọn size!";
        showToast(message, "warning");
        highlightSizeSelector();
        return;
      }

      const quantity = parseInt(qtyInput.value);

      // Get cart from localStorage
      let cart = JSON.parse(localStorage.getItem("cart") || "[]");

      // Check if product already in cart
      const existingIndex = cart.findIndex(
        (item) =>
          item.id === productData.id &&
          (!productData.needsSize || item.size === selectedSize)
      );

      if (existingIndex !== -1) {
        // Update quantity
        cart[existingIndex].quantity += quantity;
      } else {
        // Add new item
        const cartItem = {
          id: productData.id,
          name: productData.name,
          price: productData.price,
          image: productData.image,
          quantity: quantity,
        };

        if (productData.needsSize) {
          cartItem.size = selectedSize;
        }

        cart.push(cartItem);
      }

      // Save to localStorage
      localStorage.setItem("cart", JSON.stringify(cart));

      // Update cart count
      updateCartCount();

      // Show success toast
      showToast("Đã thêm vào giỏ hàng!", "success");

      // Button animation
      addToCartBtn.innerHTML = '<i class="fas fa-check me-2"></i>Đã thêm!';
      addToCartBtn.classList.add("btn-success");
      addToCartBtn.classList.remove("btn-primary");

      setTimeout(() => {
        addToCartBtn.innerHTML =
          '<i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ hàng';
        addToCartBtn.classList.add("btn-primary");
        addToCartBtn.classList.remove("btn-success");
      }, 2000);
    });
  }

  function highlightSizeSelector() {
    const sizeSelector = document.querySelector(".size-selector");
    if (sizeSelector) {
      sizeSelector.style.animation = "shake 0.5s ease";
      sizeSelector.scrollIntoView({ behavior: "smooth", block: "center" });

      setTimeout(() => {
        sizeSelector.style.animation = "";
      }, 500);
    }
  }

  // ============================================
  // CART COUNT UPDATE
  // ============================================

  function updateCartCount() {
    const cartBadge = document.getElementById("cart-count");
    if (cartBadge) {
      const cart = JSON.parse(localStorage.getItem("cart") || "[]");
      const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
      cartBadge.textContent = totalItems;

      // Badge animation
      cartBadge.style.transform = "scale(1.5)";
      setTimeout(() => {
        cartBadge.style.transform = "scale(1)";
      }, 300);
    }
  }

  // Initialize cart count on page load
  updateCartCount();

  // Note: showToast is now a global function (defined after DOMContentLoaded)

  // ============================================
  // WISHLIST BUTTON
  // ============================================

  const wishlistBtn = document.querySelector(".btn-wishlist-icon");

  if (wishlistBtn) {
    wishlistBtn.addEventListener("click", function () {
      const icon = this.querySelector("i");

      if (icon.classList.contains("far")) {
        icon.classList.remove("far");
        icon.classList.add("fas");
        this.classList.add("active");
        showToast("Đã thêm vào danh sách yêu thích!", "success");
      } else {
        icon.classList.remove("fas");
        icon.classList.add("far");
        this.classList.remove("active");
        showToast("Đã xóa khỏi danh sách yêu thích!", "success");
      }
    });
  }

  // ============================================
  // SMOOTH SCROLL FOR BREADCRUMB
  // ============================================

  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const href = this.getAttribute("href");
      // Skip if href is just "#" or if it's a special link (like view details)
      if (href === "#" || this.id === "viewDetailsLink") {
        return; // Don't prevent default, let other handlers work
      }
      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });

  // ============================================
  // KEYBOARD SHORTCUTS
  // ============================================

  document.addEventListener("keydown", function (e) {
    // Arrow keys for thumbnail navigation
    if (e.key === "ArrowLeft" || e.key === "ArrowRight") {
      const activeThumbnail = document.querySelector(".thumbnail-item.active");
      if (activeThumbnail) {
        const thumbnailArray = Array.from(thumbnails);
        const currentIndex = thumbnailArray.indexOf(activeThumbnail);

        let newIndex;
        if (e.key === "ArrowLeft") {
          newIndex =
            currentIndex > 0 ? currentIndex - 1 : thumbnailArray.length - 1;
        } else {
          newIndex =
            currentIndex < thumbnailArray.length - 1 ? currentIndex + 1 : 0;
        }

        thumbnailArray[newIndex].click();
        e.preventDefault();
      }
    }

    // + / - for quantity
    if (e.key === "+" || e.key === "=") {
      qtyPlus.click();
      e.preventDefault();
    }
    if (e.key === "-") {
      qtyMinus.click();
      e.preventDefault();
    }
  });

  // ============================================
  // LAZY LOADING OPTIMIZATION
  // ============================================

  if ("IntersectionObserver" in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute("data-src");
          }
          observer.unobserve(img);
        }
      });
    });

    document.querySelectorAll("img[data-src]").forEach((img) => {
      imageObserver.observe(img);
    });
  }

  // ============================================
  // PROMOTION MODAL
  // ============================================

  const viewDetailsLink = document.getElementById("viewDetailsLink");
  if (viewDetailsLink) {
    viewDetailsLink.addEventListener("click", function (e) {
      e.preventDefault();
      const promotionModal = new bootstrap.Modal(
        document.getElementById("promotionModal")
      );
      promotionModal.show();
    });
  }

  // ============================================
  // CONSOLE INFO
  // ============================================

  console.log("🏂 Product Detail Page Loaded");
  console.log("Product ID:", productData.id);
  console.log("Product Name:", productData.name);
  console.log("Is Shoe:", productData.isShoe);
  console.log("Stock:", productData.stock);
});

// ============================================
// TOAST NOTIFICATION (GLOBAL)
// ============================================

function showToast(message, type = "success") {
  const toastEl = document.getElementById("cartToast");
  if (!toastEl) return;

  const toastHeader = toastEl.querySelector(".toast-header strong");
  const toastBody = toastEl.querySelector(".toast-body");
  const toastIcon = toastEl.querySelector(".toast-header i");

  // Update content
  toastBody.textContent = message;

  // Update styling based on type
  if (type === "success") {
    toastIcon.className = "fas fa-check-circle text-success me-2";
    toastHeader.textContent = "Thành công";
  } else if (type === "warning") {
    toastIcon.className = "fas fa-exclamation-circle text-warning me-2";
    toastHeader.textContent = "Cảnh báo";
  } else if (type === "error") {
    toastIcon.className = "fas fa-times-circle text-danger me-2";
    toastHeader.textContent = "Lỗi";
  }

  // Show toast
  const toast = new bootstrap.Toast(toastEl);
  toast.show();
}

// ============================================
// REVIEW SYSTEM
// ============================================

// Rating Stars Interaction
document.addEventListener("DOMContentLoaded", function () {
  const reviewContent = document.getElementById("reviewContent");
  const charCount = document.getElementById("charCount");
  const starLabels = document.querySelectorAll(".star-label");

  // Character counter
  if (reviewContent && charCount) {
    reviewContent.addEventListener("input", function () {
      charCount.textContent = this.value.length;

      // Color feedback
      if (this.value.length < 10) {
        charCount.style.color = "#dc3545"; // Red
      } else if (this.value.length >= 500) {
        charCount.style.color = "#ffc107"; // Yellow
      } else {
        charCount.style.color = "#28a745"; // Green
      }
    });
  }

  // Star rating interaction
  starLabels.forEach((label, index) => {
    const input = label.previousElementSibling;

    label.addEventListener("click", function () {
      // Check the radio input
      input.checked = true;

      // Update star display
      updateStarDisplay(index);
    });

    // Hover effect
    label.addEventListener("mouseenter", function () {
      starLabels.forEach((l, i) => {
        const star = l.querySelector("i");
        if (i <= index) {
          star.classList.add("fas");
          star.classList.remove("far");
        } else {
          star.classList.remove("fas");
          star.classList.add("far");
        }
      });
    });
  });

  // Reset stars on mouse leave to checked state or empty
  const ratingInput = document.querySelector(".rating-input");
  if (ratingInput) {
    ratingInput.addEventListener("mouseleave", function () {
      const checkedInput = this.querySelector('input[name="rating"]:checked');
      if (checkedInput) {
        const checkedIndex = parseInt(checkedInput.value) - 1;
        updateStarDisplay(checkedIndex);
      } else {
        // Reset all to empty
        starLabels.forEach((l) => {
          const star = l.querySelector("i");
          star.classList.remove("fas");
          star.classList.add("far");
        });
      }
    });
  }

  // Helper function to update star display
  function updateStarDisplay(selectedIndex) {
    starLabels.forEach((l, i) => {
      const star = l.querySelector("i");
      if (i <= selectedIndex) {
        star.classList.add("fas");
        star.classList.remove("far");
      } else {
        star.classList.remove("fas");
        star.classList.add("far");
      }
    });
  }
});

// Submit Review Function
function submitReview() {
  const form = document.getElementById("reviewForm");
  const formData = new FormData(form);
  formData.append("action", "submit_review");

  // Validate rating
  const rating = formData.get("rating");
  if (!rating) {
    alert("⚠️ Vui lòng chọn số sao đánh giá!");
    return;
  }

  // Validate content
  const content = formData.get("content");
  if (!content || content.trim().length < 10) {
    alert("⚠️ Nhận xét phải có ít nhất 10 ký tự!");
    return;
  }

  if (content.length > 500) {
    alert("⚠️ Nhận xét không được quá 500 ký tự!");
    return;
  }

  // Get submit button from modal
  const submitBtn = document.querySelector(
    "#reviewModal .modal-footer .btn-primary"
  );
  if (!submitBtn) {
    console.error("Submit button not found");
    return;
  }

  const originalText = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...';

  // Submit via AJAX
  fetch("/Web_TMDT/controller/controller_User/review_controller.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Close modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("reviewModal")
        );
        modal.hide();

        // Show success message
        showToast(data.message, "success");

        // Reset form
        form.reset();
        document.getElementById("charCount").textContent = "0";

        // Reset stars
        document.querySelectorAll(".star-label i").forEach((star) => {
          star.classList.remove("fas");
          star.classList.add("far");
        });

        // Reload page after 2 seconds
        setTimeout(() => {
          location.reload();
        }, 2000);
      } else {
        alert("❌ " + data.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("❌ Có lỗi xảy ra, vui lòng thử lại sau!");
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    });
}

// Load More Reviews
let currentOffset = 5;
function loadMoreReviews() {
  const productId = productData.id;

  fetch(
    `/Web_TMDT/controller/controller_User/review_controller.php?action=get_reviews&product_id=${productId}&limit=5&offset=${currentOffset}`
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.reviews.length > 0) {
        const reviewsList = document.querySelector(".reviews-list");

        data.reviews.forEach((review) => {
          const reviewCard = createReviewCard(review);
          const loadMoreBtn = reviewsList.querySelector(".text-center");
          reviewsList.insertBefore(reviewCard, loadMoreBtn);
        });

        currentOffset += 5;

        // Hide button if no more reviews
        if (currentOffset >= data.rating.total) {
          const loadMoreBtn = reviewsList.querySelector(".text-center");
          if (loadMoreBtn) loadMoreBtn.remove();
        }
      }
    })
    .catch((error) => {
      console.error("Error loading more reviews:", error);
    });
}

// Create Review Card HTML
function createReviewCard(review) {
  const card = document.createElement("div");
  card.className = "card mb-3 border-0 shadow-sm review-item";

  let starsHtml = "";
  for (let i = 1; i <= 5; i++) {
    const starClass =
      i <= review.rating ? "fas text-warning" : "fas text-muted";
    starsHtml += `<i class="${starClass} fa-star"></i>`;
  }

  card.innerHTML = `
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="mb-1 fw-bold">${escapeHtml(review.fullname)}</h6>
                    <div class="stars">${starsHtml}</div>
                </div>
                <small class="text-muted">
                    <i class="far fa-clock me-1"></i>${review.formatted_date}
                </small>
            </div>
            <p class="mb-0 text-secondary">${escapeHtml(review.content).replace(
              /\n/g,
              "<br>"
            )}</p>
        </div>
    `;

  return card;
}

// HTML escape function
function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  };
  return text.replace(/[&<>"']/g, (m) => map[m]);
}

// ============================================
// CSS ANIMATION FOR SHAKE
// ============================================

const style = document.createElement("style");
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
`;
document.head.appendChild(style);
