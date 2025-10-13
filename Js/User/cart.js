// ========================================
// CART PAGE - JAVASCRIPT
// ========================================

// Global variable to cache suggested products data
let suggestedProductsCache = [];

document.addEventListener("DOMContentLoaded", function () {
  initializeCart();
  loadSuggestedProducts();
  updateCartCount();
});

// ========================================
// CART INITIALIZATION
// ========================================

function initializeCart() {
  const cart = getCartFromStorage();
  displayCartItems(cart);
  calculateTotals(cart);

  // Show/hide sections based on cart content
  if (cart.length === 0) {
    showEmptyCart();
  } else {
    showCartContent();
  }
}

function getCartFromStorage() {
  try {
    return JSON.parse(localStorage.getItem("cart")) || [];
  } catch (error) {
    console.error("Error parsing cart data:", error);
    return [];
  }
}

function saveCartToStorage(cart) {
  localStorage.setItem("cart", JSON.stringify(cart));
  updateCartCount();
}

// ========================================
// CART DISPLAY
// ========================================

function displayCartItems(cart) {
  const cartItemsContainer = document.getElementById("cartItems");
  cartItemsContainer.innerHTML = "";

  if (cart.length === 0) {
    return;
  }

  cart.forEach((item, index) => {
    const cartItemHTML = createCartItemHTML(item, index);
    cartItemsContainer.appendChild(cartItemHTML);
  });
}

function createCartItemHTML(item, index) {
  const cartItem = document.createElement("div");
  cartItem.className = "cart-item";
  cartItem.dataset.index = index;

  // Calculate item total
  const itemTotal = item.price * item.quantity;

  cartItem.innerHTML = `
        <div class="row align-items-center">
            <!-- Product Info Column -->
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="cart-item-image me-3">
                        <img src="${item.image}" alt="${item.name}" 
                             onerror="this.src='/Web_TMDT/Images/product/placeholder.jpg'">
                    </div>
                    <div class="cart-item-info">
                        <div class="cart-item-name" onclick="viewProduct(${item.id})">${item.name}</div>
                        <div class="cart-item-details">
                            <small class="text-muted">
                                Màu: ${item.color || "Đen"}${item.size ? " | Size: " + item.size : ""}
                            </small>
                        </div>
                        <div class="cart-item-actions mt-2">
                            <a href="#" class="action-link update-link" onclick="updateCartItem(${index})">cập nhật</a>
                            <a href="#" class="action-link remove-link ms-3" onclick="removeCartItem(${index})">xóa</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Price Column -->
            <div class="col-md-2 text-center">
                <div class="cart-item-price">
                    ${formatCurrency(item.price)}
                </div>
            </div>
            
            <!-- Quantity Column -->
            <div class="col-md-2 text-center">
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="changeQuantity(${index}, -1)" ${item.quantity <= 1 ? "disabled" : ""}>
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" class="quantity-input" value="${item.quantity}" 
                           onchange="setQuantity(${index}, this.value)" min="1" max="99">
                    <button class="quantity-btn" onclick="changeQuantity(${index}, 1)">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            
            <!-- Total Column -->
            <div class="col-md-2 text-center">
                <div class="cart-item-total fw-bold">
                    ${formatCurrency(itemTotal)}
                </div>
            </div>
        </div>
    `;

  return cartItem;
}
                </button>
            </div>
            
            <div class="cart-item-total">${formatCurrency(itemTotal)}</div>
            
            <button class="remove-item-btn" onclick="removeCartItem(${index})" title="Xóa sản phẩm">
                <i class="fas fa-times"></i>
            </button>
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
    cart[index].quantity = Math.max(
      1,
      Math.min(99, cart[index].quantity + change)
    );
    saveCartToStorage(cart);
    initializeCart();
    showToast("Đã cập nhật số lượng!");
  }
}

function setQuantity(index, newQuantity) {
  const cart = getCartFromStorage();
  const quantity = Math.max(1, Math.min(99, parseInt(newQuantity) || 1));

  if (cart[index]) {
    cart[index].quantity = quantity;
    saveCartToStorage(cart);
    initializeCart();
    showToast("Đã cập nhật số lượng!");
  }
}

function removeCartItem(index) {
  if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?")) {
    const cart = getCartFromStorage();
    cart.splice(index, 1);
    saveCartToStorage(cart);
    initializeCart();
    showToast("Đã xóa sản phẩm khỏi giỏ hàng!");
  }
}

