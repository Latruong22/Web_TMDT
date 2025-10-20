# 🏂 SNOWBOARD SHOP - Website Thương Mại Điện Tử

Dự án website bán đồ trượt tuyết (Báo cáo cuối kỳ)

## 📋 Mô tả dự án

Website thương mại điện tử chuyên bán các sản phẩm snowboard, thiết bị trượt tuyết và phụ kiện liên quan. Dự án được xây dựng bằng PHP thuần với kiến trúc MVC, tích hợp đầy đủ các chức năng mua sắm trực tuyến và quản trị hệ thống.

## ✨ Tính năng chính

### 👤 Chức năng User

#### 1. Xác thực & Quản lý tài khoản

- ✅ **Đăng ký tài khoản** - Với xác thực email
- ✅ **Đăng nhập** - Session-based authentication
- ✅ **Quên mật khẩu** - Reset password qua email
- ✅ **Hồ sơ cá nhân** - Cập nhật thông tin, đổi mật khẩu, upload avatar

#### 2. Mua sắm

- ✅ **Trang chủ** - Hiển thị sản phẩm nổi bật, banner quảng cáo
- ✅ **Danh sách sản phẩm** - Lọc theo danh mục, tìm kiếm, phân trang
- ✅ **Chi tiết sản phẩm** - Thông tin đầy đủ, hình ảnh, đánh giá
- ✅ **Giỏ hàng** - Thêm, sửa, xóa sản phẩm
- ✅ **Thanh toán** - Đặt hàng với thông tin giao hàng, áp dụng voucher

#### 3. Quản lý đơn hàng

- ✅ **Lịch sử đơn hàng** - Xem tất cả đơn đã đặt, lọc theo trạng thái
- ✅ **Theo dõi đơn hàng** - Timeline chi tiết từ đặt hàng đến giao hàng
- ✅ **Hủy đơn hàng** - Hủy đơn với lý do (chỉ đơn chưa xác nhận)

#### 4. Đánh giá sản phẩm

- ✅ **Viết đánh giá** - Rating 1-5 sao, nhận xét (sau khi nhận hàng)
- ✅ **Xem đánh giá** - Đánh giá của người dùng khác
- ✅ **Ngăn spam** - Chỉ đánh giá 1 lần/sản phẩm, phải mua mới đánh giá

### 👨‍💼 Chức năng Admin

#### 1. Dashboard

- ✅ **Tổng quan thống kê** - Doanh thu, đơn hàng, người dùng, đánh giá
- ✅ **Biểu đồ** - Doanh thu theo thời gian, top sản phẩm

#### 2. Quản lý sản phẩm

- ✅ **CRUD sản phẩm** - Thêm, sửa, xóa sản phẩm
- ✅ **Quản lý hình ảnh** - Upload ảnh sản phẩm
- ✅ **Quản lý tồn kho** - Cập nhật số lượng
- ✅ **Quản lý danh mục** - Phân loại sản phẩm

#### 3. Quản lý đơn hàng

- ✅ **Xem tất cả đơn** - Lọc theo trạng thái, tìm kiếm
- ✅ **Chi tiết đơn** - Thông tin khách hàng, sản phẩm
- ✅ **Cập nhật trạng thái** - Xác nhận, đang giao, hoàn thành, hủy
- ✅ **In đơn hàng** - Export thông tin đơn

#### 4. Quản lý người dùng

- ✅ **Danh sách user** - Xem tất cả tài khoản
- ✅ **Quản lý trạng thái** - Kích hoạt/Vô hiệu hóa tài khoản
- ✅ **Phân quyền** - Thay đổi role (admin/customer)
- ✅ **Reset mật khẩu** - Đặt lại mật khẩu cho user

#### 5. Quản lý khuyến mãi

- ✅ **CRUD Voucher** - Tạo, sửa, xóa mã giảm giá
- ✅ **Loại voucher** - Giảm theo phần trăm hoặc số tiền cố định
- ✅ **Giới hạn sử dụng** - Số lần sử dụng tối đa
- ✅ **Hết hạn tự động** - Voucher hết hạn theo ngày

#### 6. Quản lý đánh giá

