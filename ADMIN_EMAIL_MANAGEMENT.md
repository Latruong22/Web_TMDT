# ✅ ADMIN EMAIL MANAGEMENT SYSTEM - HOÀN THÀNH

**Ngày hoàn thành:** 20/10/2025  
**Trạng thái:** ✅ Sẵn sàng sử dụng  
**Comments:** 100% tiếng Việt

---

## 📋 TỔNG QUAN

Hệ thống quản lý email cho Admin, cho phép:

- ✅ Gửi email hàng loạt cho tất cả users
- ✅ Gửi email cho user cụ thể (chọn multiple)
- ✅ Sử dụng templates có sẵn (General, Promotion)
- ✅ Tự soạn nội dung với WYSIWYG editor
- ✅ Preview trước khi gửi
- ✅ Personalization với variables ({fullname}, {email})

---

## 📁 CẤU TRÚC FILES

### 1. **Model - `model/email_model.php`**

#### Functions mới đã thêm:

**`getActiveUsers()`**

- Mục đích: Lấy danh sách tất cả users active
- Return: Array users với user_id, fullname, email
- SQL: SELECT từ bảng `users` WHERE status='active' AND role='user'

**`sendBulkEmail($user_emails, $subject, $message)`**

- Mục đích: Gửi email hàng loạt cho nhiều users
- Parameters:
  - `$user_emails`: Array chứa email và fullname
  - `$subject`: Tiêu đề email
  - `$message`: Nội dung HTML (có thể chứa variables)
- Return: Array kết quả với success count, failed count, details
- Features:
  - Personalization: Thay {fullname} và {email} cho từng user
  - Delay 100ms giữa các email để tránh spam
  - Error handling cho từng email

**`sendEmailToUser($user_id, $subject, $message)`**

- Mục đích: Gửi email cho 1 user cụ thể
- Parameters:
  - `$user_id`: ID của user
  - `$subject`: Tiêu đề
  - `$message`: Nội dung HTML
- Return: Boolean (true nếu thành công)
- Validation: Check user active trước khi gửi

**`createGeneralEmailTemplate($title, $content, $button_text, $button_link)`**

- Mục đích: Tạo HTML template cho email thông báo chung
- Parameters:
  - `$title`: Tiêu đề hiển thị trong header
  - `$content`: Nội dung chính
  - `$button_text`: Text của CTA button (optional)
  - `$button_link`: Link của button (optional)