function clearCart() {
  if (confirm("Bạn có chắc chắn muốn xóa tất cả sản phẩm trong giỏ hàng?")) {
    localStorage.removeItem("cart");
    initializeCart();
    showToast("Đã xóa tất cả sản phẩm khỏi giỏ hàng!");
  }
}

function updateItemQuantity(index, change) {
  changeQuantity(index, change);
}

// ========================================
// CART CALCULATIONS
// ========================================

function calculateTotals(cart) {
  let subtotal = 0;

  cart.forEach((item) => {
    subtotal += item.price * item.quantity;
  });

  const shipping = subtotal >= 500000 ? 0 : 30000; // Free shipping over 500k VND
  const discount = calculateDiscount(subtotal);
  const total = subtotal + shipping - discount;

  // Update UI
  document.getElementById("subtotal").textContent = formatCurrency(subtotal);
  document.getElementById("shipping").textContent = formatCurrency(shipping);
  document.getElementById("discount").textContent =
    "-" + formatCurrency(discount);
  document.getElementById("total").textContent = formatCurrency(total);

  // Show/hide discount row
  const discountRow = document.getElementById("discountRow");
  if (discount > 0) {
    discountRow.style.display = "flex";
  } else {
    discountRow.style.display = "none";
  }

  // Update shipping info
  updateShippingInfo(subtotal, shipping);
}

function calculateDiscount(subtotal) {
  const promoCode = document
    .getElementById("promoCode")
    .value.trim()
    .toUpperCase();

  // Predefined promo codes
  const promoCodes = {
    WELCOME10: { type: "percentage", value: 10, minOrder: 200000 },
    SAVE50K: { type: "fixed", value: 50000, minOrder: 300000 },
    SNOWBOARD20: { type: "percentage", value: 20, minOrder: 500000 },
    FREESHIP: { type: "shipping", value: 30000, minOrder: 100000 },
  };

  if (promoCodes[promoCode] && subtotal >= promoCodes[promoCode].minOrder) {
    const promo = promoCodes[promoCode];
    if (promo.type === "percentage") {
      return (subtotal * promo.value) / 100;
    } else if (promo.type === "fixed") {
      return promo.value;
    }
  }

  return 0;
}

function updateShippingInfo(subtotal, shipping) {
  const shippingElement = document.getElementById("shipping");
  const shippingText =
    shippingElement.parentElement.querySelector("span:first-child");

  if (shipping === 0) {
    shippingElement.innerHTML = '<span class="text-success">Miễn phí</span>';
    shippingText.innerHTML =
      '<i class="fas fa-truck text-success me-1"></i>Phí vận chuyển:';
  } else {
    const remaining = 500000 - subtotal;
    shippingElement.textContent = formatCurrency(shipping);
    shippingText.innerHTML = `<i class="fas fa-truck me-1"></i>Phí vận chuyển: 
                                 <small class="text-muted">(Mua thêm ${formatCurrency(
                                   remaining
                                 )} để được miễn phí)</small>`;
  }
}

// ========================================
// PROMO CODE
// ========================================

function applyPromoCode() {
  const promoCode = document
    .getElementById("promoCode")
    .value.trim()
    .toUpperCase();
  const promoMessage = document.getElementById("promoMessage");

  if (!promoCode) {
    showPromoMessage("Vui lòng nhập mã giảm giá!", "error");
    return;
  }

  const cart = getCartFromStorage();
  const subtotal = cart.reduce(
    (sum, item) => sum + item.price * item.quantity,
    0
  );

  const promoCodes = {
    WELCOME10: {
      type: "percentage",
      value: 10,
      minOrder: 200000,
      name: "Giảm 10%",
    },
    SAVE50K: {
      type: "fixed",
      value: 50000,
      minOrder: 300000,
      name: "Giảm 50,000₫",
    },
    SNOWBOARD20: {
      type: "percentage",
      value: 20,
      minOrder: 500000,
      name: "Giảm 20%",
    },
    FREESHIP: {
      type: "shipping",
      value: 30000,
      minOrder: 100000,
      name: "Miễn phí vận chuyển",
    },
  };

  if (promoCodes[promoCode]) {
    const promo = promoCodes[promoCode];
    if (subtotal >= promo.minOrder) {
      showPromoMessage(
        `Áp dụng thành công mã "${promoCode}" - ${promo.name}!`,
        "success"
      );
      calculateTotals(cart);
    } else {
      showPromoMessage(
        `Mã "${promoCode}" yêu cầu đơn hàng tối thiểu ${formatCurrency(
          promo.minOrder
        )}!`,
        "error"
      );
    }
  } else {
    showPromoMessage("Mã giảm giá không hợp lệ!", "error");
  }
}