- ✅ **Duyệt đánh giá** - Approve/Reject review từ user
- ✅ **Xóa đánh giá** - Xóa review không phù hợp
- ✅ **Lọc & Tìm kiếm** - Theo trạng thái, rating, sản phẩm

#### 7. Báo cáo doanh thu

- ✅ **Tổng quan doanh thu** - Theo ngày, tuần, tháng, năm
- ✅ **Biểu đồ xu hướng** - Doanh thu theo thời gian
- ✅ **Phân tích theo trạng thái** - Pending, confirmed, delivered, cancelled
- ✅ **Top sản phẩm** - Sản phẩm bán chạy nhất
- ✅ **Top khách hàng** - Khách hàng mua nhiều nhất

#### 8. Quản lý Email Marketing

- ✅ **Gửi email hàng loạt** - Gửi thông báo/khuyến mãi đến tất cả users
- ✅ **Gửi email cá nhân** - Gửi email đến user cụ thể
- ✅ **Email templates** - Template General và Promotion có sẵn
- ✅ **Preview email** - Xem trước email trước khi gửi
- ✅ **Personalization** - Tự động thay thế {fullname}, {email}, {date}
- ✅ **WYSIWYG Editor** - Định dạng nội dung email (Bold, Italic, Insert variables)

**Chú thích:** ✅ Hoàn thành

## 🛠️ Công nghệ sử dụng

### Backend

- **PHP** 8.0.30
- **MySQL** (MariaDB 10.4.32)
- **Architecture:** MVC Pattern
- **Authentication:** Session-based với timeout 30 phút
- **Email:** PHPMailer cho gửi email xác thực

### Frontend

- **Bootstrap** 5.3.8 - UI Framework
- **Font Awesome** 6.5.1 - Icons
- **JavaScript** ES6+ (Vanilla) - AJAX, DOM manipulation
- **CSS3** - Custom styles với animations

### Security

- ✅ Password hashing với `password_hash()` (bcrypt)
- ✅ Prepared Statements - Chống SQL Injection
- ✅ XSS Prevention - `htmlspecialchars()`, sanitization
- ✅ CSRF Protection - Token validation
- ✅ Session Security - Timeout, regeneration
- ✅ Input Validation - Client & Server side

### Tools & Environment

- **XAMPP** - Local development (Apache + MySQL)
- **Git** + **GitHub** - Version control
- **VS Code** - Code editor

## 📁 Cấu trúc thư mục

```
Web_TMDT/
├── 📁 config/                  # Cấu hình hệ thống
│   └── bootstrap-5.3.8-dist/   # Bootstrap framework
│
├── 📁 controller/              # Controllers (Logic xử lý)
│   ├── controller_Admin/       # Admin controllers
│   │   ├── admin_order_controller.php
│   │   ├── admin_product_controller.php
│   │   ├── admin_promotion_controller.php
│   │   ├── admin_revenue_controller.php
│   │   ├── admin_review_controller.php
│   │   ├── admin_user_controller.php
│   │   └── admin_email_controller.php
│   ├── controller_User/        # User controllers
│   │   ├── cart_controller.php
│   │   ├── checkout_controller.php
│   │   ├── cancel_order_controller.php
│   │   ├── order_controller.php
│   │   ├── product_controller.php
│   │   ├── profile_controller.php
│   │   ├── review_controller.php
│   │   ├── voucher_controller.php
│   │   └── email_controller.php
│   └── PHPMailer-master/       # Email library
│
├── 📁 model/                   # Models (Database layer)
│   ├── database.php            # Database connection
│   ├── auth_middleware.php     # Authentication guard
│   ├── user_model.php          # User operations
│   ├── product_model.php       # Product operations
│   ├── category_model.php      # Category operations
│   ├── cart_model.php          # Cart operations
│   ├── order_model.php         # Order operations
│   ├── promotion_model.php     # Voucher operations
│   ├── review_model.php        # Review operations
│   ├── revenue_model.php       # Revenue analytics
│   └── email_model.php         # Email templates
│
├── 📁 view/                    # Views (Presentation layer)
│   ├── User/                   # User interface
│   │   ├── home.php            # Trang chủ
│   │   ├── login.php           # Đăng nhập
│   │   ├── register.php        # Đăng ký
│   │   ├── forgot_password.php # Quên mật khẩu
│   │   ├── reset_password.php  # Đặt lại mật khẩu
│   │   ├── profile.php         # Hồ sơ cá nhân
│   │   ├── product_list.php    # Danh sách sản phẩm
│   │   ├── product_detail.php  # Chi tiết sản phẩm
│   │   ├── cart.php            # Giỏ hàng
│   │   ├── checkout.php        # Thanh toán
│   │   ├── order_history.php   # Lịch sử đơn hàng
│   │   └── order_tracking.php  # Theo dõi đơn hàng
│   └── Admin/                  # Admin interface
│       ├── admin_home.php      # Dashboard
│       ├── admin_product.php   # Quản lý sản phẩm
│       ├── admin_order.php     # Quản lý đơn hàng
│       ├── admin_user.php      # Quản lý người dùng
│       ├── admin_promotion.php # Quản lý voucher
│       ├── admin_review.php    # Quản lý đánh giá
│       ├── admin_revenue.php   # Báo cáo doanh thu
│       └── admin_email.php     # Gửi email marketing
│
├── 📁 Css/                     # Custom CSS
│   ├── User/                   # User styles
│   └── Admin/                  # Admin styles
│
├── 📁 Js/                      # Custom JavaScript
│   ├── User/                   # User scripts
│   └── Admin/                  # Admin scripts
│
├── 📁 Images/                  # Hình ảnh
│   ├── avatars/                # Avatar người dùng
│   ├── baner/                  # Banner trang chủ
│   ├── logo/                   # Logo
│   └── product/                # Hình ảnh sản phẩm
│
├── 📄 index.php                # Entry point
├── 📄 snowboard_web.sql        # Database schema
├── 📄 sample_products.sql      # Sample data
└── 📄 README.md                # Tài liệu dự án
```

