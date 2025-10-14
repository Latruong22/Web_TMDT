// Modern Cart JavaScript - Fixed Version
console.log("Cart.js loaded successfully!");

document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM loaded, initializing cart...");
  initializeCart();
  updateCartCount();
  loadSuggestedProducts();
});

// ========================================
// CART INITIALIZATION
// ========================================

function initializeCart() {
  const cart = getCartFromStorage();
  console.log("🚀 Initializing cart with", cart.length, "items:", cart);

  // Load voucher từ localStorage nếu có
  loadVoucherFromStorage();

  displayCartItems(cart);
  updateOrderSummary(cart);
  toggleCartSections(cart.length > 0);

  console.log("✅ Cart initialization completed!");
}

function loadVoucherFromStorage() {
  const savedVoucher = localStorage.getItem("appliedVoucher");
  if (savedVoucher) {
    try {
      appliedVoucher = JSON.parse(savedVoucher);
      console.log("✅ Voucher đã load từ localStorage:", appliedVoucher);

      // Hiển thị voucher đã apply trong UI
      const promoInput = document.getElementById("promoCode");
      const promoMessage = document.getElementById("promoMessage");

      if (promoInput) {
        promoInput.value = appliedVoucher.code;
      }

      if (promoMessage) {
        const discountText =
          appliedVoucher.type === "percent"
            ? `${appliedVoucher.discount}%`
            : formatCurrency(appliedVoucher.discount);
        promoMessage.textContent = `✓ Mã "${appliedVoucher.code}" đang được áp dụng (Giảm ${discountText})`;
        promoMessage.className = "promo-message success";
        promoMessage.style.display = "block";
      }
    } catch (error) {
      console.error("❌ Lỗi khi load voucher:", error);
      appliedVoucher = null;
      localStorage.removeItem("appliedVoucher");
    }
  }
}

function getCartFromStorage() {
  return JSON.parse(localStorage.getItem("cart") || "[]");
}

function saveCartToStorage(cart) {
  localStorage.setItem("cart", JSON.stringify(cart));
}

function displayCartItems(cart) {
  const cartItemsContainer = document.getElementById("cartItems");
  if (!cartItemsContainer) {
    console.error(
      "❌ Cart items container NOT found! Element #cartItems missing."
    );
    return;
  }

  console.log("✅ Cart container found, clearing contents...");
  cartItemsContainer.innerHTML = "";

  if (cart.length === 0) {
    console.log("ℹ️ Cart is empty - no items to display");
    return;
  }

  console.log(`🛒 Displaying ${cart.length} cart items...`);

  cart.forEach((item, index) => {
    console.log(`📦 Creating element for item ${index + 1}: ${item.name}`);
    const cartItemElement = createCartItemHTML(item, index);
    cartItemsContainer.appendChild(cartItemElement);
    console.log(`✅ Added item ${index + 1} to cart container`);
  });

  console.log("🎉 All cart items displayed successfully!");
}

function createCartItemHTML(item, index) {
  const cartItem = document.createElement("div");
  cartItem.className = "cart-item";
  cartItem.dataset.index = index;

  const itemTotal = item.price * item.quantity;

  cartItem.innerHTML = `
    <div class="row align-items-center">
      <div class="col-md-6">
        <div class="d-flex align-items-center">
          <div class="cart-item-image me-3">
            <img src="${item.image}" alt="${item.name}" 
                 onerror="this.src='/Web_TMDT/Images/product/placeholder.jpg'">
          </div>
          <div class="cart-item-info">
            <div class="cart-item-name" onclick="viewProduct(${item.id})">${
    item.name
  }</div>
            <div class="cart-item-details">
              <small class="text-muted">
                Màu: ${item.color || "Đen"}${
    item.size ? " | Size: " + item.size : ""
  }
              </small>
            </div>
            <div class="cart-item-actions mt-2">
              <a href="#" class="action-link update-link" onclick="updateCartItem(${index}); return false;">cập nhật</a>
              <a href="#" class="action-link remove-link ms-3" onclick="removeCartItem(${index}); return false;">xóa</a>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-2 text-center">
        <div class="cart-item-price">
          ${formatCurrency(item.price)}
        </div>
      </div>
      
      <div class="col-md-2 text-center">
        <div class="quantity-controls">
          <button class="quantity-btn" onclick="changeQuantity(${index}, -1)" ${
    item.quantity <= 1 ? "disabled" : ""
  }>
            <i class="fas fa-minus"></i>
          </button>
          <input type="number" class="quantity-input" value="${item.quantity}" 
                 onchange="setQuantity(${index}, this.value)" min="1" max="99">
          <button class="quantity-btn" onclick="changeQuantity(${index}, 1)">
            <i class="fas fa-plus"></i>
          </button>
        </div>
      </div>
      
      <div class="col-md-2 text-center">
        <div class="cart-item-total fw-bold">
          ${formatCurrency(itemTotal)}
        </div>
      </div>
    </div>
  `;

  return cartItem;
}

