// ========== TRANG DANH SÁCH SẢN PHẨM - JAVASCRIPT ==========

// Khởi tạo khi trang load
document.addEventListener("DOMContentLoaded", function () {
  updateCartCount();
  initializeScrollAnimations();
  initializeImageLoading();
});

// ========== HÀM GIỎ HÀNG ==========

/**
 * Thêm sản phẩm vào giỏ hàng
 */
function addToCart(productId, quantity = 1) {
  // Lấy giỏ hàng từ localStorage (array format để sync với product_detail.js)
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  // Lấy thông tin sản phẩm từ trang
  const productCard = document
    .querySelector(`.product-card [href="product_detail.php?id=${productId}"]`)
    .closest(".product-card");

  if (!productCard) {
    console.error("Product card not found");
    return;
  }

  const productName = productCard
    .querySelector(".product-title a")
    .textContent.trim();
  const priceText = productCard
    .querySelector(".price-current")
    .textContent.replace(/[^\d]/g, "");
  const price = parseInt(priceText);
  const imageElement = productCard.querySelector(".product-image");
  const image = imageElement ? imageElement.getAttribute("src") : "";

  // Kiểm tra sản phẩm đã có trong giỏ chưa (array format)
  const existingIndex = cart.findIndex((item) => item.id === productId);

  if (existingIndex !== -1) {
    // Cập nhật số lượng sản phẩm có sẵn
    cart[existingIndex].quantity += quantity;
  } else {
    // Thêm sản phẩm mới vào giỏ
    cart.push({
      id: productId,
      name: productName,
      price: price,
      quantity: quantity,
      image: image,
    });
  }

  // Lưu vào localStorage
  localStorage.setItem("cart", JSON.stringify(cart));

  // Cập nhật số lượng giỏ hàng
  updateCartCount();

  // Hiển thị thông báo thành công
  showToast("Đã thêm sản phẩm vào giỏ hàng!");

  // Thêm hiệu ứng trực quan
  const btn = event.target.closest(".action-btn");
  if (btn) {
    btn.classList.add("added");
    setTimeout(() => {
      btn.classList.remove("added");
    }, 1000);
  }
}

/**
 * Cập nhật số lượng giỏ hàng trên thanh điều hướng
 */
function updateCartCount() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

  // Cập nhật cả 2 badge IDs để tương thích với cả 2 trang
  const cartBadges = [
    document.getElementById("cartCount"),
    document.getElementById("cart-count"),
  ];

  cartBadges.forEach((cartBadge) => {
    if (cartBadge) {
      cartBadge.textContent = totalItems;
      cartBadge.style.display = totalItems > 0 ? "inline-block" : "none";

      // Thêm hiệu ứng bounce
      if (totalItems > 0) {
        cartBadge.style.animation = "none";
        setTimeout(() => {
          cartBadge.style.animation = "cartBounce 0.5s ease";
        }, 10);
      }
    }
  });
}

// ========== THÔNG BÁO TOAST ==========

/**
 * Hiển thị thông báo toast
 */
function showToast(message) {
  const toastElement = document.getElementById("addToCartToast");
  if (!toastElement) return;

  const toastBody = toastElement.querySelector(".toast-body");
  if (toastBody) {
    toastBody.textContent = message;
  }

  const toast = new bootstrap.Toast(toastElement, {
    animation: true,
    autohide: true,
    delay: 3000,
  });

  toast.show();
}

// ========== HIỆU ỨNG CUỘN ==========

/**
 * Khởi tạo hiệu ứng cuộn cho thẻ sản phẩm
 */
function initializeScrollAnimations() {
  const productCards = document.querySelectorAll(".product-card");

  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry, index) => {
          if (entry.isIntersecting) {
            setTimeout(() => {
              entry.target.style.opacity = "1";
              entry.target.style.transform = "translateY(0)";
            }, index * 50); // Stagger animation
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.1,
        rootMargin: "0px 0px -50px 0px",
      }
    );

    productCards.forEach((card) => {
      card.style.opacity = "0";
      card.style.transform = "translateY(30px)";
      card.style.transition = "opacity 0.5s ease, transform 0.5s ease";
      observer.observe(card);
    });
  } else {
    // Dự phòng cho trình duyệt cũ
    productCards.forEach((card) => {
      card.style.opacity = "1";
      card.style.transform = "translateY(0)";
    });
  }
}

// ========== TẢI HÌNH ẢNH ==========

/**
 * Tải hình ảnh lazy load
 */
function initializeImageLoading() {
  const images = document.querySelectorAll(".product-image");

  if ("IntersectionObserver" in window) {
    const imageObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.style.opacity = "0";
          img.style.transition = "opacity 0.5s ease";

          // Thêm class đang tải
          img.closest(".product-image-wrapper").classList.add("loading");

          img.addEventListener("load", function () {
            img.style.opacity = "1";
            img.closest(".product-image-wrapper").classList.remove("loading");
            img.closest(".product-image-wrapper").classList.add("loaded");
          });

          // Nếu hình ảnh đã tải xong
          if (img.complete) {
            img.style.opacity = "1";
            img.closest(".product-image-wrapper").classList.remove("loading");
            img.closest(".product-image-wrapper").classList.add("loaded");
          }

          imageObserver.unobserve(img);
        }
      });
    });

    images.forEach((img) => imageObserver.observe(img));
  }
}

