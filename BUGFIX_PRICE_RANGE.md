# 🐛 Bug Fix - Khoảng giá sản phẩm

## ⚠️ Vấn đề phát hiện

### Screenshot từ người dùng:

- **Hiển thị**: Thấp nhất: 499.980đ | Cao nhất: 999.990đ
- **Thực tế**: Có sản phẩm giá 400.000đ (Oakley Flow M -20%)
- **Kết luận**: Giá thấp nhất SAI!

---

## 🔍 Nguyên nhân

### Code cũ (SAI):

```php
$prices = array_map(function($p) {
    return $p['price'] - ($p['manual_discount'] ?? 0);
}, $all_products);
```

**Vấn đề**:

- `manual_discount` là **phần trăm** (10, 20, 30...) chứ không phải số tiền
- Code đang **TRỪ trực tiếp** số phần trăm từ giá → SAI

**Ví dụ**:

```php
Giá: 500.000đ, Giảm: 20%
Code cũ: 500.000 - 20 = 499.980đ ❌ (SAI)
Đúng phải: 500.000 * (1 - 20/100) = 400.000đ ✅
```

---

## ✅ Giải pháp

### Code mới (ĐÚNG):

```php
$prices = array_map(function($p) {
    $discount = floatval($p['manual_discount'] ?? 0);
    return $p['price'] * (1 - $discount / 100);
}, $all_products);
```

**Công thức đúng**:

```
Giá sau giảm = Giá gốc × (1 - Phần trăm giảm / 100)
```

---

## 🔧 Các thay đổi

### File: `view/User/product_list.php`

#### 1. Sửa hiển thị khoảng giá (Line ~220)

```php
// CŨ (SAI)
$prices = array_map(function($p) {
    return $p['price'] - ($p['manual_discount'] ?? 0);
}, $all_products);

// MỚI (ĐÚNG)
$prices = array_map(function($p) {
    $discount = floatval($p['manual_discount'] ?? 0);
    return $p['price'] * (1 - $discount / 100);
}, $all_products);
```

#### 2. Sửa sắp xếp theo giá tăng dần (Line ~50)

```php
// CŨ (SAI)
case 'price_asc':
    usort($all_products, function($a, $b) {
        $price_a = $a['price'] - ($a['manual_discount'] ?? 0);
        $price_b = $b['price'] - ($b['manual_discount'] ?? 0);
        return $price_a - $price_b;
    });

// MỚI (ĐÚNG)
case 'price_asc':
    usort($all_products, function($a, $b) {
        $discount_a = floatval($a['manual_discount'] ?? 0);
        $discount_b = floatval($b['manual_discount'] ?? 0);
        $price_a = $a['price'] * (1 - $discount_a / 100);
        $price_b = $b['price'] * (1 - $discount_b / 100);
        return $price_a - $price_b;
    });
```

#### 3. Sửa sắp xếp theo giá giảm dần (Line ~60)

```php
// CŨ (SAI)
case 'price_desc':
    usort($all_products, function($a, $b) {
        $price_a = $a['price'] - ($a['manual_discount'] ?? 0);
        $price_b = $b['price'] - ($b['manual_discount'] ?? 0);
        return $price_b - $price_a;
    });

// MỚI (ĐÚNG)
case 'price_desc':
    usort($all_products, function($a, $b) {
        $discount_a = floatval($a['manual_discount'] ?? 0);
        $discount_b = floatval($b['manual_discount'] ?? 0);
        $price_a = $a['price'] * (1 - $discount_a / 100);
        $price_b = $b['price'] * (1 - $discount_b / 100);
        return $price_b - $price_a;
    });
```

---

## 🧪 Test Cases

### Trường hợp 1: Sản phẩm không giảm giá

```
Giá: 1.000.000đ
Giảm: 0%
Kết quả: 1.000.000 × (1 - 0/100) = 1.000.000đ ✅
```

### Trường hợp 2: Giảm 10%

```
Giá: 1.000.000đ
Giảm: 10%
Code cũ: 1.000.000 - 10 = 999.990đ ❌
Code mới: 1.000.000 × (1 - 10/100) = 900.000đ ✅
```

### Trường hợp 3: Giảm 20%

```
Giá: 500.000đ
Giảm: 20%
Code cũ: 500.000 - 20 = 499.980đ ❌
Code mới: 500.000 × (1 - 20/100) = 400.000đ ✅
```

### Trường hợp 4: Giảm 50%

```
Giá: 1.000.000đ
Giảm: 50%
Code cũ: 1.000.000 - 50 = 999.950đ ❌
Code mới: 1.000.000 × (1 - 50/100) = 500.000đ ✅
```

---

## 📊 Kết quả sau khi fix

### Ví dụ với 2 sản phẩm trong ảnh:

#### Sản phẩm 1: "Phụ kiện"

- Giá gốc: 1.000.000đ
- Giảm: 10%
- **Giá sau giảm**: 900.000đ

#### Sản phẩm 2: "Oakley Flow M Matte Black Goggle"

- Giá gốc: 500.000đ
- Giảm: 20%
- **Giá sau giảm**: 400.000đ

### Khoảng giá mới (ĐÚNG):

```
Thấp nhất: 400.000đ ✅ (thay vì 499.980đ)
Cao nhất: 900.000đ ✅ (thay vì 999.990đ)
```

---

## ✅ Checklist

- [x] Sửa tính toán khoảng giá trong sidebar
- [x] Sửa sắp xếp theo giá tăng dần
- [x] Sửa sắp xếp theo giá giảm dần
- [x] Test với các giá trị khác nhau
- [x] Kiểm tra các file khác có dùng công thức sai không

---

## 🔄 Testing

### Để test:

1. Refresh trang sản phẩm (Ctrl + Shift + R)
2. Kiểm tra phần "Khoảng giá" trong sidebar
3. Test sắp xếp "Giá: Thấp đến cao"
4. Test sắp xếp "Giá: Cao đến thấp"
5. Xác nhận giá hiển thị đúng với % giảm

---

## 📝 Notes

### Lưu ý quan trọng:

- `manual_discount` là **PHẦN TRĂM** (0-100)
- Không phải số tiền tuyệt đối
- Luôn dùng công thức: `price × (1 - discount / 100)`

### Impact:

- ✅ Khoảng giá hiển thị chính xác
- ✅ Sắp xếp theo giá hoạt động đúng
- ✅ Không ảnh hưởng đến tính năng khác

---

**Date**: October 11, 2025  
**Fixed by**: GitHub Copilot  
**Status**: ✅ RESOLVED  
**Files Changed**: 1 file (product_list.php)