// ========================================
// CART OPERATIONS
// ========================================

function changeQuantity(index, change) {
  const cart = getCartFromStorage();
  if (cart[index]) {
    const newQuantity = cart[index].quantity + change;
    if (newQuantity >= 1 && newQuantity <= 99) {
      cart[index].quantity = newQuantity;
      saveCartToStorage(cart);
      initializeCart();
      updateCartCount();
    }
  }
}

function setQuantity(index, newQuantity) {
  const cart = getCartFromStorage();
  if (cart[index]) {
    const quantity = parseInt(newQuantity);
    if (quantity >= 1 && quantity <= 99) {
      cart[index].quantity = quantity;
      saveCartToStorage(cart);
      initializeCart();
      updateCartCount();
    }
  }
}

function removeCartItem(index) {
  const cart = getCartFromStorage();
  if (cart[index]) {
    cart.splice(index, 1);
    saveCartToStorage(cart);
    initializeCart();
    updateCartCount();
    showToast("Đã xóa sản phẩm khỏi giỏ hàng!");
  }
}

function updateCartItem(index) {
  showToast("Tính năng cập nhật sản phẩm sẽ được phát triển!");
}

function clearCart() {
  if (confirm("Bạn có chắc chắn muốn xóa tất cả sản phẩm trong giỏ hàng?")) {
    localStorage.removeItem("cart");
    initializeCart();
    updateCartCount();
    showToast("Đã xóa tất cả sản phẩm trong giỏ hàng!");
  }
}

// ========================================
// UI HELPERS
// ========================================

function toggleCartSections(hasItems) {
  console.log(`🔄 Toggling cart sections. Has items: ${hasItems}`);

  const emptyCart = document.getElementById("emptyCart");
  const orderSummaryContainer = document.getElementById(
    "orderSummaryContainer"
  );
  const continueShoppingSection = document.getElementById(
    "continueShoppingSection"
  );
  const shippingOptionsSection = document.getElementById(
    "shippingOptionsSection"
  );

  if (hasItems) {
    console.log("📦 Showing cart with items...");
    if (emptyCart) {
      emptyCart.style.display = "none";
      console.log("✅ Hidden empty cart message");
    }
    if (orderSummaryContainer) {
      orderSummaryContainer.style.display = "block";
      console.log("✅ Shown order summary");
    }
    if (continueShoppingSection) {
      continueShoppingSection.style.display = "block";
      console.log("✅ Shown continue shopping");
    }
    if (shippingOptionsSection) {
      shippingOptionsSection.style.display = "block";
      console.log("✅ Shown shipping options");
    }
  } else {
    console.log("🛒 Showing empty cart...");
    if (emptyCart) {
      emptyCart.style.display = "block";
      console.log("✅ Shown empty cart message");
    }
    if (orderSummaryContainer) {
      orderSummaryContainer.style.display = "none";
      console.log("✅ Hidden order summary");
    }
    if (continueShoppingSection) {
      continueShoppingSection.style.display = "none";
      console.log("✅ Hidden continue shopping");
    }
    if (shippingOptionsSection) {
      shippingOptionsSection.style.display = "none";
      console.log("✅ Hidden shipping options");
    }
  }
}

