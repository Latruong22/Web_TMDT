document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector(
    'form[action*="admin_product_controller.php"]'
  );
  if (!form) return;

  form.addEventListener("submit", function (e) {
    const name = form.querySelector('input[name="name"]').value.trim();
    const price = parseFloat(form.querySelector('input[name="price"]').value);
    const discountInput = form.querySelector('input[name="manual_discount"]');
    const discount = discountInput ? parseFloat(discountInput.value || "0") : 0;
    const stock = parseInt(form.querySelector('input[name="stock"]').value, 10);
    const category = form.querySelector('select[name="category_id"]').value;
    const action = form.querySelector('input[name="action"]').value;
    const imageInput = form.querySelector('input[name="image"]');

    let errors = [];
    if (name.length === 0) errors.push("Vui lòng nhập tên sản phẩm");
    if (isNaN(price) || price < 0) errors.push("Giá không hợp lệ");
    if (isNaN(discount) || discount < 0 || discount > 100)
      errors.push("Khuyến mãi phải từ 0 đến 100");
    if (isNaN(stock) || stock < 0) errors.push("Tồn kho không hợp lệ");
    if (!category) errors.push("Vui lòng chọn danh mục");
    if (action === "add" && imageInput && imageInput.files.length === 0)
      errors.push("Vui lòng chọn ảnh sản phẩm");

    if (errors.length > 0) {
      e.preventDefault();
      alert(errors.join("\n"));
    }
  });
});
