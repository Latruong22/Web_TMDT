# 📋 TODO LIST - SNOWBOARD SHOP

## ✅ ĐÃ HOÀN THÀNH (75%)

### Authentication & UI ✅

- ✅ Register, Login, Logout
- ✅ Landing Page (index.php)
- ✅ User Home (home.php)
- ✅ Session Management

### Admin Panel ✅

- ✅ **admin_home.php** - Dashboard
- ✅ **admin_product.php** - CRUD sản phẩm hoàn chỉnh
- ✅ **admin_order.php** - Quản lý đơn hàng
- ✅ **admin_user.php** - Quản lý người dùng
- ✅ **admin_promotion.php** - Quản lý voucher
- ✅ **admin_review.php** - Duyệt đánh giá
- ✅ **admin_revenue.php** - Báo cáo doanh thu

---

## 🔴 URGENT - Cần làm ngay (25% còn lại)

### Tuần 1-2: USER Shopping Features

#### 1. Product Display

- [x] **product_list.php** - Danh sách sản phẩm ✅ **100% HOÀN THÀNH**

  - [x] Display products in grid (3-4 columns) ✅
  - [x] Filter by category (sidebar) ✅
  - [x] Sort by price/name/newest ✅
  - [x] Pagination (12 products/page) ✅
  - [x] Show: image, name, price, discount, stock ✅
  - [x] "Add to cart" button ✅
  - [x] Responsive design (6 breakpoints) ✅
  - [x] SEO optimization (meta + schema.org) ✅
  - [x] Accessibility (ARIA + keyboard) ✅
  - [x] Performance optimization ✅
  - [x] Rich animations ✅

- [x] **product_detail.php** - Chi tiết sản phẩm ✅ **100% HOÀN THÀNH**
  - [x] Product images (main + gallery with zoom) ✅
  - [x] Full product info (name, price, description) ✅
  - [x] Size selector (for shoes only) ✅
  - [x] Quantity selector (+/- buttons) ✅
  - [x] "Add to cart" button (with validation) ✅
  - [x] Related products (6 items, same category) ✅
  - [x] Fullscreen image viewer ✅
  - [x] Toast notifications ✅
  - [x] Responsive design (6 breakpoints) ✅
  - [x] Modern animations & effects ✅

#### 2. Shopping Cart

- [ ] **cart.php** - Giỏ hàng

  - [ ] List cart items (session-based)
  - [ ] Update quantity (+/-)
  - [ ] Remove item button
  - [ ] Calculate subtotal, discount, total
  - [ ] Apply voucher code
  - [ ] "Proceed to checkout" button
  - [ ] Empty cart message

- [ ] **cart_model.php** - Cart logic
  - [ ] `addToCart($product_id, $quantity)`
  - [ ] `updateCart($product_id, $quantity)`
  - [ ] `removeFromCart($product_id)`
  - [ ] `getCart()`
  - [ ] `calculateTotal()`
  - [ ] `applyVoucher($code)`
  - [ ] `clearCart()`

#### 3. Checkout

- [ ] **checkout.php** - Thanh toán

  - [ ] Shipping information form
  - [ ] Order summary (items, total)
  - [ ] Payment method selection
  - [ ] Voucher application
  - [ ] "Place order" button
  - [ ] Validation

- [ ] **Order Creation**
  - [ ] Insert into `orders` table
  - [ ] Insert into `order_details` table
  - [ ] Update product stock
  - [ ] Clear cart after success
  - [ ] Redirect to order confirmation

### Tuần 3: USER Order Management

- [ ] **order_history.php** - Lịch sử đơn hàng

  - [ ] List all user orders
  - [ ] Show: order_id, date, total, status
  - [ ] View details button
  - [ ] Cancel button (if pending)
  - [ ] Pagination

- [ ] **order_tracking.php** - Theo dõi đơn hàng

  - [ ] Track by order ID
  - [ ] Show status timeline
  - [ ] Estimated delivery
  - [ ] Contact support link

- [ ] **order_cancel.php** - Hủy đơn hàng
  - [ ] Cancel reason form
  - [ ] Confirmation dialog
  - [ ] Update order status to 'cancelled'
  - [ ] Restore product stock

---

## 🟢 NICE TO HAVE - Optional Features

### User Reviews (Optional)

- [ ] Submit review form (in product_detail.php)
  - [ ] Rating (1-5 stars)
  - [ ] Comment textarea
  - [ ] Only for users who bought product
  - [ ] One review per user per product

### Admin Enhancements (Optional)

- [ ] Print invoice (admin_order.php)
- [ ] Export reports CSV/PDF (admin_revenue.php)
- [ ] Reply to reviews (admin_review.php)
- [ ] User activity logs (admin_user.php)
- [ ] Revenue by category chart

### Email System (Optional)

- [ ] Order confirmation email
- [ ] Order status update email
- [ ] Password reset email
- [ ] Welcome email

### Search & Filters (Optional)

- [ ] Global search functionality
- [ ] Autocomplete suggestions
- [ ] Price range slider
- [ ] Advanced filters

---

## 🎯 KẾ HOẠCH THỰC HIỆN

### Bước 1: Product & Cart (3-4 ngày)

1. Tạo product_list.php (hiển thị sản phẩm từ DB)
2. Tạo product_detail.php (chi tiết + add to cart)
3. Implement cart.php (session-based cart)
4. Test cart functionality

### Bước 2: Checkout & Orders (2-3 ngày)

1. Tạo checkout.php (form + payment)
2. Implement order creation logic
3. Tạo order_history.php
4. Test toàn bộ shopping flow

### Bước 3: Admin Product Management (2-3 ngày)

1. Tạo admin_product.php (list + CRUD)
2. Implement image upload
3. Stock management
4. Test admin features

### Bước 4: Admin Order Management (1-2 ngày)

1. Tạo admin_order.php
2. Order status updates
3. Order details view
4. Test order workflow

### Bước 5: Polish & Testing (2-3 ngày)

1. Bug fixes
2. UI/UX improvements
3. Mobile testing
4. Security checks

---

## 📊 TIẾN ĐỘ

- ✅ Authentication (100%)
- ✅ UI/UX Design (100%)
- ✅ User Home (100%)
- ✅ **Admin Dashboard (100%)** ⭐
- ✅ **Admin Product (100%)** ⭐
- ✅ **Admin Order (95%)** ⭐
- ✅ **Admin User (95%)** ⭐
- ✅ **Admin Promotion (100%)** ⭐
- ✅ **Admin Review (95%)** ⭐
- ✅ **Admin Revenue (90%)** ⭐
- ✅ **User Product List (100%)** ⭐
- ✅ **User Product Detail (100%)** ⭐ **MỚI HOÀN THÀNH!**
- ⏳ Shopping Cart (0%)
- ⏳ Checkout (0%)
- ⏳ User Orders (0%)

**Tổng:** 85% hoàn thành (tăng từ 78%)

---

## 💡 GHI CHÚ

- Database schema đã có sẵn cho tất cả features
- Focus vào user shopping experience trước
- Admin features làm sau
- Test kỹ từng feature trước khi chuyển sang feature mới
- Commit code thường xuyên lên Git

---

**Cập nhật:** 11/10/2025