- Return: HTML đầy đủ với styling
- Styling: Blue theme (#007bff)

**`createPromoEmailTemplate($promo_title, $promo_content, $promo_code, $discount, $expiry_date)`**

- Mục đích: Tạo HTML template cho email khuyến mãi
- Parameters:
  - `$promo_title`: Tiêu đề khuyến mãi
  - `$promo_content`: Mô tả chi tiết
  - `$promo_code`: Mã giảm giá
  - `$discount`: Mức giảm (VD: "20%")
  - `$expiry_date`: Ngày hết hạn
- Return: HTML với promo box đẹp
- Styling: Red theme (#ff6b6b), promo code box nổi bật

---

### 2. **Controller - `controller/controller_Admin/admin_email_controller.php`**

#### Actions:

**`get_users`** (GET)

- Mục đích: Lấy danh sách users để hiển thị trong form
- Response JSON:

```json
{
    "success": true,
    "users": [
        {"user_id": 1, "fullname": "...", "email": "..."},
        ...
    ],
    "total": 10
}
```

**`send_email`** (POST)

- Mục đích: Gửi email đến users
- POST data:
  - `recipient_type`: 'all' hoặc 'individual'
  - `user_ids`: JSON array của user IDs (nếu individual)
  - `subject`: Tiêu đề email
  - `message`: Nội dung HTML
  - `template_type`: 'custom', 'general', 'promo' (optional)
- Response JSON:

```json
{
    "success": true,
    "message": "Đã gửi thành công 5 email",
    "results": {
        "success": 5,
        "failed": 0,
        "details": [...]
    }
}
```

**`preview_template`** (GET)

- Mục đích: Lấy HTML preview của template
- GET params:
  - `template_type`: 'general' hoặc 'promo'
- Response JSON:

```json
{
  "success": true,
  "html": "<html>...</html>"
}
```

#### Security:

- ✅ `requireAdmin()` - Chỉ admin mới truy cập được
- ✅ Input validation (empty subject, message)
- ✅ Error handling với try-catch
- ✅ Activity logging

---

### 3. **View - `view/Admin/admin_email.php`**

#### Layout:

**Header:**

- Tiêu đề "Quản lý Email"
- Hiển thị tên admin đang login

**Main Form (Left Column - col-lg-8):**

1. **Chọn người nhận:**

   - Radio: "Tất cả users" hoặc "Chọn user cụ thể"
   - Nếu chọn "Cụ thể" → Hiện user list với checkboxes
   - Có "Select All" checkbox

2. **Chọn Template:**

   - Dropdown: Custom, General, Promotion
   - Auto-fill subject và content khi chọn

3. **Tiêu đề Email:**

   - Input text
   - Required field

4. **Editor Toolbar:**

   - Bold, Italic, Underline
   - Insert variables: {fullname}, {email}

5. **Content Editor:**

   - ContentEditable div (WYSIWYG)
   - Min height: 300px
   - Placeholder text

6. **Action Buttons:**
   - Gửi Email (Primary)
   - Xem trước (Secondary)
   - Xóa (Danger outline)

**Sidebar (Right Column - col-lg-4):**

1. **Hướng dẫn Card:**

   - Quick tips
   - Variable usage guide

2. **Templates Card:**
   - Template thumbnails
   - Click để load template

**Preview Modal:**

- Modal hiển thị email preview
- Replace variables với sample data
- Responsive layout

---

### 4. **CSS - `Css/Admin/admin_email.css`**

#### Key Styles:

**Email Card:**

- Box shadow cho depth
- Border radius 10px
- Header với background #f8f9fa

**User Selection:**

- Scrollable container (max-height: 300px)
- Custom scrollbar
- Hover effects
- User info layout (name + email)

**Editor:**

- Toolbar với buttons
- ContentEditable styling
- Focus effects
- Placeholder text

**Templates:**

- Hover animation (translateX)
- Border color change
- Icon colors

**Loading State:**

- Spinner animation
- Disabled pointer events
- Button với ::after spinner

**Floating Alerts:**

- Fixed position (top-right)
- Slide-in animation
- Auto-dismiss sau 5s

**Responsive:**

- Mobile: Stack columns
- Tablet: Adjust heights
- Desktop: Full layout

---

### 5. **JavaScript - `Js/Admin/email.js`**

#### Main Functions:

**`initializeRecipientToggle()`**

- Toggle giữa "All" và "Individual"
- Show/hide user selection container
- Auto-load users khi chọn Individual

**`loadUsersList()`**

- Fetch users từ API
- Handle loading state
- Display checkboxes với user info

**`displayUsersList(users)`**

- Render user checkboxes
- Add "Select All" option
- Bind checkbox events

**`initializeEditorButtons()`**

- Handle editor focus/blur
- Enable rich text commands

**`insertVariable(variable)`**

- Insert {fullname} hoặc {email} vào cursor position
- Use document.execCommand('insertText')

**`loadTemplate(templateType)`**

- Load pre-defined template content
- Auto-fill subject và editor
- Add fade-in animation

**`validateForm()`**

- Check subject không rỗng
- Check message không rỗng
- Check có chọn user (nếu Individual)
- Return boolean

**`sendEmail()`**

- Prepare FormData
- POST đến controller
- Handle loading state
- Show success/error alert
- Clear form nếu thành công

**`showPreview()`**

- Get editor content
- Replace variables với sample data
- Display trong modal
- Use Bootstrap Modal API

**`showAlert(message, type)`**

- Create floating alert
- Auto-dismiss sau 5s
- Remove old alerts

**`clearForm()`**

- Reset tất cả fields
- Uncheck users
- Clear editor content

---

## 🎨 UI/UX FEATURES

### Design:

- ✅ Modern Bootstrap 5 design
- ✅ Font Awesome icons
- ✅ Consistent color scheme (Blue primary)
- ✅ Card-based layout
- ✅ Responsive grid system

### Animations:

- ✅ Slide-down cho user selection
- ✅ Fade-in cho template load
- ✅ Slide-in cho floating alerts
- ✅ Hover effects cho buttons và cards
- ✅ Loading spinner

### User Experience:

- ✅ Clear visual hierarchy
- ✅ Helpful placeholder text
- ✅ Inline help text
- ✅ Confirm dialog trước khi gửi
- ✅ Loading state feedback
- ✅ Success/error messages
- ✅ Preview before send

---

## 🔄 WORKFLOW

### Gửi email cho tất cả users:

```
1. Admin mở trang Admin Email Management
   ↓
2. Chọn "Tất cả users" (đã chọn mặc định)
   ↓
3. Chọn template hoặc tự soạn:
   - Option A: Chọn template "General" hoặc "Promo" → Auto-fill
   - Option B: Tự soạn trong editor
   ↓
4. Nhập tiêu đề email
   ↓
5. Soạn/chỉnh sửa nội dung:
   - Sử dụng toolbar (Bold, Italic, etc.)
   - Insert variables {fullname}, {email}
   ↓
6. (Optional) Click "Xem trước" để preview
   ↓
7. Click "Gửi Email"
   ↓
8. Confirm dialog: "Bạn có chắc muốn gửi email đến tất cả users?"
   ↓
9. Click OK → Loading state
   ↓
10. Server gửi email hàng loạt:
    - Loop qua tất cả users
    - Personalize message cho từng user
    - Delay 100ms giữa mỗi email
    ↓
11. Response: "Đã gửi thành công 50 email"
    ↓
12. Floating alert hiển thị kết quả
    ↓
13. Form được clear tự động
```

### Gửi email cho user cụ thể:

```
1. Admin mở trang Admin Email Management
   ↓
2. Chọn "Chọn user cụ thể"
   ↓
3. User selection container xuất hiện
   ↓
4. System fetch danh sách users từ server
   ↓
5. Danh sách users hiển thị với checkboxes
   ↓
6. Admin chọn users:
   - Option A: Click từng checkbox
   - Option B: Click "Chọn tất cả"
   ↓
7. Soạn email (giống workflow trên)
   ↓
8. Click "Gửi Email"
   ↓
9. Confirm: "Bạn có chắc muốn gửi email đến 5 user được chọn?"
   ↓
10. System gửi email cho users đã chọn
    ↓
11. Response: "Đã gửi thành công 5 email"
```

---

## 📧 EMAIL TEMPLATES

### 1. General Template (Thông báo chung)

**Khi sử dụng:**

- Thông báo bảo trì website
- Cập nhật điều khoản dịch vụ
- Thông báo tính năng mới
- Thông tin quan trọng

**Design:**

- Header: Blue background (#007bff)
- Content: Light gray background (#f9f9f9)
- Optional CTA button
- Footer với copyright

**Variables hỗ trợ:**

- `{fullname}` - Tên người nhận
- `{email}` - Email người nhận

**Example:**

```html
Xin chào Nguyễn Văn A, Chúng tôi xin gửi đến bạn thông báo... [Nội dung] Trân
trọng, Snowboard Shop Team
```

---

### 2. Promo Template (Khuyến mãi)

**Khi sử dụng:**

- Flash sale
- Seasonal promotion
- Birthday voucher
- Loyalty program rewards

**Design:**

- Header: Red background (#ff6b6b)
- Promo box: Highlighted với large code
- Countdown/expiry date
- CTA button "Mua Sắm Ngay"
- Content: Pink tint (#fff5f5)

**Variables hỗ trợ:**

- `{fullname}` - Tên người nhận
- `{email}` - Email người nhận
- Plus: Custom promo fields khi tạo

**Example:**

```html
Xin chào Nguyễn Văn A, 🎉 KHUYẾN MÃI ĐẶC BIỆT Mã giảm giá: SUMMER2025 Giảm: 20%
Có hiệu lực đến: 31/12/2025 [CTA Button: Mua Sắm Ngay]
```

---

## 🧪 TESTING GUIDE

### Test Case 1: Gửi email cho tất cả users

**Steps:**

1. Login admin
2. Vào Admin Email Management
3. Chọn "Tất cả users"
4. Nhập subject: "Test Email"
5. Nhập message: "Đây là email test gửi đến {fullname}"
6. Click "Gửi Email"
7. Confirm OK

**Expected:**

- ✅ Loading spinner xuất hiện
- ✅ Alert success: "Đã gửi thành công N email"
- ✅ Form được clear
- ✅ Tất cả users nhận được email
- ✅ Email có tên user thật (không còn {fullname})

---

### Test Case 2: Gửi email cho user cụ thể

**Steps:**

1. Chọn "Chọn user cụ thể"
2. Wait for users load
3. Chọn 2-3 users
4. Soạn email
5. Gửi

**Expected:**

- ✅ User list hiển thị đúng
- ✅ Chỉ users được chọn nhận email
- ✅ Alert hiển thị số lượng đúng

---

### Test Case 3: Preview template

**Steps:**

1. Chọn template "General"
2. Click "Xem trước"

**Expected:**

- ✅ Modal xuất hiện
- ✅ Email preview với styling đúng
- ✅ Variables được replace với sample data

---

### Test Case 4: Validation

**Steps:**

1. Leave subject empty
2. Click "Gửi Email"

**Expected:**

- ✅ Alert warning: "Vui lòng nhập tiêu đề email"
- ✅ Focus vào subject input
- ✅ Không gửi email

---

### Test Case 5: Select All users

**Steps:**

1. Chọn "Chọn user cụ thể"
2. Click "Chọn tất cả"

**Expected:**

- ✅ Tất cả checkboxes được check
- ✅ Uncheck "Chọn tất cả" → Tất cả unchecked

---

## 🔒 SECURITY & PERMISSIONS

### Access Control:

- ✅ `requireAdmin()` middleware
- ✅ Session check
- ✅ Role validation (role = 'admin')

### Input Validation:

- ✅ Subject không rỗng
- ✅ Message không rỗng
- ✅ Recipient type validation ('all' or 'individual')
- ✅ User IDs validation (array, numeric)

### SQL Injection Prevention:

- ✅ Prepared statements
- ✅ Bind parameters
- ✅ No raw SQL queries

### XSS Prevention:

- ✅ `htmlspecialchars()` khi output
- ✅ Validate input trước khi lưu
- ✅ Content Security Policy (nếu có)

### Rate Limiting:

- ✅ 100ms delay giữa mỗi email
- ✅ Tránh spam Gmail SMTP
- ✅ Có thể thêm daily limit (future)

---

## 📊 MONITORING & LOGGING

### Activity Logging:

```php
error_log("Admin {$_SESSION['user_id']} sent email: {$results['success']} success, {$results['failed']} failed");
```

### Email Results:

- Success count
- Failed count
- Details array với từng email

### Future Enhancements:

- Database logging (email_logs table)
- Dashboard với statistics
- Email delivery rate
- Open rate tracking (với pixel)

---

## 🚀 FUTURE IMPROVEMENTS

### Phase 2 (Optional):

1. **Email Templates Management:**

   - CRUD templates trong admin panel
   - Save custom templates
   - Template categories

2. **Scheduling:**

   - Schedule email gửi vào thời gian cụ thể
   - Cron job integration

3. **Email History:**

   - Table `email_logs`
   - View lịch sử email đã gửi
   - Filter by date, recipient, status

4. **Rich Text Editor:**

   - Thay ContentEditable bằng TinyMCE/CKEditor
   - Upload images
   - More formatting options

5. **A/B Testing:**

   - Test 2 versions email
   - Track which performs better

6. **Segmentation:**

   - Send based on user segments
   - VD: Users có order > 5, users inactive 30 days

7. **Attachment Support:**
   - Upload và attach files
   - PDF, images, etc.

---

## ✅ CHECKLIST HOÀN THÀNH

- [x] Model functions (getActiveUsers, sendBulkEmail, sendEmailToUser)
- [x] Email templates (General, Promo)
- [x] Controller với 3 actions (get_users, send_email, preview_template)
- [x] Admin view với responsive layout
- [x] User selection với checkboxes
- [x] WYSIWYG editor với toolbar
- [x] Template selection
- [x] Preview modal
- [x] Send email với personalization
- [x] Validation
- [x] Loading states
- [x] Success/error alerts
- [x] Confirm dialogs
- [x] Clear form function
- [x] CSS styling và animations
- [x] JavaScript event handlers
- [x] Error handling
- [x] Security (admin only)
- [x] Comments 100% tiếng Việt
- [x] Documentation

---

## 📝 USAGE EXAMPLES

### Example 1: Gửi thông báo bảo trì

```
Subject: Thông báo bảo trì hệ thống
Template: General
Message:
    Kính gửi {fullname},

    Hệ thống sẽ bảo trì vào 25/10/2025 từ 2:00 - 4:00 sáng.
    Trong thời gian này, website sẽ tạm thời không hoạt động.

    Xin lỗi vì sự bất tiện này.

    Trân trọng,
    Snowboard Shop Team
```

### Example 2: Gửi voucher sinh nhật

```
Subject: 🎉 Chúc mừng sinh nhật {fullname}!
Template: Promo
Message:
    Chúc mừng sinh nhật {fullname}!

    Tặng bạn voucher giảm 30% mọi đơn hàng:
    Mã: BIRTHDAY2025

    Có hiệu lực trong 7 ngày!
```

---

**Status:** ✅ **PRODUCTION READY**  
**Version:** 1.0  
**Last Updated:** 20/10/2025
