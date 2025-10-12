# 🔧 SETUP PRODUCT FOLDERS - HƯỚNG DẪN

## 📅 Date: 12/10/2025

---

## 🎯 VẤN ĐỀ

Bạn đã:

1. ✅ Xóa folders Sp1-Sp7 cũ trong `Images/product/`
2. ✅ Xóa tất cả products cũ trong database
3. ✅ Thêm products mới qua Admin (ID: 16, 17, 18)
4. ❌ **Images không hiện trong product_detail** vì không có folders Sp{id}/

---

## ✅ SOLUTION

### Bước 1: Chạy Setup Script (AUTO)

**Mở trong browser:**

```
http://localhost/Web_TMDT/setup_product_folders.php
```

**Script sẽ tự động:**

- ✅ Tạo folder `Sp16/`, `Sp17/`, `Sp18/` cho mỗi product
- ✅ Copy main image từ database vào mỗi folder
- ✅ Hiển thị báo cáo đầy đủ

---

### Bước 2: Upload Thêm Ảnh Detail

Mỗi product NÊN có **3-4 ảnh** từ góc độ khác nhau để gallery đẹp.

**Ví dụ Product #16 (Snowboard):**

Upload vào: `C:\xampp\htdocs\Web_TMDT\Images\product\Sp16\`

Các file:

```
Sp16/
├── product_68eb6013b62847.05818374.jpg  (đã có - từ database)
├── snowboard_angle1.jpg                  (thêm - góc nghiêng)
├── snowboard_top.jpg                     (thêm - nhìn từ trên)
├── snowboard_bottom.jpg                  (thêm - mặt đế)
```

**Ví dụ Product #17 (Goggle):**

Upload vào: `C:\xampp\htdocs\Web_TMDT\Images\product\Sp17\`

Các file:

```
Sp17/
├── product_68eb6052949343.28627805.jpg  (đã có)
├── goggle_side.jpg                       (thêm - góc bên)
├── goggle_inside.jpg                     (thêm - bên trong)
├── goggle_wearing.jpg                    (thêm - đeo thử)
```

**Ví dụ Product #18 (Boots):**

Upload vào: `C:\xampp\htdocs\Web_TMDT\Images\product\Sp18\`

Các file:

```
Sp18/
├── product_68eb61657ac5f9.25793498.jpg  (đã có)
├── boots_side.jpg                        (thêm - góc bên)
├── boots_back.jpg                        (thêm - phía sau)
├── boots_sole.jpg                        (thêm - đế giày)
```

---

### Bước 3: Test Products

**Test từng product:**

```
http://localhost/Web_TMDT/view/User/product_detail.php?id=16
http://localhost/Web_TMDT/view/User/product_detail.php?id=17
http://localhost/Web_TMDT/view/User/product_detail.php?id=18
```

**Expected results:**

- ✅ Main image loads
- ✅ Thumbnail gallery shows ALL images (3-4 ảnh)
- ✅ Click thumbnail → Changes main image
- ✅ Zoom works
- ✅ Console: No 404 errors

---

## 📁 FOLDER STRUCTURE

```
Images/
└── product/
    ├── Sp16/  (Snowboard - Product ID 16)
    │   ├── product_68eb6013b62847.05818374.jpg
    │   ├── snowboard_angle1.jpg
    │   ├── snowboard_top.jpg
    │   └── snowboard_bottom.jpg
    │
    ├── Sp17/  (Goggle - Product ID 17)
    │   ├── product_68eb6052949343.28627805.jpg
    │   ├── goggle_side.jpg
    │   ├── goggle_inside.jpg
    │   └── goggle_wearing.jpg
    │
    └── Sp18/  (Boots - Product ID 18)
        ├── product_68eb61657ac5f9.25793498.jpg
        ├── boots_side.jpg
        ├── boots_back.jpg
        └── boots_sole.jpg
```

---

## 💡 IMAGE GUIDELINES

### Kích thước khuyến nghị:

- **Width:** 800-1200px
- **Height:** 800-1200px
- **Aspect ratio:** 1:1 (vuông) hoặc 4:3
- **Format:** JPG (quality 80-90%)
- **File size:** < 500KB mỗi ảnh

### Tên file:

```
✅ GOOD:
- product_1.jpg
- snowboard_top.jpg
- boots_side_view.jpg