function updateCartCount() {
  const cart = getCartFromStorage();
  const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
  const cartCountElement = document.getElementById("cart-count");
  if (cartCountElement) {
    cartCountElement.textContent = totalItems;
    cartCountElement.style.display = totalItems > 0 ? "inline-block" : "none";
  }
}

// ========================================
// ORDER SUMMARY
// ========================================

// Biến global để lưu voucher đã apply
let appliedVoucher = null;

function updateOrderSummary(cart) {
  const subtotal = cart.reduce(
    (total, item) => total + item.price * item.quantity,
    0
  );
  const shipping = calculateShippingCost(subtotal);
  const discount = getCurrentDiscount(subtotal);
  const total = subtotal + shipping - discount;

  const subtotalEl = document.getElementById("subtotal");
  const shippingEl = document.getElementById("shipping");
  const totalEl = document.getElementById("total");

  if (subtotalEl) subtotalEl.textContent = formatCurrency(subtotal);
  if (shippingEl) shippingEl.textContent = formatCurrency(shipping);
  if (totalEl) totalEl.textContent = formatCurrency(total);

  // Show/hide discount row
  const discountRow = document.getElementById("discountRow");
  if (discount > 0) {
    const discountEl = document.getElementById("discount");
    if (discountEl) discountEl.textContent = "-" + formatCurrency(discount);
    if (discountRow) discountRow.style.display = "flex";
  } else {
    if (discountRow) discountRow.style.display = "none";
  }
}

function calculateShippingCost(subtotal) {
  // Phí vận chuyển cố định 30,000đ (đồng nhất với checkout)
  return 30000;
}

function getCurrentDiscount(subtotal) {
  if (!appliedVoucher) {
    return 0;
  }

  // Tính discount dựa trên type
  if (appliedVoucher.type === "percent") {
    return (subtotal * appliedVoucher.discount) / 100;
  } else if (appliedVoucher.type === "fixed") {
    return appliedVoucher.discount;
  }

  return 0;
}

// ========================================
// PROMO CODES
// ========================================

function applyPromoCode() {
  const promoInput = document.getElementById("promoCode");
  const promoMessage = document.getElementById("promoMessage");
  const applyBtn = document.querySelector('button[onclick="applyPromoCode()"]');

  if (!promoInput) {
    console.error("❌ Promo input not found");
    return;
  }

  const promoCode = promoInput.value.trim();

  if (!promoCode) {
    showPromoMessage("Vui lòng nhập mã giảm giá!", "error");
    return;
  }

  // Hiển thị loading
  if (applyBtn) {
    applyBtn.disabled = true;
    applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  }
  showPromoMessage("Đang kiểm tra...", "info");

  // Gọi API validate voucher
  fetch("../../controller/controller_User/voucher_controller.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=validate_voucher&code=${encodeURIComponent(promoCode)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Lưu voucher đã apply
        appliedVoucher = {
          code: promoCode,
          discount: data.discount,
          type: data.type,
        };

        // LƯU VOUCHER VÀO LOCALSTORAGE để sử dụng ở trang checkout
        localStorage.setItem("appliedVoucher", JSON.stringify(appliedVoucher));
        console.log("✅ Voucher đã lưu vào localStorage:", appliedVoucher);

        // Hiển thị message success
        const discountText =
          data.type === "percent"
            ? `${data.discount}%`
            : formatCurrency(data.discount);
        showPromoMessage(
          `✓ Áp dụng thành công! Giảm ${discountText}`,
          "success"
        );

        // Tính lại totals
        const cart = getCartFromStorage();
        updateOrderSummary(cart);

        // Auto-hide success message sau 5 giây
        setTimeout(() => {
          if (promoMessage) {
            promoMessage.style.display = "none";
          }
        }, 5000);
      } else {
        // Reset voucher nếu không hợp lệ
        appliedVoucher = null;

        // XÓA VOUCHER KHỎI LOCALSTORAGE
        localStorage.removeItem("appliedVoucher");
        console.log("❌ Voucher đã xóa khỏi localStorage");

        showPromoMessage(data.message || "Mã giảm giá không hợp lệ", "error");

        // Tính lại totals không có discount
        const cart = getCartFromStorage();
        updateOrderSummary(cart);
      }
    })
    .catch((error) => {
      console.error("Lỗi khi kiểm tra voucher:", error);
      appliedVoucher = null;
      showPromoMessage("Có lỗi xảy ra. Vui lòng thử lại", "error");
    })
    .finally(() => {
      if (applyBtn) {
        applyBtn.disabled = false;
        applyBtn.innerHTML = '<i class="fas fa-arrow-right"></i>';
      }
    });
}

