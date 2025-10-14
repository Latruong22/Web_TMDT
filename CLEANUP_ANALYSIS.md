# 🧹 DỰ ÁN CLEANUP - PHÂN TÍCH FILES CẦN XÓA

## 📋 DANH SÁCH FILES CẦN XÓA

### 🔴 1. DOCUMENTATION FILES - CÓ THỂ XÓA (Backup trước)

```
ADMIN_FILTER_AUDIT.md                    ❌ Documentation cũ
ADMIN_ORDER_FILTER_COMPLETE.md           ❌ Documentation cũ
ADMIN_PRODUCT_FILTER_COMPLETE.md         ❌ Documentation cũ
ADMIN_PROMOTION_REVIEW_COMPLETE.md       ❌ Documentation cũ
ADMIN_USER_FILTER_TEST.md                ❌ Documentation cũ
CHECKOUT_FOOTER_BACKTOTOP_FIX.md         ❌ Documentation cũ
DEBUG_GUIDE.md                           ❌ Documentation cũ
DETAILED_ASSESSMENT.md                   ❌ Documentation cũ
FINAL_CHECKOUT_FIX.md                    ❌ Documentation cũ
FINAL_FIX_SUMMARY.md                     ❌ Documentation cũ
FIX_VOUCHER_ISSUE.md                     ❌ Documentation cũ
FONT_UNIFICATION_AUTH_PAGES.md           ❌ Documentation cũ
FOOTER_ICON_FIX.md                       ❌ Documentation cũ
QUICK_CHECKLIST.md                       ❌ Documentation cũ
TEST_GUIDE_SIMPLE.md                     ❌ Documentation cũ
VOUCHER_CROSS_PAGE_FIX.md                ❌ Documentation cũ
```

**Lý do:** Các file markdown này là documentation/notes trong quá trình development.
**Action:** Có thể xóa HOẶC di chuyển vào folder `docs/` nếu muốn lưu lại.

---

### 🔴 2. TEST FILES - NÊN XÓA

```
check_vouchers.php                       ❌ Test script
create_test_vouchers.php                 ❌ Test script
debug_cart.html                          ❌ Debug HTML
test_cart_calculation.html               ❌ Test HTML
test_checkout_voucher.html               ❌ Test HTML
```

**Lý do:** Các file test không cần thiết trong production.
**Action:** XÓA an toàn.

---

### 🔴 3. DEPRECATED CSS FILES - XÓA

```
Css/User/cart.css                        ❌ KHÔNG được sử dụng
```

**Đã kiểm tra:**

- ✅ `cart_new.css` đang được sử dụng trong `view/User/cart.php`
- ❌ `cart.css` KHÔNG được reference trong bất kỳ file PHP nào

**Action:** XÓA an toàn.

---

### 🔴 4. DEPRECATED JS FILES - XÓA

```
Js/User/cart.js                          ❌ KHÔNG được sử dụng
```

**Đã kiểm tra:**

- ✅ `cart_simple.js` đang được sử dụng trong `view/User/cart.php`
- ❌ `cart.js` KHÔNG được reference trong bất kỳ file PHP nào

**Action:** XÓA an toàn.

---

### 🔴 5. SCRIPT FILES - CÓ THỂ XÓA

```
serve_project.ps1                        ⚠️ PowerShell script để serve project
stop_port_3000.ps1                       ⚠️ PowerShell script để stop port
```

**Lý do:** Development scripts, không cần cho production.
**Action:** XÓA nếu deploy lên server. GIỮ LẠI nếu dev local.

---

## ✅ FILES QUAN TRỌNG - KHÔNG XÓA

### 📦 Core Files

```
index.php                                ✅ Entry point
snowboard_web.sql                        ✅ Database schema
README.md                                ✅ Project documentation
```

### 📁 Folders

```
config/                                  ✅ Bootstrap & configs
controller/                              ✅ Controllers
model/                                   ✅ Models
view/                                    ✅ Views
Css/                                     ✅ Stylesheets (trừ cart.css)
Js/                                      ✅ JavaScript (trừ cart.js)
Images/                                  ✅ Images
.git/                                    ✅ Git repository
```

---

## 📊 TỔNG KẾT

| Category       | Files to Delete | Action                          |
| -------------- | --------------- | ------------------------------- |
| Documentation  | 16 files        | Xóa hoặc move to docs/          |
| Test Files     | 5 files         | XÓA                             |
| Deprecated CSS | 1 file          | XÓA                             |
| Deprecated JS  | 1 file          | XÓA                             |
| Scripts        | 2 files         | XÓA (production) hoặc GIỮ (dev) |
| **TOTAL**      | **25 files**    |                                 |