❌ BAD:
- Image 1.jpg        (có space)
- Ảnh-sản-phẩm.jpg  (tiếng Việt)
- PHOTO(1).jpg       (ký tự đặc biệt)
```

### Nội dung ảnh:

- 📸 **Main image:** Product chính diện, nền trắng
- 📸 **Angle 1:** Góc 45° để thấy chi tiết
- 📸 **Angle 2:** Side view (góc bên)
- 📸 **Detail:** Close-up chi tiết quan trọng
- 📸 **In use:** Ảnh đang sử dụng (optional)

---

## 🔄 KHI THÊM PRODUCT MỚI

**Mỗi lần thêm product qua Admin:**

### Option 1: Chạy lại setup script

```
http://localhost/Web_TMDT/setup_product_folders.php
```

→ Tự động tạo folder + copy image

### Option 2: Manual (không khuyến nghị)

1. Tạo folder: `Images/product/Sp{new_id}/`
2. Upload 3-4 ảnh vào folder đó
3. Test: `product_detail.php?id={new_id}`

---

## 🐛 TROUBLESHOOTING

### Problem: Ảnh vẫn không hiện

**Check:**

```powershell
# 1. Folder có tồn tại?
Test-Path "C:\xampp\htdocs\Web_TMDT\Images\product\Sp16"

# 2. Folder có ảnh?
Get-ChildItem "C:\xampp\htdocs\Web_TMDT\Images\product\Sp16"

# 3. Check permissions
Get-Acl "C:\xampp\htdocs\Web_TMDT\Images\product\Sp16" | Format-List
```

**Solutions:**

- ❌ Folder không tồn tại → Chạy lại setup_product_folders.php
- ❌ Folder rỗng → Upload ảnh vào
- ❌ Permission denied → Right-click folder → Properties → Security → Add "Everyone" with Read

---

### Problem: Chỉ hiện 1 ảnh

**Check:**

- Folder có bao nhiêu ảnh? (Cần ít nhất 2-3 ảnh)
- Extensions đúng? (.jpg, .jpeg, .png, .gif, .webp)
- Console có lỗi? (F12 → Console)

**Solution:**

- Upload thêm ảnh vào folder Sp{id}/
- Refresh trang (Ctrl + Shift + R)

---

### Problem: Console shows 404 errors

**Check URL trong console:**

```
❌ WRONG: ../../Images/product/Sp16/image.jpg
✅ CORRECT: /Web_TMDT/Images/product/Sp16/image.jpg
```

**Solution:**

- Đã fix trong product_detail.php (dùng absolute paths)
- Clear cache: Ctrl + Shift + R

---

## 📊 CURRENT STATUS

**Products in database:**

```
Product #16: Lib Tech Men's Son of Birdman Snowboard
Product #17: Oakley Flow M Matte Black Goggle
Product #18: Burton Men's Highshot X Step On Snowboard Boots
```

**Folders to create:**

```
✅ Sp16/ → For Product #16 (Snowboard)
✅ Sp17/ → For Product #17 (Goggle)
✅ Sp18/ → For Product #18 (Boots)
```

---

## ✅ CHECKLIST

### Immediate (Ngay bây giờ):

- [ ] Chạy `setup_product_folders.php`
- [ ] Verify 3 folders created: Sp16/, Sp17/, Sp18/
- [ ] Verify main images copied into folders

### Short-term (Trong hôm nay):

- [ ] Upload 2-3 ảnh detail cho Product #16
- [ ] Upload 2-3 ảnh detail cho Product #17
- [ ] Upload 2-3 ảnh detail cho Product #18
- [ ] Test tất cả 3 product detail pages

### Long-term (Khi thêm products mới):

- [ ] Chạy setup_product_folders.php sau mỗi lần add product
- [ ] Hoặc manual tạo folder Sp{id}/ + upload ảnh
- [ ] Always có ít nhất 3 ảnh cho mỗi product

---

## 🎯 QUICK COMMANDS

**Chạy setup:**

```
http://localhost/Web_TMDT/setup_product_folders.php
```

**Test products:**

```
http://localhost/Web_TMDT/view/User/product_detail.php?id=16
http://localhost/Web_TMDT/view/User/product_detail.php?id=17
http://localhost/Web_TMDT/view/User/product_detail.php?id=18
```

**Product list:**

```
http://localhost/Web_TMDT/view/User/product_list.php
```

---

**Status:** ✅ **SETUP SCRIPT READY - CHẠY NGAY!**

**Next:** Mở `setup_product_folders.php` → Click button → Upload thêm ảnh → Test! 🚀