function showPromoMessage(message, type) {
  const promoMessage = document.getElementById("promoMessage");
  if (promoMessage) {
    promoMessage.textContent = message;
    promoMessage.className = `promo-message ${type}`;
    promoMessage.style.display = "block";
  }
}

// ========================================
// SHIPPING
// ========================================

function calculateShipping() {
  const postalCode = document.getElementById("postalCode");
  if (postalCode && postalCode.value.trim()) {
    showToast("Đang tính toán phí vận chuyển...");
    setTimeout(() => {
      showToast("Phí vận chuyển đã được cập nhật!");
      const cart = getCartFromStorage();
      updateOrderSummary(cart);
    }, 1000);
  } else {
    showToast("Vui lòng nhập mã bưu chính!");
  }
}

// ========================================
// SUGGESTED PRODUCTS
// ========================================

async function loadSuggestedProducts() {
  try {
    console.log("🛍️ Loading suggested products...");

    const suggestedContainer = document.getElementById("suggestedProductsGrid");
    if (!suggestedContainer) {
      console.log("❌ Suggested products container not found");
      return;
    }

    // Show loading state
    suggestedContainer.innerHTML = `
      <div class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Đang tải...</span>
        </div>
        <p class="mt-2 text-muted">Đang tải sản phẩm gợi ý...</p>
      </div>
    `;

    // Fetch suggested products from API
    const response = await fetch(
      "../../controller/controller_User/cart_api.php?action=suggested"
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (!data.success || !data.products || data.products.length === 0) {
      suggestedContainer.innerHTML = `
        <div class="text-center py-4">
          <p class="text-muted">Không có sản phẩm gợi ý nào.</p>
        </div>
      `;
      return;
    }

    console.log(`✅ Loaded ${data.products.length} suggested products`);

    // Generate HTML for suggested products
    const productsHTML = data.products
      .map((product) => createSuggestedProductHTML(product))
      .join("");

    suggestedContainer.innerHTML = `
      <div class="row g-3">
        ${productsHTML}
      </div>
    `;

    console.log("🎉 Suggested products displayed successfully!");
  } catch (error) {
    console.error("❌ Error loading suggested products:", error);

    const suggestedContainer = document.getElementById("suggestedProductsGrid");
    if (suggestedContainer) {
      suggestedContainer.innerHTML = `
        <div class="text-center py-4">
          <p class="text-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Không thể tải sản phẩm gợi ý. Vui lòng thử lại sau.
          </p>
        </div>
      `;
    }
  }
}

function createSuggestedProductHTML(product) {
  const hasDiscount = product.discount > 0;
  const discountBadge = hasDiscount
    ? `<div class="discount-badge">-${product.discount}%</div>`
    : "";

  return `
    <div class="col-lg-3 col-md-4 col-sm-6">
      <div class="product-card card h-100 border-0 shadow-sm">
        ${discountBadge}
        <div class="image-container">
          <a href="product_detail.php?id=${product.id}">
            <img src="${product.image}" class="product-image" 
                 alt="${product.name}" 
                 style="display: block !important; background: none !important;"
                 onload="console.log('✅ Image loaded: ${product.image}')"
                 onerror="console.log('❌ Image failed: ${
                   product.image
                 }'); this.src='../../Images/product/placeholder.jpg';">
          </a>
        </div>
        <div class="card-body d-flex flex-column">
          <h5 class="card-title">
            <a href="product_detail.php?id=${
              product.id
            }" class="text-decoration-none text-dark">
              ${product.name}
            </a>
          </h5>
          <p class="text-muted small mb-2">Snowboard</p>
          <div class="price-box mt-auto">
            ${
              hasDiscount
                ? `<span class="original-price text-muted text-decoration-line-through me-2">
                    ${formatCurrency(product.originalPrice)}
                   </span>
                   <span class="final-price fw-bold fs-5" style="color: #212529 !important;">
                    ${formatCurrency(product.price)}
                   </span>`
                : `<span class="final-price fw-bold fs-5" style="color: #212529 !important;">
                    ${formatCurrency(product.price)}
                   </span>`
            }
          </div>
          <button class="btn btn-dark btn-sm mt-3" onclick="addSuggestedToCart(${
            product.id
          }, '${product.name}', ${product.price}, '${product.image}')">
            <i class="fas fa-cart-plus me-1"></i>Thêm vào giỏ
          </button>
        </div>
      </div>
    </div>
  `;
}

// ========================================
// SUGGESTED PRODUCTS FUNCTIONS
// ========================================

function addSuggestedToCart(productId, productName, price, image) {
  console.log(`🛒 Adding suggested product to cart: ${productName}`);

  const cart = getCartFromStorage();

  // Check if product already exists in cart
  const existingItemIndex = cart.findIndex((item) => item.id === productId);

  if (existingItemIndex !== -1) {
    // Update quantity if product exists
    cart[existingItemIndex].quantity += 1;
    showToast(`Đã cập nhật số lượng "${productName}" trong giỏ hàng!`);
  } else {
    // Add new product to cart
    const newItem = {
      id: productId,
      name: productName,
      price: price,
      quantity: 1,
      image: image,
      color: "Đen", // Default color
      size: "M", // Default size
    };

    cart.push(newItem);
    showToast(`Đã thêm "${productName}" vào giỏ hàng!`);
  }

  // Save and refresh cart
  saveCartToStorage(cart);
  updateCartCount();
  initializeCart();

  console.log(`✅ Added suggested product to cart successfully`);
}

function viewProduct(productId) {
  console.log(`👁️ Viewing product: ${productId}`);
  window.open(`product_detail.php?id=${productId}`, "_blank");
}

// ========================================
// CHECKOUT
// ========================================

function proceedToCheckout() {
  const cart = getCartFromStorage();
  if (cart.length === 0) {
    showToast("Giỏ hàng của bạn đang trống!");
    return;
  }

  showToast("Đang chuyển đến trang thanh toán...");
  setTimeout(() => {
    window.location.href = "checkout.php";
  }, 1500);
}

// ========================================
// UTILITIES
// ========================================

function formatCurrency(amount) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
}

function showToast(message) {
  console.log("Toast:", message);

  // Create toast if Bootstrap is available
  if (typeof bootstrap !== "undefined") {
    let toastContainer = document.querySelector(".toast-container");
    if (!toastContainer) {
      toastContainer = document.createElement("div");
      toastContainer.className =
        "toast-container position-fixed top-0 end-0 p-3";
      document.body.appendChild(toastContainer);
    }

    const toastId = "toast-" + Date.now();
    const toastHTML = `
      <div id="${toastId}" class="toast" role="alert">
        <div class="toast-header">
          <i class="fas fa-check-circle text-success me-2"></i>
          <strong class="me-auto">Thông báo</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      </div>
    `;

    toastContainer.insertAdjacentHTML("beforeend", toastHTML);

    const toastElement = document.getElementById(toastId);
    const bsToast = new bootstrap.Toast(toastElement);
    bsToast.show();

    toastElement.addEventListener("hidden.bs.toast", () => {
      toastElement.remove();
    });
  } else {
    // Fallback to alert if Bootstrap not available
    alert(message);
  }
}
