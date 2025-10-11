# 📚 HƯỚNG DẪN PHÁT TRIỂN - DEVELOPER GUIDE

## 🎯 Mục đích

Tài liệu này hướng dẫn cách phát triển tiếp các tính năng cho dự án Snowboard Shop.

---

## 🏗️ Kiến trúc dự án

### MVC Pattern

```
Request → Controller → Model → Database
                ↓
            View (Response)
```

### Flow cụ thể

1. **User Request** → index.php hoặc controller
2. **Controller** xử lý logic, gọi Model
3. **Model** tương tác với database
4. **View** nhận data từ controller và render HTML
5. **Response** trả về browser

---

## 📂 Chi tiết cấu trúc

### /controller/controller_User/controller.php

- Xử lý POST requests (register, login, logout)
- Validation dữ liệu
- Gọi functions từ model
- Redirect sau khi xử lý

**Example:**

```php
if (isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $result = validateLogin($email, $password);

    if (is_array($result)) {
        $_SESSION['user_id'] = $result['user_id'];
        // ... set session
        header("Location: ../../view/User/home.php");
    }
}
```

### /model/

- Chứa business logic
- Tương tác với database
- Không chứa HTML

**Naming convention:**

- `user_model.php` - User functions
- `product_model.php` - Product functions
- `order_model.php` - Order functions

**Example function:**

```php
function getProductById($product_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
```

### /view/

- Chứa HTML, CSS, JS
- Nhận data từ controller/model
- Không chứa business logic

**Structure:**

```
view/
├── User/           # Customer-facing pages
│   ├── index.php   # Landing page (public)
│   ├── home.php    # User home (protected)
│   ├── cart.php
│   └── ...
└── Admin/          # Admin panel
    ├── admin_home.php
    ├── admin_product.php
    └── ...
```

---

## 🔐 Authentication Flow

### 1. Register

```
Form (register.php)
→ POST to controller.php
→ registerUser() in user_model.php
→ Insert to database
→ Redirect to login.php?msg=success
```

### 2. Login

```
Form (login.php)
→ POST to controller.php
→ validateLogin() in user_model.php
→ Set $_SESSION variables
→ session_write_close()
→ Redirect based on role
```

### 3. Protected Pages

```
Page load
→ Check auth_middleware.php
→ requireUser() or requireAdmin()
→ Check $_SESSION['user_id']
→ If not set: redirect to login
→ If set: render page
```

### Session Variables

```php
$_SESSION['user_id']        // User ID
$_SESSION['fullname']       // User full name
$_SESSION['role']           // 'user' or 'admin'
$_SESSION['last_activity']  // Timestamp for timeout
```

---

## 🛒 Cách thêm tính năng mới

### Example: Tạo Product List Page

#### Step 1: Tạo Model (/model/product_model.php)

```php
<?php
require_once 'database.php';

function getAllProducts($limit = null, $offset = 0) {
    global $conn;

    $sql = "SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC";

    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
    }

    $stmt = $conn->prepare($sql);

    if ($limit) {
        $stmt->bind_param("ii", $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    return $products;
}

function getProductsByCategory($category_id, $limit = null) {
    global $conn;

    $sql = "SELECT * FROM products
            WHERE category_id = ? AND status = 'active'
            ORDER BY created_at DESC";

    if ($limit) {
        $sql .= " LIMIT ?";
    }

    $stmt = $conn->prepare($sql);

    if ($limit) {
        $stmt->bind_param("ii", $category_id, $limit);
    } else {
        $stmt->bind_param("i", $category_id);
    }

    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function searchProducts($keyword) {
    global $conn;

    $search = "%$keyword%";
    $stmt = $conn->prepare("
        SELECT * FROM products
        WHERE (name LIKE ? OR description LIKE ?)
        AND status = 'active'
    ");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
```

#### Step 2: Tạo View (/view/User/product_list.php)

