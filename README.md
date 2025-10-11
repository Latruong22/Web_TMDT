# 🏂 SNOWBOARD SHOP - Website Thương Mại Điện Tử

Dự án website bán đồ trượt tuyết (Báo cáo cuối kỳ)

## 📋 Mô tả dự án

Website thương mại điện tử chuyên bán các sản phẩm snowboard, thiết bị trượt tuyết và phụ kiện liên quan. Dự án được xây dựng bằng PHP thuần với kiến trúc MVC.

## ✨ Tính năng chính

### User Features

- ✅ Đăng ký / Đăng nhập / Quên mật khẩu
- ✅ Trang chủ hiển thị sản phẩm nổi bật
- 🔄 Xem danh sách sản phẩm & chi tiết
- 🔄 Giỏ hàng & Thanh toán
- 🔄 Quản lý đơn hàng
- 🔄 Đánh giá sản phẩm

### Admin Features ⭐ MỚI

- ✅ Dashboard thống kê tổng quan
- ✅ **Quản lý sản phẩm (CRUD)** - Hoàn chỉnh
- ✅ **Quản lý đơn hàng** - Hoàn chỉnh
- ✅ **Quản lý người dùng** - Hoàn chỉnh
- ✅ **Quản lý khuyến mãi (Voucher)** - Hoàn chỉnh
- ✅ **Quản lý đánh giá** - Hoàn chỉnh
- ✅ **Báo cáo doanh thu** - Hoàn chỉnh

**Chú thích:** ✅ Hoàn thành | 🔄 Đang phát triển

## 🛠️ Công nghệ sử dụng

### Backend

- PHP 8.0.30
- MySQL (MariaDB 10.4.32)
- Session-based authentication
- MVC Architecture

### Frontend

- Bootstrap 5.3.8
- Font Awesome 6.5.1
- JavaScript (Vanilla)
- CSS3 với animations

### Tools

- XAMPP (Apache + MySQL)
- Git + GitHub
- VS Code

## 📁 Cấu trúc thư mục

```
Web_TMDT/
├── config/              # Bootstrap, cấu hình
├── controller/          # Controllers xử lý logic
├── model/               # Models tương tác database
├── view/                # Views hiển thị giao diện
│   ├── User/           # Giao diện user
│   └── Admin/          # Giao diện admin
├── Css/                 # Custom CSS
├── Js/                  # Custom JavaScript
├── Images/              # Hình ảnh, banners
├── index.php            # Entry point
├── snowboard_web.sql    # Database schema
└── README.md
```

## 🚀 Hướng dẫn cài đặt

### Yêu cầu hệ thống

- XAMPP (PHP 8.0+, MySQL)
- Browser hiện đại (Chrome, Firefox, Edge)

### Các bước cài đặt

1. **Clone repository**

```bash
git clone https://github.com/Latruong22/Web_TMDT.git
cd Web_TMDT
```

2. **Cài đặt XAMPP**

   - Download XAMPP từ https://www.apachefriends.org
   - Cài đặt và khởi động Apache + MySQL

3. **Tạo database**

   - Mở phpMyAdmin: http://localhost/phpmyadmin
   - Tạo database mới tên `snowboard_web`
   - Import file `snowboard_web.sql`

4. **Tạo test accounts**

   - Import file `insert_test_accounts.sql` để tạo tài khoản test

5. **Chạy ứng dụng**
   - Truy cập: http://localhost/Web_TMDT
   - Landing page: http://localhost/Web_TMDT/view/User/index.php
   - Login: http://localhost/Web_TMDT/view/User/login.php

## 👤 Tài khoản test

### Admin

- **Email:** admin@snowboard.com
- **Password:** admin123

### User

- **Email:** user@test.com
- **Password:** user123

## 📊 Tiến độ dự án

Xem chi tiết tại: [PROGRESS_REPORT.md](PROGRESS_REPORT.md)

**Tổng quan:**

- ✅ **Hoàn thành: 75%** ⭐ (Tăng từ 35%)
- 🔄 Đang làm: 0%
- ⏳ Chưa làm: 25%

**Cập nhật mới nhất:**

- ✅ Đã hoàn thành toàn bộ Admin Panel (100%)
- ✅ Authentication System (100%)
- ✅ User Home (100%)
- ⏳ Còn lại: User Shopping Features (Product List, Cart, Checkout, Orders)

## 📝 TODO

Xem danh sách công việc tại: [TODO.md](TODO.md)

## 🎨 Screenshots

### Landing Page

![Landing Page](docs/screenshots/landing.png)

### Login Page

![Login](docs/screenshots/login.png)

### User Home

![Home](docs/screenshots/home.png)

### Admin Dashboard

![Admin](docs/screenshots/admin.png)

_(Screenshots sẽ được thêm sau)_

## 🔒 Bảo mật

- Password hashing với bcrypt
- Prepared statements chống SQL injection
- XSS prevention
- Session management với timeout (30 phút)
- Input validation & sanitization

## 🤝 Đóng góp

Dự án này là báo cáo cuối kỳ. Mọi góp ý xin gửi qua Issues.

## 📄 License

Dự án này thuộc về sinh viên thực hiện báo cáo cuối kỳ.

## 👨‍💻 Tác giả

- **Tên:** [Tên sinh viên]
- **MSSV:** [Mã số sinh viên]
- **Lớp:** [Lớp]
- **Email:** [Email]

## 📞 Liên hệ

- GitHub: [@Latruong22](https://github.com/Latruong22)
- Repository: [Web_TMDT](https://github.com/Latruong22/Web_TMDT)

---

**Ngày bắt đầu:** [Ngày bắt đầu]  
**Ngày hoàn thành (dự kiến):** [Ngày deadline]  
**Cập nhật lần cuối:** 11/10/2025
