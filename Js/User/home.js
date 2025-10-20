// Home page interactive features
document.addEventListener("DOMContentLoaded", function () {
  // Check if need to clear cart (khi login hoặc logout)
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("clear_cart") === "1") {
    // Clear cart và voucher từ localStorage
    localStorage.removeItem("cart");
    localStorage.removeItem("appliedVoucher");
    console.log("✅ Cart cleared - User switched");

    // Remove URL parameter để tránh clear lại khi refresh
    const newUrl = window.location.pathname;
    window.history.replaceState({}, document.title, newUrl);

    // Update cart count về 0
    const cartCountElements = document.querySelectorAll(".cart-count");
    cartCountElements.forEach((el) => {
      el.textContent = "0";
      el.style.display = "none";
    });
  }

  // Auto play carousel with pause on hover
  const carousel = document.querySelector("#heroCarousel");
  if (carousel) {
    const bsCarousel = new bootstrap.Carousel(carousel, {
      interval: 4000,
      ride: "carousel",
      pause: "hover",
    });
  }

  // Smooth scroll for internal links
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

  // Add animation on scroll
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

  // Observe product cards
  document.querySelectorAll(".product-card").forEach((card) => {
    card.style.opacity = "0";
    card.style.transform = "translateY(30px)";
    card.style.transition = "opacity 0.6s ease, transform 0.6s ease";
    observer.observe(card);
  });

  // Observe feature boxes
  document.querySelectorAll(".feature-box").forEach((box) => {
    box.style.opacity = "0";
    box.style.transform = "translateY(30px)";
    box.style.transition = "opacity 0.6s ease, transform 0.6s ease";
    observer.observe(box);
  });

  // Add to cart quick action (if needed later)
  const addToCartButtons = document.querySelectorAll(".btn-add-to-cart");
  addToCartButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      // Add animation feedback
      this.innerHTML = '<i class="fas fa-check me-1"></i>Đã thêm';
      this.classList.add("btn-success");
      setTimeout(() => {
        this.innerHTML = '<i class="fas fa-cart-plus me-1"></i>Thêm giỏ hàng';
        this.classList.remove("btn-success");
      }, 2000);
    });
  });

  // Back to top button
  const backToTopBtn = document.createElement("button");
  backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
  backToTopBtn.className = "back-to-top";
  backToTopBtn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: #000;
        color: #fff;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    `;
  document.body.appendChild(backToTopBtn);

  window.addEventListener("scroll", function () {
    if (window.pageYOffset > 300) {
      backToTopBtn.style.opacity = "1";
      backToTopBtn.style.visibility = "visible";
    } else {
      backToTopBtn.style.opacity = "0";
      backToTopBtn.style.visibility = "hidden";
    }
  });

  backToTopBtn.addEventListener("click", function () {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });

  backToTopBtn.addEventListener("mouseenter", function () {
    this.style.transform = "scale(1.1)";
  });

  backToTopBtn.addEventListener("mouseleave", function () {
    this.style.transform = "scale(1)";
  });

  // Preload carousel images for smooth transitions
  const carouselImages = carousel?.querySelectorAll("img");
  if (carouselImages) {
    carouselImages.forEach((img) => {
      const src = img.getAttribute("src");
      const preloadImg = new Image();
      preloadImg.src = src;
    });
  }

  console.log("Snowboard Shop Home loaded successfully!");
});