## 🚀 Hướng dẫn cài đặt

### Yêu cầu hệ thống

- **XAMPP** (PHP 8.0+, MySQL/MariaDB)
- **Browser:** Chrome, Firefox, Edge (phiên bản mới nhất)
- **Git** (tùy chọn)

### Các bước cài đặt

#### 1️⃣ Clone repository

```bash
git clone https://github.com/Latruong22/Web_TMDT.git
# Hoặc download ZIP và giải nén vào thư mục htdocs
```

#### 2️⃣ Cài đặt XAMPP

- Download từ: https://www.apachefriends.org
- Cài đặt và khởi động **Apache** + **MySQL**

#### 3️⃣ Tạo database

1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Tạo database mới tên: `snowboard_web`
3. Import file: `snowboard_web.sql`
4. (Tùy chọn) Import: `sample_products.sql` để có dữ liệu mẫu

#### 4️⃣ Cấu hình database

Mở file `model/database.php` và kiểm tra:

```php
$servername = "localhost";
$username = "root";
$password = "";        // Mật khẩu MySQL (mặc định trống)
$dbname = "snowboard_web";
```

#### 5️⃣ Chạy ứng dụng

- **Trang chủ:** `http://localhost/Web_TMDT/view/User/home.php`
- **Đăng nhập:** `http://localhost/Web_TMDT/view/User/login.php`
- **Admin:** `http://localhost/Web_TMDT/view/Admin/admin_home.php`

## 👤 Tài khoản test

### 👨‍💼 Admin

```
Email:    admin@snowboard.com
Password: admin123
```

**Quyền:** Truy cập toàn bộ Admin Panel

### 👥 User

```
Email:    user@test.com
Password: user123
```

**Quyền:** Mua sắm, đặt hàng, đánh giá sản phẩm

> **Lưu ý:** Đổi mật khẩu sau khi đăng nhập lần đầu để bảo mật

## 🎯 Các trang chính

### � User Pages

