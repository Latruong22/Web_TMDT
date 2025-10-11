# 📸 HƯỚNG DẪN TEST PRODUCT LIST

## 🚀 CÁC BƯỚC TEST

### Bước 1: Khởi động XAMPP

```powershell
# Mở XAMPP Control Panel
# Start Apache
# Start MySQL
```

### Bước 2: Truy cập trang

```
http://localhost/Web_TMDT/view/User/product_list.php
```

### Bước 3: Đăng nhập (nếu cần)

- Email: `user@test.com`
- Password: `user123`

---

## ✅ CHECKLIST KIỂM TRA

### 1. Hero Banner

- [ ] Banner hiển thị ảnh `baner_product.jpg`
- [ ] Text "Bộ Sưu Tập Snowboard" hiển thị rõ ràng
- [ ] Breadcrumb (Home > Sản phẩm) hoạt động
- [ ] Parallax effect khi scroll

### 2. Sidebar

- [ ] Search box hoạt động
- [ ] Category list hiển thị đầy đủ
- [ ] Category count đúng
- [ ] Click category → filter products
- [ ] Price range hiển thị min/max
- [ ] Sidebar banner hiển thị

### 3. Product Grid

- [ ] Hiển thị 12 sản phẩm/page
- [ ] Layout 3-4 columns (desktop)
- [ ] Product images load
- [ ] Hover vào card → zoom image
- [ ] Hover → hiện quick actions

### 4. Product Cards

- [ ] Product name hiển thị (2 lines max)
- [ ] Giá hiển thị đúng
- [ ] Discount badge (nếu có)
- [ ] Stock badge (nếu low/out)
- [ ] "Add to cart" button hoạt động
- [ ] "View detail" button hoạt động

### 5. Toolbar

- [ ] Results count đúng
- [ ] Sort dropdown hoạt động:
  - [ ] Mới nhất
  - [ ] Giá tăng dần
  - [ ] Giá giảm dần
  - [ ] Tên A-Z

### 6. Search

- [ ] Nhập keyword → tìm thấy sản phẩm
- [ ] Không tìm thấy → empty state
- [ ] Clear search hoạt động

### 7. Pagination

- [ ] Page numbers hiển thị đúng
- [ ] Click page → chuyển trang
- [ ] Previous/Next arrows hoạt động
- [ ] Active page highlight

### 8. Add to Cart

- [ ] Click "Add to cart" → toast notification
- [ ] Cart badge cập nhật số lượng
- [ ] Cart badge bounce animation
- [ ] Button pulse effect

### 9. Animations

- [ ] Product cards fade in khi scroll
- [ ] Images lazy load
- [ ] Hover effects smooth
- [ ] Back-to-top button xuất hiện khi scroll

### 10. Responsive

- [ ] Desktop (>991px): 4 columns
- [ ] Tablet (768-991px): 3 columns, sidebar on top
- [ ] Mobile (576-768px): 2 columns
- [ ] Small Mobile (<576px): 2 columns compact

---

## 🎯 TEST SCENARIOS

### Scenario 1: Browse All Products

1. Vào trang product_list.php
2. Xem tất cả sản phẩm
3. Scroll xuống → see animations
4. Click page 2 → see new products

### Scenario 2: Filter by Category

1. Click category "Snowboard"
2. Xem chỉ sản phẩm snowboard
3. Check count đúng
4. Click "Tất cả" → back to all

### Scenario 3: Search Products

1. Nhập "board" vào search
2. Click search icon
3. Xem kết quả tìm kiếm
4. Xóa search → back to all

### Scenario 4: Sort Products

1. Select "Giá tăng dần"
2. Check products sorted by price low to high
3. Select "Giá giảm dần"
4. Check products sorted by price high to low

### Scenario 5: Add to Cart

1. Hover product card
2. Click cart icon
3. See toast "Đã thêm vào giỏ"
4. Check navbar badge updated
5. Click multiple products
6. Check badge count increases

### Scenario 6: Navigate to Detail

1. Hover product card
2. Click eye icon
3. Should go to product_detail.php?id=X
4. (Will implement next)

### Scenario 7: Mobile View

1. Resize browser to 375px width
2. Check layout responsive
3. Check sidebar not overlapping
4. Check cards in 2 columns
5. Check all buttons tappable

---

## 🐛 COMMON ISSUES & FIXES

### Issue 1: Images không hiển thị

**Cause:** Path sai hoặc images không tồn tại  
**Fix:** Check `Images/product/` folder có files không

### Issue 2: Banner không hiển thị

**Cause:** `baner_product.jpg` không tồn tại  
**Fix:** Check `Images/baner/baner_product.jpg`

### Issue 3: Cart badge không update

**Cause:** JavaScript error  
**Fix:** F12 → Console → check errors

### Issue 4: Pagination không hoạt động

**Cause:** URL parameters bị mất  
**Fix:** Check form hidden inputs

### Issue 5: Search không tìm thấy

**Cause:** Case sensitive  
**Fix:** Already handled with mb_strtolower

---

## 📷 SCREENSHOTS NEEDED

### 1. Desktop View

- [ ] Full page overview
- [ ] Hero banner
- [ ] Product grid (4 columns)
- [ ] Sidebar filter
- [ ] Hover effects
- [ ] Pagination

### 2. Mobile View

- [ ] Mobile layout (2 columns)
- [ ] Mobile nav
- [ ] Mobile sidebar
- [ ] Mobile cards

### 3. Interactions

- [ ] Cart badge with count
- [ ] Toast notification
- [ ] Hover actions
- [ ] Sort dropdown

### 4. Empty State

- [ ] Search no results
- [ ] Filter no products

---

## 🎨 VISUAL CHECKS

### Colors

- [ ] Black navbar (#000)
- [ ] Red discount badges (#ff416c gradient)
- [ ] White cards
- [ ] Gray backgrounds (#f5f5f5)

### Typography

- [ ] Titles bold, readable
- [ ] Prices prominent
- [ ] Text hierarchy clear

### Spacing

- [ ] Cards have equal gaps
- [ ] Padding consistent
- [ ] No overflow issues

### Shadows

- [ ] Cards have subtle shadows
- [ ] Hover shadows deeper
- [ ] Buttons have shadows

---

## ⚡ PERFORMANCE CHECKS

- [ ] Page loads < 2 seconds
- [ ] Images load progressively
- [ ] Animations smooth (60fps)
- [ ] No JavaScript errors
- [ ] No CSS warnings

---

## 🔐 SECURITY CHECKS

- [ ] SQL injection safe (prepared statements)
- [ ] XSS safe (htmlspecialchars)
- [ ] Session check working
- [ ] No exposed sensitive data

---

## ✅ SIGN-OFF

**Tested by:** ********\_********  
**Date:** ********\_********  
**Status:** [ ] PASS [ ] FAIL  
**Notes:** ********\_********

---

## 🎉 SUCCESS CRITERIA

Product List page is considered COMPLETE when:

- ✅ All products display correctly
- ✅ All filters work
- ✅ Search works
- ✅ Sort works
- ✅ Pagination works
- ✅ Add to cart works
- ✅ Cart badge updates
- ✅ Responsive on all devices
- ✅ No errors in console
- ✅ Professional appearance

---

**If all checks pass → READY FOR PRODUCTION!** 🚀

**Next:** Implement Product Detail page