function showPromoMessage(message, type) {
  const promoMessage = document.getElementById("promoMessage");
  promoMessage.textContent = message;
  promoMessage.className = `promo-message ${type}`;
  promoMessage.style.display = "block";

  // Hide after 5 seconds
  setTimeout(() => {
    promoMessage.style.display = "none";
  }, 5000);
}

// ========================================
// UI CONTROL
// ========================================

function showEmptyCart() {
  document.getElementById("emptyCart").style.display = "block";
  document.getElementById("cartItems").style.display = "none";
  document.getElementById("continueShoppingSection").style.display = "none";
  document.getElementById("orderSummaryContainer").style.display = "none";
  document.getElementById("clearCartBtn").style.display = "none";
}

function showCartContent() {
  document.getElementById("emptyCart").style.display = "none";
  document.getElementById("cartItems").style.display = "block";
  document.getElementById("continueShoppingSection").style.display = "block";
  document.getElementById("orderSummaryContainer").style.display = "block";
  document.getElementById("clearCartBtn").style.display = "inline-block";
}

function updateCartCount() {
  const cart = getCartFromStorage();
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  const cartBadge = document.getElementById("cart-count");

  if (cartBadge) {
    cartBadge.textContent = totalItems;
    cartBadge.style.display = totalItems > 0 ? "inline-block" : "none";
  }
}

// ========================================
// SUGGESTED PRODUCTS
// ========================================

async function loadSuggestedProducts() {
  try {
    // Load and cache suggested products
    const suggestedProducts = await getSuggestedProducts();
    // Cache the data globally for use in addSuggestedToCart
    suggestedProductsCache = suggestedProducts;
    displaySuggestedProducts(suggestedProducts);
  } catch (error) {
    console.error("Error loading suggested products:", error);
  }
}

async function getSuggestedProducts() {
  try {
    // Load suggested products from API
    const response = await fetch(
      "/Web_TMDT/controller/controller_User/cart_api.php?action=suggested"
    );
    const data = await response.json();

    if (data.success) {
      return data.products;
    } else {
      throw new Error("Failed to load suggested products");
    }
  } catch (error) {
    console.error("Error loading suggested products:", error);

    // Fallback to hardcoded products with proper image paths
    return [
      {
        id: 1,
        name: "Burton Custom Snowboard",
        price: 2500000,
        originalPrice: 3000000,
        image: "/Web_TMDT/Images/product/placeholder.jpg",
      },
      {
        id: 2,
        name: "K2 Snowboard Boots",
        price: 1800000,
        originalPrice: 2200000,
        image: "/Web_TMDT/Images/product/placeholder.jpg",
      },
      {
        id: 3,
        name: "Oakley Goggles",
        price: 800000,
        originalPrice: 1000000,
        image: "/Web_TMDT/Images/product/placeholder.jpg",
      },
      {
        id: 4,
        name: "Snowboard Gloves",
        price: 450000,
        originalPrice: 600000,
        image: "/Web_TMDT/Images/product/placeholder.jpg",
      },
    ];
  }
}

function displaySuggestedProducts(products) {
  const grid = document.getElementById("suggestedProductsGrid");
  grid.innerHTML = "";

  products.forEach((product) => {
    const productHTML = createSuggestedProductHTML(product);
    grid.appendChild(productHTML);
  });
}

function createSuggestedProductHTML(product) {
  const productElement = document.createElement("div");
  productElement.className = "suggested-product";

  const hasDiscount = product.originalPrice > product.price;
  const discountPercent = hasDiscount
    ? Math.round((1 - product.price / product.originalPrice) * 100)
    : 0;

  productElement.innerHTML = `
        <div class="suggested-product-image">
            <img src="${product.image}" alt="${product.name}" 
                 onerror="this.src='/Web_TMDT/Images/product/placeholder.jpg'">
        </div>
        <div class="suggested-product-name">${product.name}</div>
        <div class="suggested-product-price">
            ${formatCurrency(product.price)}
            ${
              hasDiscount
                ? `<span class="suggested-product-original-price">${formatCurrency(
                    product.originalPrice
                  )}</span>`
                : ""
            }
        </div>
        ${
          hasDiscount
            ? `<div class="badge bg-danger mb-2">-${discountPercent}%</div>`
            : ""
        }
        <button class="btn btn-outline-primary btn-sm" onclick="addSuggestedToCart(${
          product.id
        })">
            <i class="fas fa-cart-plus me-1"></i>Thêm vào giỏ
        </button>
    `;

  return productElement;
}