| Trang            | URL                              | Mô tả                     |
| ---------------- | -------------------------------- | ------------------------- |
| 🏠 Trang chủ     | `/view/User/home.php`            | Sản phẩm nổi bật, banner  |
| 🔐 Đăng nhập     | `/view/User/login.php`           | Đăng nhập tài khoản       |
| 📝 Đăng ký       | `/view/User/register.php`        | Tạo tài khoản mới         |
| 🔑 Quên mật khẩu | `/view/User/forgot_password.php` | Khôi phục mật khẩu        |
| 👤 Hồ sơ         | `/view/User/profile.php`         | Quản lý thông tin cá nhân |
| 📦 Sản phẩm      | `/view/User/product_list.php`    | Danh sách sản phẩm        |
| 🔍 Chi tiết SP   | `/view/User/product_detail.php`  | Thông tin chi tiết        |
| 🛒 Giỏ hàng      | `/view/User/cart.php`            | Quản lý giỏ hàng          |
| 💳 Thanh toán    | `/view/User/checkout.php`        | Đặt hàng                  |
| 📋 Đơn hàng      | `/view/User/order_history.php`   | Lịch sử mua hàng          |
| 🚚 Theo dõi      | `/view/User/order_tracking.php`  | Trạng thái đơn hàng       |

### �‍💼 Admin Pages

| Trang         | URL                               | Mô tả               |
| ------------- | --------------------------------- | ------------------- |
| 📊 Dashboard  | `/view/Admin/admin_home.php`      | Tổng quan hệ thống  |
| 📦 Sản phẩm   | `/view/Admin/admin_product.php`   | CRUD sản phẩm       |
| 📋 Đơn hàng   | `/view/Admin/admin_order.php`     | Quản lý đơn         |
| 👥 Người dùng | `/view/Admin/admin_user.php`      | Quản lý user        |
| 🎟️ Voucher    | `/view/Admin/admin_promotion.php` | Mã giảm giá         |
| ⭐ Đánh giá   | `/view/Admin/admin_review.php`    | Duyệt review        |
| 💰 Doanh thu  | `/view/Admin/admin_revenue.php`   | Báo cáo & phân tích |
| 📧 Gửi Email  | `/view/Admin/admin_email.php`     | Email marketing     |

## 📊 Database Schema

### Bảng chính

- **users** - Thông tin người dùng (15 cột)
- **products** - Sản phẩm (10 cột)
- **categories** - Danh mục (4 cột)
- **orders** - Đơn hàng (10 cột + cancel_reason)
- **order_details** - Chi tiết đơn (6 cột)
- **vouchers** - Mã giảm giá (9 cột)
- **reviews** - Đánh giá sản phẩm (8 cột)
- **carts** - Giỏ hàng (5 cột)

### Bảng phụ

- **verification_codes** - Mã xác thực email
- **password_resets** - Token reset password
- **remember_tokens** - Remember me
- **login_history** - Lịch sử đăng nhập

> Xem chi tiết trong file `snowboard_web.sql`

## 🎨 Screenshots

### 🏠 Trang chủ User

![User Home](docs/screenshots/home.png)
_Giao diện trang chủ với sản phẩm nổi bật, banner quảng cáo_

### 📦 Danh sách sản phẩm

![Product List](docs/screenshots/products.png)
_Lọc, tìm kiếm và xem sản phẩm theo danh mục_

### 🛒 Giỏ hàng & Thanh toán

![Cart & Checkout](docs/screenshots/cart.png)
_Quản lý giỏ hàng và đặt hàng_

### 👤 Hồ sơ cá nhân

![Profile](docs/screenshots/profile.png)
_Cập nhật thông tin, đổi mật khẩu, upload avatar_

### 📊 Admin Dashboard

![Admin Dashboard](docs/screenshots/admin-dashboard.png)
_Tổng quan thống kê doanh thu, đơn hàng, người dùng_

### 🔧 Quản lý sản phẩm

![Product Management](docs/screenshots/admin-products.png)
_CRUD sản phẩm, quản lý tồn kho_

### 📋 Quản lý đơn hàng

![Order Management](docs/screenshots/admin-orders.png)
_Xử lý đơn hàng, cập nhật trạng thái_

### 💰 Báo cáo doanh thu

![Revenue Report](docs/screenshots/admin-revenue.png)
_Biểu đồ và phân tích doanh thu_

> **Lưu ý:** Screenshots sẽ được cập nhật đầy đủ trong phiên bản release

## 🔒 Bảo mật

Dự án được xây dựng với các biện pháp bảo mật cơ bản:

### 🛡️ Authentication & Authorization

- ✅ **Password Hashing:** Sử dụng `password_hash()` với thuật toán bcrypt
- ✅ **Session Management:** Timeout 30 phút, regenerate session ID sau login
- ✅ **Role-based Access:** Phân quyền admin/customer
- ✅ **Auth Middleware:** Kiểm tra quyền truy cập mọi trang admin

