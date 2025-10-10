document.addEventListener("DOMContentLoaded", () => {
  const clock = document.getElementById("dashboard-clock");
  if (!clock) return;

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
});