async function addSuggestedToCart(productId) {
  try {
    // Use cached data first, fallback to API if needed
    let productData = suggestedProductsCache.find((p) => p.id === productId);

    // If not found in cache, fetch from API
    if (!productData) {
      const response = await fetch(
        `/Web_TMDT/controller/controller_User/cart_api.php?action=suggested`
      );
      const data = await response.json();

      if (data.success) {
        productData = data.products.find((p) => p.id === productId);
      }
    }

    // If still not found, use fallback data
    if (!productData) {
      productData = {
        id: productId,
        name: `Sản phẩm ${productId}`,
        price: 500000,
        image: `/Web_TMDT/Images/product/placeholder.jpg`,
      };
    }

    const cart = getCartFromStorage();

    // Check if product already exists
    const existingIndex = cart.findIndex((item) => item.id === productId);

    if (existingIndex !== -1) {
      cart[existingIndex].quantity += 1;
    } else {
      // Add new product with real data
      const newItem = {
        id: productData.id,
        name: productData.name,
        price: productData.price,
        quantity: 1,
        image: productData.image,
        color: "Đen",
        size: "M",
      };
      cart.push(newItem);
    }

    saveCartToStorage(cart);
    initializeCart();
    showToast("Đã thêm sản phẩm vào giỏ hàng!");
  } catch (error) {
    console.error("Error adding suggested product to cart:", error);
    showToast("Có lỗi xảy ra khi thêm sản phẩm!");
  }
}

// ========================================
// NAVIGATION
// ========================================

function viewProduct(productId) {
  window.location.href = `product_detail.php?id=${productId}`;
}

function proceedToCheckout() {
  const cart = getCartFromStorage();

  if (cart.length === 0) {
    showToast(
      "Giỏ hàng trống! Vui lòng thêm sản phẩm trước khi thanh toán.",
      "error"
    );
    return;
  }

  // Check if user is logged in
  if (!isUserLoggedIn()) {
    if (
      confirm("Bạn cần đăng nhập để thanh toán. Chuyển đến trang đăng nhập?")
    ) {
      window.location.href = "login.php?redirect=cart.php";
    }
    return;
  }

  // Proceed to checkout
  window.location.href = "checkout.php";
}

function isUserLoggedIn() {
  // Check if user session exists (simplified check)
  return document.querySelector(".dropdown-toggle") !== null;
}

// ========================================
// UTILITIES
// ========================================

function formatCurrency(amount) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
    minimumFractionDigits: 0,
  }).format(amount);
}

function showToast(message, type = "success") {
  const toast = document.getElementById("cartToast");
  const toastBody = toast.querySelector(".toast-body");
  const toastHeader = toast.querySelector(".toast-header");

  // Update content
  toastBody.textContent = message;

  // Update style based on type
  const icon = toastHeader.querySelector("i");
  if (type === "error") {
    icon.className = "fas fa-exclamation-circle text-danger me-2";
    toastHeader.querySelector("strong").textContent = "Lỗi";
  } else {
    icon.className = "fas fa-check-circle text-success me-2";
    toastHeader.querySelector("strong").textContent = "Thành công";
  }

  // Show toast
  const bsToast = new bootstrap.Toast(toast);
  bsToast.show();
}

// ========================================
// EVENT LISTENERS
// ========================================

// Listen for cart updates from other pages
window.addEventListener("storage", function (e) {
  if (e.key === "cart") {
    initializeCart();
  }
});

// Listen for promo code input enter key
document.getElementById("promoCode").addEventListener("keypress", function (e) {
  if (e.key === "Enter") {
    e.preventDefault();
    applyPromoCode();
  }
});

// ========================================
// INITIALIZATION
// ========================================

console.log("🛒 Cart page loaded successfully");
console.log("Current cart items:", getCartFromStorage().length);