// ========== LỌC & TÌM KIẾM ==========

/**
 * Xóa tất cả bộ lọc
 */
function clearFilters() {
  window.location.href = "product_list.php";
}

/**
 * Xử lý thay đổi danh mục
 */
const categorySelect = document.getElementById("categorySelect");
if (categorySelect) {
  categorySelect.addEventListener("change", function () {
    // Tự động submit form khi chọn danh mục khác
    const searchInput = document.querySelector(
      '.search-box input[name="search"]'
    );
    if (!searchInput || !searchInput.value.trim()) {
      // Nếu không có từ khóa tìm kiếm, chuyển trang trực tiếp
      const categoryValue = this.value;
      if (categoryValue == 0) {
        window.location.href = "product_list.php";
      } else {
        window.location.href = "product_list.php?category=" + categoryValue;
      }
    }
  });
}

/**
 * Xử lý submit tìm kiếm
 */
const searchForm = document.querySelector(".filter-section form");
if (searchForm) {
  searchForm.addEventListener("submit", function (e) {
    const searchInput = this.querySelector('input[name="search"]');
    if (searchInput && !searchInput.value.trim()) {
      e.preventDefault();
      searchInput.focus();
    }
  });
}

// ========== CUỘN MƯỢT ==========
// Note: Back to top button is implemented in product_list.php inline script

// ========== PHÍM TẮT ==========

document.addEventListener("keydown", function (e) {
  // Nhấn '/' để focus vào ô tìm kiếm
  if (e.key === "/" && !e.ctrlKey && !e.metaKey) {
    e.preventDefault();
    const searchInput = document.querySelector(
      '.search-box input[type="text"]'
    );
    if (searchInput) {
      searchInput.focus();
    }
  }
});

// ========== TIỆN ÍCH ==========

/**
 * Định dạng số thành tiền tệ
 */
function formatCurrency(number) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(number);
}

/**
 * Hàm debounce
 */
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// ========== THÔNG TIN CONSOLE ==========

console.log(
  "%c🏂 Snowboard Shop - Danh Sách Sản Phẩm",
  "color: #000; font-size: 16px; font-weight: bold; padding: 10px;"
);
console.log(
  "%cPhím tắt: Nhấn '/' để tìm kiếm",
  "color: #666; font-size: 12px;"
);

// ========== SCROLL EFFECTS ==========

/**
 * Thêm class "scrolled" vào navbar khi cuộn
 */
let lastScroll = 0;
window.addEventListener("scroll", function () {
  const navbar = document.querySelector(".navbar");
  const currentScroll = window.scrollY;

  if (currentScroll > 50) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }

  lastScroll = currentScroll;
});

// ========== PERFORMANCE OPTIMIZATION ==========

/**
 * Lazy load images với Intersection Observer
 */
if ("loading" in HTMLImageElement.prototype) {
  // Nếu trình duyệt hỗ trợ lazy loading native
  const images = document.querySelectorAll('img[loading="lazy"]');
  images.forEach((img) => {
    img.src = img.dataset.src || img.src;
  });
} else {
  // Fallback cho trình duyệt cũ - đã có sẵn trong initializeImageLoading()
}

// ========== ACCESSIBILITY ==========

/**
 * Thêm ARIA labels và keyboard navigation
 */
document.addEventListener("DOMContentLoaded", function () {
  // Thêm keyboard support cho product cards
  const productCards = document.querySelectorAll(".product-card");
  productCards.forEach((card) => {
    card.setAttribute("tabindex", "0");
    card.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        const link = card.querySelector(".product-title a");
        if (link) link.click();
      }
    });
  });

  // Thêm keyboard support cho category links
  const categoryLinks = document.querySelectorAll(".category-link");
  categoryLinks.forEach((link) => {
    link.setAttribute("role", "button");
  });
});

// ========== ERROR HANDLING ==========

/**
 * Xử lý lỗi khi load hình ảnh thất bại
 */
document.addEventListener("DOMContentLoaded", function () {
  const images = document.querySelectorAll(".product-image");
  images.forEach((img) => {
    img.addEventListener("error", function () {
      this.src = "/Web_TMDT/Images/product/placeholder.jpg"; // Fallback image
      this.alt = "Hình ảnh không khả dụng";
      this.closest(".product-image-wrapper").classList.add("error");
    });
  });
});

// ========== SMOOTH SCROLL FOR ANCHOR LINKS ==========

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

// ========== LOCAL STORAGE HELPERS ==========

/**
 * Lưu lại vị trí cuộn khi chuyển trang
 */
window.addEventListener("beforeunload", function () {
  sessionStorage.setItem("scrollPosition", window.scrollY);
});

/**
 * Khôi phục vị trí cuộn khi quay lại
 */
window.addEventListener("load", function () {
  const scrollPos = sessionStorage.getItem("scrollPosition");
  if (scrollPos) {
    window.scrollTo(0, parseInt(scrollPos));
    sessionStorage.removeItem("scrollPosition");
  }
});
