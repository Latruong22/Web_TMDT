// ========================================
// CHECKOUT PAGE - JAVASCRIPT
// ========================================

// Biến toàn cục
let cart = [];
let appliedVoucher = null;
const SHIPPING_FEE = 30000; // Phí ship cố định 30,000đ

// ========================================
// KHỞI TẠO TRANG
// ========================================

document.addEventListener("DOMContentLoaded", function () {
  initializeCheckout();
  setupEventListeners();
  updateCartCount();
});

// ========================================
// KHỞI TẠO CHECKOUT
// ========================================

function initializeCheckout() {
  // Load giỏ hàng từ localStorage
  cart = getCartFromStorage();

  // Kiểm tra giỏ hàng trống
  if (cart.length === 0) {
    showToast("Giỏ hàng trống! Chuyển về trang giỏ hàng...", "error");
    setTimeout(() => {
      window.location.href = "cart.php";
    }, 2000);
    return;
  }

  // LOAD VOUCHER từ localStorage nếu có
  loadVoucherFromStorage();

  // Hiển thị sản phẩm và tính tổng
  displayOrderItems();
  calculateTotals();
}

// ========================================
// LOAD VOUCHER TỪ LOCALSTORAGE
// ========================================

function loadVoucherFromStorage() {
  const savedVoucher = localStorage.getItem("appliedVoucher");
  if (savedVoucher) {
    try {
      appliedVoucher = JSON.parse(savedVoucher);
      console.log("✅ Voucher từ cart đã được load:", appliedVoucher);

      // Hiển thị voucher trong UI
      const voucherInput = document.getElementById("voucherCode");
      const voucherMessage = document.getElementById("voucherMessage");

      if (voucherInput) {
        voucherInput.value = appliedVoucher.code;
      }

      if (voucherMessage) {
        const discountText =
          appliedVoucher.type === "percent"
            ? `${appliedVoucher.discount}%`
            : formatCurrency(appliedVoucher.discount);
        voucherMessage.textContent = `✓ Mã "${appliedVoucher.code}" đang được áp dụng (Giảm ${discountText})`;
        voucherMessage.className = "success";
        voucherMessage.style.display = "block";
      }
    } catch (error) {
      console.error("❌ Lỗi khi load voucher:", error);
      appliedVoucher = null;
    }
  }
}

// ========================================
// LẤY GIỎ HÀNG TỪ STORAGE
// ========================================

function getCartFromStorage() {
  try {
    return JSON.parse(localStorage.getItem("cart")) || [];
  } catch (error) {
    console.error("Lỗi khi đọc giỏ hàng:", error);
    return [];
  }
}

// ========================================
// HIỂN THỊ SẢN PHẨM TRONG ĐƠN HÀNG
// ========================================

function displayOrderItems() {
  const orderItemsContainer = document.getElementById("orderItems");
  orderItemsContainer.innerHTML = "";

  cart.forEach((item) => {
    const orderItemHTML = createOrderItemHTML(item);
    orderItemsContainer.insertAdjacentHTML("beforeend", orderItemHTML);
  });
}

function createOrderItemHTML(item) {
  return `
    <div class="order-item">
      <div class="order-item-image">
        <img src="${item.image}" alt="${item.name}" 
             onerror="this.src='/Web_TMDT/Images/product/placeholder.jpg'">
      </div>
      <div class="order-item-info">
        <div class="order-item-name">${item.name}</div>
        <div class="order-item-details">
          <span class="item-quantity">SL: ${item.quantity}</span>
        </div>
      </div>
      <div class="order-item-price">
        <div class="item-price">${formatCurrency(
          item.price * item.quantity
        )}</div>
      </div>
    </div>
  `;
}

// ========================================
// TÍNH TỔNG GIÁ TRỊ ĐƠN HÀNG
// ========================================

function calculateTotals() {
  // Tính tạm tính (subtotal)
  const subtotal = cart.reduce(
    (total, item) => total + item.price * item.quantity,
    0
  );

  // Phí vận chuyển
  const shippingFee = SHIPPING_FEE;

  // Tính giảm giá nếu có voucher
  let discount = 0;
  if (appliedVoucher) {
    if (appliedVoucher.type === "percent") {
      discount = (subtotal * appliedVoucher.discount) / 100;
    } else {
      // type = 'fixed'
      discount = appliedVoucher.discount;
    }
  }

  // Tổng cộng
  const total = subtotal + shippingFee - discount;

  // Hiển thị
  document.getElementById("subtotal").textContent = formatCurrency(subtotal);
  document.getElementById("shippingFee").textContent =
    formatCurrency(shippingFee);
  document.getElementById("totalPrice").textContent = formatCurrency(total);

  // Hiển thị dòng giảm giá nếu có
  const discountRow = document.getElementById("discountRow");
  if (discount > 0) {
    discountRow.style.display = "flex";
    document.getElementById("discount").textContent =
      "-" + formatCurrency(discount);
  } else {
    discountRow.style.display = "none";
  }
}

