document.addEventListener("DOMContentLoaded", () => {
  // Clock functionality
  const clock = document.getElementById("dashboard-clock");
  if (clock) {
    const formatter = new Intl.DateTimeFormat("vi-VN", {
      weekday: "long",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
    });

    const tick = () => {
      clock.textContent = formatter.format(new Date());
    };

    tick();
    setInterval(tick, 1000);
  }

  // Sidebar toggle for mobile
  const menuToggle = document.getElementById("menuToggle");
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("adminSidebar");

  if (menuToggle) {
    menuToggle.addEventListener("click", () => {
      sidebar.classList.add("active");
    });
  }

  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", () => {
      sidebar.classList.remove("active");
    });
  }

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", (e) => {
    if (
      window.innerWidth <= 991 &&
      sidebar.classList.contains("active") &&
      !sidebar.contains(e.target) &&
      !menuToggle.contains(e.target)
    ) {
      sidebar.classList.remove("active");
    }
  });

  // Smooth scroll animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  }, observerOptions);

  document.querySelectorAll(".stats-card, .card").forEach((card) => {
    observer.observe(card);
  });
});