### 🔐 Input Security

- ✅ **SQL Injection Prevention:** Prepared Statements với bind parameters
- ✅ **XSS Prevention:** `htmlspecialchars()` cho mọi output
- ✅ **Input Validation:** Client-side (JavaScript) + Server-side (PHP)
- ✅ **Data Sanitization:** `trim()`, `filter_var()`, type casting

### 📧 Email Security

- ✅ **Token-based Reset:** Random token cho reset password
- ✅ **Token Expiry:** Hết hạn sau 1 giờ
- ✅ **One-time Use:** Token chỉ dùng được 1 lần

### 🚨 Security Best Practices

- ✅ File upload validation (avatar): Type, size, rename
- ✅ Error handling: Không hiển thị lỗi hệ thống ra client
- ✅ HTTPS ready: Code tương thích với HTTPS
- ✅ Database credentials: Không commit vào Git

> **Khuyến nghị production:** Bật HTTPS, cấu hình firewall, regular backup

## 📈 Tiến độ dự án

### ✅ Hoàn thành (100%)

#### Backend Core

- ✅ MVC Architecture
- ✅ Database Design & Schema
- ✅ Authentication System (Login, Register, Forgot Password)
- ✅ Session Management
- ✅ Email Service (PHPMailer)

#### User Features

- ✅ Trang chủ
- ✅ Danh sách sản phẩm (Filter, Search, Pagination)
- ✅ Chi tiết sản phẩm
- ✅ Giỏ hàng (Add, Update, Remove)
- ✅ Thanh toán (Checkout với voucher)
- ✅ Quản lý đơn hàng (History, Tracking, Cancel)
- ✅ Hồ sơ cá nhân (Update info, Change password, Upload avatar)
- ✅ Đánh giá sản phẩm (Review system)

#### Admin Features

- ✅ Dashboard với thống kê
- ✅ Quản lý sản phẩm (CRUD)
- ✅ Quản lý đơn hàng
- ✅ Quản lý người dùng
- ✅ Quản lý voucher
- ✅ Quản lý đánh giá
- ✅ Báo cáo doanh thu
- ✅ Gửi email marketing

### 🎯 Tính năng nổi bật

1. **Review System** - Đánh giá sản phẩm với rating 1-5 sao, chống spam
2. **Profile Management** - Upload avatar, cập nhật thông tin, đổi mật khẩu
3. **Order Cancel** - Hủy đơn hàng với lý do chi tiết
4. **Revenue Analytics** - Báo cáo doanh thu đa chiều với biểu đồ
5. **Voucher System** - Mã giảm giá linh hoạt (%, fixed amount)
6. **Email Marketing** - Gửi email tự động, template có sẵn, personalization
7. **Cart Sync** - Đồng bộ giỏ hàng theo user, auto-clear khi đổi tài khoản
8. **Order Email** - Tự động gửi email xác nhận khi đặt hàng thành công

### 📊 Thống kê code

- **Total Files:** 60+ files
- **PHP Files:** 35+
- **JavaScript Files:** 15+
- **CSS Files:** 15+
- **Database Tables:** 12 tables
- **Lines of Code:** ~15,000+ lines

## 🚧 Tính năng mở rộng (Optional)

Các tính năng có thể phát triển thêm:

- 📱 Responsive design (Mobile-first)
- 💬 Live chat support
- 🔔 Real-time notifications
- 📊 Advanced analytics (Google Analytics)
- 💳 Payment gateway integration (VNPay, Momo)
- 📦 Inventory management (Nhập/xuất kho)
- 🎁 Loyalty program (Điểm thưởng)
- 🌐 Multi-language support
- 📧 Email marketing automation
- 🔍 Advanced search với Elasticsearch

## � Changelog & Bug Fixes

### ✅ Version 1.1 (20/10/2025)

**Tính năng mới:**

- ✨ **Admin Email Management** - Hệ thống gửi email marketing hoàn chỉnh
  - Gửi email hàng loạt hoặc cá nhân
  - WYSIWYG editor với formatting
  - Email templates (General, Promotion)
  - Preview email trước khi gửi
  - Personalization variables

**Bug Fixes:**