// ========================================
// XỬ LÝ VOUCHER
// ========================================

function applyVoucher() {
  const voucherCode = document.getElementById("voucherCode").value.trim();
  const messageDiv = document.getElementById("voucherMessage");

  if (!voucherCode) {
    showVoucherMessage("Vui lòng nhập mã giảm giá", "error");
    return;
  }

  // Hiển thị loading
  const applyBtn = document.getElementById("applyVoucherBtn");
  applyBtn.disabled = true;
  applyBtn.innerHTML =
    '<i class="fas fa-spinner fa-spin me-1"></i>Đang kiểm tra...';

  // Gọi API kiểm tra voucher
  fetch("../../controller/controller_User/voucher_controller.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=validate_voucher&code=${encodeURIComponent(voucherCode)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        appliedVoucher = {
          code: voucherCode,
          discount: data.discount,
          type: data.type,
        };

        // LƯU VOUCHER VÀO LOCALSTORAGE
        localStorage.setItem("appliedVoucher", JSON.stringify(appliedVoucher));
        console.log("✅ Voucher đã lưu vào localStorage:", appliedVoucher);

        showVoucherMessage(
          `Áp dụng thành công! Giảm ${
            data.type === "percent"
              ? data.discount + "%"
              : formatCurrency(data.discount)
          }`,
          "success"
        );
        calculateTotals();
      } else {
        // XÓA VOUCHER KHỎI LOCALSTORAGE nếu không hợp lệ
        appliedVoucher = null;
        localStorage.removeItem("appliedVoucher");

        showVoucherMessage(data.message || "Mã giảm giá không hợp lệ", "error");
      }
    })
    .catch((error) => {
      console.error("Lỗi khi kiểm tra voucher:", error);
      showVoucherMessage("Có lỗi xảy ra. Vui lòng thử lại", "error");
    })
    .finally(() => {
      applyBtn.disabled = false;
      applyBtn.innerHTML = '<i class="fas fa-tag me-1"></i>Áp dụng';
    });
}

function showVoucherMessage(message, type) {
  const messageDiv = document.getElementById("voucherMessage");
  messageDiv.textContent = message;
  messageDiv.className = type;
}

// ========================================
// ĐẶT HÀNG
// ========================================

function placeOrder() {
  // Validate form
  const form = document.getElementById("checkoutForm");
  if (!form.checkValidity()) {
    form.classList.add("was-validated");
    showToast("Vui lòng điền đầy đủ thông tin", "error");
    return;
  }

  // Lấy dữ liệu form
  const formData = new FormData(form);
  const fullname = formData.get("fullname");
  const email = formData.get("email");
  const phone = formData.get("phone");
  const address = formData.get("address");
  const note = formData.get("note") || "";

  // Validate phone
  if (!validatePhone(phone)) {
    showToast("Số điện thoại không hợp lệ", "error");
    return;
  }

  // Lấy payment method
  const paymentMethod = document.querySelector(
    'input[name="payment_method"]:checked'
  ).value;

  // Tính tổng tiền
  const subtotal = cart.reduce(
    (total, item) => total + item.price * item.quantity,
    0
  );
  const shippingFee = SHIPPING_FEE;
  let discount = 0;
  if (appliedVoucher) {
    discount =
      appliedVoucher.type === "percent"
        ? (subtotal * appliedVoucher.discount) / 100
        : appliedVoucher.discount;
  }
  const total = subtotal + shippingFee - discount;

  // Chuẩn bị dữ liệu đơn hàng
  const orderData = {
    action: "create_order",
    fullname: fullname,
    email: email,
    phone: phone,
    shipping_address: address,
    note: note,
    payment_method: paymentMethod,
    voucher_code: appliedVoucher ? appliedVoucher.code : "",
    cart: JSON.stringify(cart),
    subtotal: subtotal,
    shipping_fee: shippingFee,
    discount: discount,
    total: total,
  };

  // Hiển thị loading
  showLoading();
  const placeOrderBtn = document.getElementById("placeOrderBtn");
  placeOrderBtn.disabled = true;

  // Gửi request tạo đơn hàng
  fetch("../../controller/controller_User/checkout_controller.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams(orderData).toString(),
  })
    .then((response) => response.json())
    .then((data) => {
      hideLoading();
      if (data.success) {
        // Xóa giỏ hàng và voucher đã sử dụng
        localStorage.removeItem("cart");
        localStorage.removeItem("appliedVoucher");
        console.log("✅ Đã xóa cart và voucher sau khi đặt hàng thành công");

        showToast("Đặt hàng thành công!", "success");

        // Chuyển đến trang success hoặc order detail
        setTimeout(() => {
          window.location.href = `order_tracking.php?order_id=${data.order_id}&success=1`;
        }, 1500);
      } else {
        placeOrderBtn.disabled = false;
        showToast(data.message || "Có lỗi xảy ra. Vui lòng thử lại", "error");
      }
    })
    .catch((error) => {
      hideLoading();
      placeOrderBtn.disabled = false;
      console.error("Lỗi khi đặt hàng:", error);
      showToast("Có lỗi xảy ra. Vui lòng thử lại", "error");
    });
}