---

## 🚨 SAFETY CHECK

### ✅ Đã Verify An Toàn:

**cart.css:**

```bash
# Tìm kiếm trong tất cả .php files
grep -r "cart.css" view/
# Result: No matches found ✅
```

**cart.js:**

```bash
# Tìm kiếm trong tất cả .php files
grep -r "cart.js" view/
# Result: No matches found ✅
```

**cart_new.css:**

```bash
# Tìm kiếm trong tất cả .php files
grep -r "cart_new.css" view/
# Result: view/User/cart.php:24 ✅ ĐANG DÙNG
```

**cart_simple.js:**

```bash
# Tìm kiếm trong tất cả .php files
grep -r "cart_simple.js" view/
# Result: view/User/cart.php:331 ✅ ĐANG DÙNG
```

---

## 🎯 RECOMMENDED CLEANUP STEPS

### Step 1: Backup (Safety First)

```powershell
# Tạo backup folder
mkdir backup_cleanup_$(Get-Date -Format 'yyyyMMdd_HHmmss')

# Copy files cần xóa vào backup
cp ADMIN_*.md backup_cleanup_*/
cp *.html backup_cleanup_*/
cp check_vouchers.php backup_cleanup_*/
cp create_test_vouchers.php backup_cleanup_*/
```

### Step 2: Xóa Test Files (An toàn nhất)

```powershell
Remove-Item check_vouchers.php
Remove-Item create_test_vouchers.php
Remove-Item debug_cart.html
Remove-Item test_cart_calculation.html
Remove-Item test_checkout_voucher.html
```

### Step 3: Xóa Deprecated CSS/JS

```powershell
Remove-Item Css\User\cart.css
Remove-Item Js\User\cart.js
```

### Step 4: Xóa Documentation (Tùy chọn)

```powershell
# Option A: Xóa hết
Remove-Item *_FIX*.md
Remove-Item *_GUIDE*.md
Remove-Item ADMIN_*.md
Remove-Item CHECKOUT_*.md
Remove-Item DETAILED_*.md
Remove-Item FINAL_*.md
Remove-Item FONT_*.md
Remove-Item FOOTER_*.md
Remove-Item QUICK_*.md
Remove-Item VOUCHER_*.md

# Option B: Move to docs folder
mkdir docs
Move-Item *.md docs\ -Exclude README.md
```

### Step 5: Xóa Scripts (Nếu production)

```powershell
Remove-Item serve_project.ps1
Remove-Item stop_port_3000.ps1
```

---

## ⚠️ WARNINGS

1. **KHÔNG XÓA:**

   - `README.md` - Project documentation chính
   - `snowboard_web.sql` - Database schema
   - `index.php` - Entry point
   - `cart_new.css` - ✅ ĐANG ĐƯỢC DÙNG
   - `cart_simple.js` - ✅ ĐANG ĐƯỢC DÙNG

2. **Double Check Before Delete:**

   - Chạy test website trước
   - Verify cart page hoạt động tốt
   - Check git status

3. **Git Cleanup:**
   ```bash
   # Sau khi xóa files, commit
   git add .
   git commit -m "chore: cleanup test files and deprecated assets"
   ```

---

## 📝 NOTES

**Files giữ lại:**

- `README.md` - Essential project info
- `snowboard_web.sql` - Database
- `cart_new.css` - Active cart stylesheet
- `cart_simple.js` - Active cart JavaScript
- All folders in config/, controller/, model/, view/, Images/

**Files có thể xóa an toàn:**

- Tất cả `.md` files trừ `README.md`
- Tất cả test `.html` files
- `check_vouchers.php`, `create_test_vouchers.php`
- `cart.css`, `cart.js` (deprecated)
- `serve_project.ps1`, `stop_port_3000.ps1` (dev scripts)

**Kích thước giải phóng ước tính:** ~500KB-1MB

---

## ✅ FINAL CHECKLIST

- [ ] Backup all files trước khi xóa
- [ ] Test website sau mỗi bước xóa
- [ ] Verify cart page works với cart_new.css
- [ ] Verify cart functionality với cart_simple.js
- [ ] Check console for errors
- [ ] Git commit changes
- [ ] Document what was removed

**Status: READY TO EXECUTE** 🚀