```php
<?php
session_start();
require_once '../../model/auth_middleware.php';
require_once '../../model/product_model.php';
require_once '../../model/category_model.php';

// Optional: Check if user is logged in
// requireUser();

// Get filter parameters
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12; // Products per page
$offset = ($page - 1) * $limit;

// Get products
if ($search) {
    $products = searchProducts($search);
    $total_products = count($products);
    $products = array_slice($products, $offset, $limit);
} elseif ($category_id) {
    $products = getProductsByCategory($category_id, $limit);
    $total_products = count(getProductsByCategory($category_id));
} else {
    $products = getAllProducts($limit, $offset);
    $total_products = getTotalProducts();
}

// Get all categories for filter
$categories = getAllCategories();

// Calculate pagination
$total_pages = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Snowboard Shop</title>
    <link rel="stylesheet" href="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../Css/User/product_list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home.php">Trang chủ</a></li>
                <li class="breadcrumb-item active">Sản phẩm</li>
            </ol>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Sidebar Filter -->
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <h5 class="mb-3">Danh mục</h5>
                    <ul class="list-unstyled">
                        <li><a href="product_list.php" class="<?= !$category_id ? 'active' : '' ?>">Tất cả</a></li>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="?category=<?= $cat['category_id'] ?>"
                                   class="<?= $category_id == $cat['category_id'] ? 'active' : '' ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Price Filter -->
                    <h5 class="mb-3 mt-4">Giá</h5>
                    <form method="get">
                        <div class="mb-2">
                            <input type="number" name="min_price" class="form-control" placeholder="Từ">
                        </div>
                        <div class="mb-2">
                            <input type="number" name="max_price" class="form-control" placeholder="Đến">
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Lọc</button>
                    </form>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Sản phẩm (<?= $total_products ?>)</h4>
                    <select class="form-select w-auto" onchange="sortProducts(this.value)">
                        <option value="">Sắp xếp</option>
                        <option value="price_asc">Giá tăng dần</option>
                        <option value="price_desc">Giá giảm dần</option>
                        <option value="name_asc">Tên A-Z</option>
                    </select>
                </div>

                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4">
                            <div class="product-card">
                                <a href="product_detail.php?id=<?= $product['product_id'] ?>">
                                    <img src="../../Images/product/<?= htmlspecialchars($product['image']) ?>"
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         class="product-image">
                                </a>
                                <div class="product-body">
                                    <h6 class="product-title">
                                        <a href="product_detail.php?id=<?= $product['product_id'] ?>">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </a>
                                    </h6>
                                    <p class="product-category"><?= htmlspecialchars($product['category_name'] ?? '') ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="product-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</span>
                                        <button class="btn btn-sm btn-dark" onclick="addToCart(<?= $product['product_id'] ?>)">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($products)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Không tìm thấy sản phẩm nào</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= $category_id ? '&category='.$category_id : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="../../config/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Js/User/product_list.js"></script>
</body>
</html>
```

#### Step 3: Tạo CSS (/Css/User/product_list.css)

```css
.filter-sidebar {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
}

.filter-sidebar a {
  color: #333;
  text-decoration: none;
  display: block;
  padding: 8px 0;
  transition: color 0.3s;
}

.filter-sidebar a:hover,
.filter-sidebar a.active {
  color: #000;
  font-weight: 600;
}

.product-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.product-image {
  width: 100%;
  height: 250px;
  object-fit: cover;
}

.product-body {
  padding: 15px;
}

.product-title {
  margin-bottom: 5px;
}

.product-title a {
  color: #333;
  text-decoration: none;
}

.product-title a:hover {
  color: #000;
}

.product-category {
  color: #666;
  font-size: 0.875rem;
  margin-bottom: 10px;
}

.product-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: #000;
}
```

#### Step 4: Tạo JS (/Js/User/product_list.js)

```javascript
function addToCart(productId) {
  // TODO: Implement add to cart
  alert("Thêm vào giỏ hàng: " + productId);

  // AJAX call to add to cart
  fetch("../../controller/cart_controller.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "action=add&product_id=" + productId + "&quantity=1",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update cart count in header
        updateCartCount();
        // Show success message
        showToast("Đã thêm vào giỏ hàng!");
      }
    });
}

function sortProducts(sortBy) {
  const url = new URL(window.location.href);
  url.searchParams.set("sort", sortBy);
  window.location.href = url.toString();
}

function showToast(message) {
  // Simple toast notification
  const toast = document.createElement("div");
  toast.className = "toast-notification";
  toast.textContent = message;
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.classList.add("show");
  }, 100);

  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}
```

---

## 🔍 Debug Tips

### 1. Check PHP Errors

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### 2. Debug Variables

```php
echo "<pre>";
print_r($variable);
echo "</pre>";
die(); // Stop execution
```

### 3. Check Database Queries

```php
echo $conn->error; // After query execution
```

### 4. Check Session

```php
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
```

### 5. JavaScript Console

```javascript
console.log("Debug:", variable);
console.table(arrayData);
```

---

## ✅ Best Practices

### Security

1. **Always use prepared statements**

```php
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
```

2. **Sanitize user input**

```php
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$name = htmlspecialchars(trim($_POST['name']));
```

3. **Validate data**

```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Invalid email
}
```

### Code Organization

1. **One function = one responsibility**
2. **Use meaningful variable names**
3. **Comment complex logic**
4. **Keep functions short (< 50 lines)**

### Database

1. **Use indexes on frequently queried columns**
2. **Limit query results when possible**
3. **Close connections properly**
4. **Use transactions for multiple related queries**

---

## 📞 Cần trợ giúp?

- Xem [TODO.md](TODO.md) cho roadmap
- Xem [PROGRESS_REPORT.md](PROGRESS_REPORT.md) cho tiến độ
- Check GitHub Issues

---

**Happy Coding! 🚀**