// ========================================
// VALIDATION
// ========================================

function validatePhone(phone) {
  // Regex cho số điện thoại VN (10-11 số, bắt đầu bằng 0)
  const phoneRegex = /^0[0-9]{9,10}$/;
  return phoneRegex.test(phone);
}

// ========================================
// EVENT LISTENERS
// ========================================

function setupEventListeners() {
  // Apply voucher button
  const applyVoucherBtn = document.getElementById("applyVoucherBtn");
  if (applyVoucherBtn) {
    applyVoucherBtn.addEventListener("click", applyVoucher);
  }

  // Voucher input - Enter key
  const voucherInput = document.getElementById("voucherCode");
  if (voucherInput) {
    voucherInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        applyVoucher();
      }
    });
  }

  // Place order button
  const placeOrderBtn = document.getElementById("placeOrderBtn");
  if (placeOrderBtn) {
    placeOrderBtn.addEventListener("click", placeOrder);
  }

  // Payment method selection
  const paymentMethods = document.querySelectorAll(".payment-method");
  paymentMethods.forEach((method) => {
    const radio = method.querySelector('input[type="radio"]');
    if (radio && !radio.disabled) {
      method.addEventListener("click", function () {
        paymentMethods.forEach((m) => m.classList.remove("active"));
        this.classList.add("active");
        radio.checked = true;
      });
    }
  });

  // Form validation real-time
  const form = document.getElementById("checkoutForm");
  const inputs = form.querySelectorAll("input, textarea");
  inputs.forEach((input) => {
    input.addEventListener("blur", function () {
      if (!this.checkValidity()) {
        this.classList.add("is-invalid");
      } else {
        this.classList.remove("is-invalid");
      }
    });

    input.addEventListener("input", function () {
      if (this.classList.contains("is-invalid") && this.checkValidity()) {
        this.classList.remove("is-invalid");
      }
    });
  });
}

// ========================================
// HELPER FUNCTIONS
// ========================================

// Format tiền tệ
function formatCurrency(amount) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
}

// Cập nhật số lượng giỏ hàng trong badge
function updateCartCount() {
  const cart = getCartFromStorage();
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  const cartBadge = document.getElementById("cart-count");
  if (cartBadge) {
    cartBadge.textContent = totalItems;
  }
}

// Hiển thị toast notification
function showToast(message, type = "info") {
  const toast = document.getElementById("toast");
  toast.textContent = message;
  toast.className = `toast-notification ${type} show`;

  setTimeout(() => {
    toast.classList.remove("show");
  }, 3000);
}

// Hiển thị loading overlay
function showLoading() {
  const overlay = document.getElementById("loadingOverlay");
  overlay.style.display = "flex";
}

// Ẩn loading overlay
function hideLoading() {
  const overlay = document.getElementById("loadingOverlay");
  overlay.style.display = "none";
}

// ========================================
// UTILITY FUNCTIONS
// ========================================

// Format số điện thoại khi nhập
document.getElementById("phone").addEventListener("input", function (e) {
  // Chỉ cho phép số
  this.value = this.value.replace(/[^0-9]/g, "");

  // Giới hạn 11 số
  if (this.value.length > 11) {
    this.value = this.value.slice(0, 11);
  }
});

// Auto-resize textarea
document.getElementById("note").addEventListener("input", function () {
  this.style.height = "auto";
  this.style.height = this.scrollHeight + "px";
});

// Scroll to top when page loads
window.scrollTo(0, 0);