- 🐛 **Fix Voucher Percentage Bug** - Sửa lỗi voucher giảm 20% bị hiểu thành 20,000đ
  - Thay đổi: `'percentage'` → `'percent'` trong checkout_controller.php
  - Impact: Tính toán giảm giá chính xác cho cả percent và fixed vouchers
- 🐛 **Fix Cart User Sync** - Sửa lỗi giỏ hàng không đồng bộ khi đổi user
  - Thêm user_id tracking trong localStorage
  - Auto-clear cart khi detect user khác đăng nhập
  - Thêm meta tag user-id vào 5 pages chính
- 🐛 **Fix Admin Email UI** - Sửa lỗi CSS và navigation
  - Chuẩn hóa HTML structure của admin_email.php
  - Thêm link "Gửi Email" vào tất cả 8 trang admin
  - Sidebar responsive với toggle button

**Improvements:**

- 🔒 Remove debug information trong production code
- ♻️ Code cleanup: Xóa test files không cần thiết
- 📝 Documentation: Thêm 4 file MD chi tiết các fix

### ✅ Version 1.0 (17/10/2025)

**Initial Release:**

- ✅ Hoàn thành toàn bộ chức năng User & Admin
- ✅ Review System
- ✅ Profile Management
- ✅ Order Tracking & Cancel
- ✅ Revenue Analytics
- ✅ Voucher System
- ✅ Email confirmation khi đặt hàng

---

## �🐛 Troubleshooting

### Lỗi thường gặp

**1. Không kết nối được database**

```
Error: Connection failed: Access denied for user 'root'@'localhost'
```

✅ **Giải pháp:** Kiểm tra MySQL đã chạy chưa, username/password trong `database.php`

**2. Email không gửi được**

```
SMTP Error: Could not connect to SMTP host
```

✅ **Giải pháp:** Cấu hình SMTP trong `email_model.php`, bật "Less secure app" nếu dùng Gmail

**3. Lỗi upload avatar**

```
Warning: move_uploaded_file(): failed to open stream
```

✅ **Giải pháp:** Kiểm tra quyền ghi thư mục `Images/avatars/`

**4. Session timeout nhanh**

```
Bị logout liên tục
```

✅ **Giải pháp:** Tăng session timeout trong `auth_middleware.php`

**5. Lỗi 404 Not Found**

```
Object not found!
```

✅ **Giải pháp:** Kiểm tra đường dẫn file, đảm bảo Apache đang chạy

## 🤝 Đóng góp

Dự án này là **báo cáo cuối kỳ** môn Phát triển ứng dụng Web.

- 🐛 **Bug reports:** Tạo issue trên GitHub
- 💡 **Feature requests:** Gửi qua Issues
- 🔧 **Pull requests:** Welcome!

## � License

Dự án này thuộc về sinh viên thực hiện báo cáo cuối kỳ.  
Chỉ sử dụng cho mục đích học tập và nghiên cứu.

## 👨‍💻 Tác giả

- **GitHub:** [@Latruong22](https://github.com/Latruong22)
- **Repository:** [Web_TMDT](https://github.com/Latruong22/Web_TMDT)
- **Email:** [Liên hệ qua GitHub]

## 📞 Hỗ trợ

Nếu gặp vấn đề khi cài đặt hoặc sử dụng:

1. 📖 Đọc lại hướng dẫn trong README
2. 🔍 Tìm trong phần [Troubleshooting](#-troubleshooting)
3. 🐛 Tạo [Issue](https://github.com/Latruong22/Web_TMDT/issues) mới trên GitHub
4. 📧 Liên hệ qua email (nếu cần)

## 🙏 Acknowledgments

Cảm ơn:

- **Bootstrap Team** - UI Framework
- **Font Awesome** - Icons
- **PHPMailer** - Email service
- **XAMPP Team** - Development environment
- **Giảng viên** - Hướng dẫn và góp ý

---

<div align="center">

### ⭐ Nếu thấy dự án hữu ích, hãy cho 1 star nhé! ⭐

**Ngày bắt đầu:** 01/10/2025  
**Cập nhật lần cuối:** 20/10/2025  
**Trạng thái:** ✅ Hoàn thành 100%

Made with ❤️ by [Latruong22](https://github.com/Latruong22)

</div>
