# ✅ QUICK CHECKLIST - USER SHOPPING FEATURES

## 📋 TUẦN 1-2: PRODUCT & CART

### Day 1-2: Product List Page

- [ ] Tạo file `view/User/product_list.php`
- [ ] Tạo file `Css/User/product_list.css`
- [ ] Tạo file `Js/User/product_list.js`
- [ ] Implement grid layout (3-4 columns)
- [ ] Add pagination (12 products/page)
- [ ] Add sidebar với category filter
- [ ] Add sort dropdown (price, name, newest)
- [ ] Add search bar
- [ ] Show product: image, name, price, discount, stock
- [ ] Add "Add to cart" button
- [ ] Test responsive design

### Day 3-4: Product Detail Page

- [ ] Tạo file `view/User/product_detail.php`
- [ ] Tạo file `Css/User/product_detail.css`
- [ ] Tạo file `Js/User/product_detail.js`
- [ ] Display main product image + gallery
- [ ] Show full product info (name, price, discount, description, stock)
- [ ] Add quantity selector (+/-)
- [ ] Add "Add to cart" button
- [ ] Show related products section (4-6 items)
- [ ] Display reviews section (read-only)
- [ ] Show rating stars
- [ ] Test all functionality

### Day 5-6: Shopping Cart

- [ ] Tạo file `view/User/cart.php`
- [ ] Tạo file `Css/User/cart.css` (đã có, check nội dung)
- [ ] Tạo file `Js/User/cart.js` (đã có, check nội dung)
- [ ] Tạo file `model/cart_model.php`
- [ ] Implement `addToCart($product_id, $quantity)`
- [ ] Implement `updateCart($product_id, $quantity)`
- [ ] Implement `removeFromCart($product_id)`
- [ ] Implement `getCart()`
- [ ] Implement `calculateTotal()`
- [ ] Implement `applyVoucher($code)`
- [ ] Implement `clearCart()`
- [ ] Display cart items in table
- [ ] Add update quantity buttons (+/-)
- [ ] Add remove item button
- [ ] Show subtotal, discount, total
- [ ] Add voucher input field
- [ ] Add "Proceed to checkout" button
- [ ] Show empty cart message
- [ ] Update cart count in header
- [ ] Test session-based cart

### Day 7: Integration & Testing

- [ ] Link product_list to product_detail
- [ ] Link "Add to cart" buttons to cart
- [ ] Update header cart icon with count
- [ ] Test full flow: browse → detail → add to cart
- [ ] Test cart update/remove
- [ ] Test voucher application
- [ ] Fix bugs
- [ ] Mobile responsive testing

---

## 📋 TUẦN 3: CHECKOUT & ORDERS

### Day 1-2: Checkout

- [ ] Tạo file `view/User/checkout.php`
- [ ] Tạo file `Css/User/checkout.css` (đã có, check)
- [ ] Tạo file `Js/User/checkout.js` (đã có, check)
- [ ] Tạo file `controller/controller_User/order_controller.php`
- [ ] Display cart summary (items, total)
- [ ] Create shipping form (name, phone, address, note)
- [ ] Add payment method selection
- [ ] Add voucher display (if applied)
- [ ] Add "Place order" button
- [ ] Implement order creation logic:
  - [ ] Insert into `orders` table
  - [ ] Insert into `order_details` table
  - [ ] Update product stock
  - [ ] Update voucher usage
  - [ ] Clear cart
- [ ] Redirect to order confirmation page
- [ ] Test complete checkout flow

### Day 3: Order History

- [ ] Tạo file `view/User/order_history.php`
- [ ] Tạo file `Css/User/order_history.css` (đã có, check)
- [ ] Tạo file `Js/User/order_history.js` (đã có, check)
- [ ] List all user orders
- [ ] Show: order_id, date, total, status
- [ ] Add "View details" button/link
- [ ] Add "Cancel" button (if pending)
- [ ] Add pagination
- [ ] Filter by status (optional)
- [ ] Test order listing

### Day 4: Order Tracking

- [ ] Tạo file `view/User/order_tracking.php`
- [ ] Tạo file `Css/User/order_tracking.css` (đã có, check)
- [ ] Tạo file `Js/User/order_tracking.js` (đã có, check)
- [ ] Get order by ID
- [ ] Show order details:
  - [ ] Order info (ID, date, status, total)
  - [ ] Customer info
  - [ ] Shipping address
  - [ ] Order items
- [ ] Display status timeline:
  - [ ] Pending
  - [ ] Confirmed
  - [ ] Shipping
  - [ ] Delivered
- [ ] Show estimated delivery (optional)
- [ ] Add contact support link
- [ ] Test tracking view

### Day 5: Order Cancel

- [ ] Tạo file `view/User/order_cancel.php` (hoặc modal)
- [ ] Tạo file `Css/User/order_cancel.css` (đã có, check)
- [ ] Tạo file `Js/User/order_cancel.js` (đã có, check)
- [ ] Add cancel reason form
- [ ] Add confirmation dialog
- [ ] Implement cancel logic:
  - [ ] Update order status to 'cancelled'
  - [ ] Restore product stock
  - [ ] Update voucher usage (if used)
- [ ] Redirect to order history
- [ ] Test cancel functionality

### Day 6-7: Polish & Testing

- [ ] Bug fixes
- [ ] UI/UX improvements
- [ ] Mobile responsive testing
- [ ] Cross-browser testing
- [ ] Security review
- [ ] Performance optimization

---

## 📝 IMPLEMENTATION NOTES

### Session Cart Structure

```php
$_SESSION['cart'] = [
    'product_id_1' => [
        'product_id' => 1,
        'name' => 'Product Name',
        'price' => 1000000,
        'discount' => 100000,
        'quantity' => 2,
        'image' => 'product.jpg',
        'stock' => 50
    ],
    'product_id_2' => [...],
];
```

### Order Creation Flow

```
1. User clicks "Place order"
2. Validate cart not empty
3. Validate shipping form
4. Calculate total
5. Begin transaction
6. Insert into orders table
7. Insert into order_details table
8. Update product stocks
9. Update voucher usage (if used)
10. Commit transaction
11. Clear cart
12. Redirect to confirmation
```

### Cart Functions

```php
// model/cart_model.php
function addToCart($product_id, $quantity) {
    // Check product exists
    // Check stock
    // Add to $_SESSION['cart']
}

function updateCart($product_id, $quantity) {
    // Update quantity
    // Check stock
}

function removeFromCart($product_id) {
    // Remove from session
}

function getCart() {
    // Return $_SESSION['cart']
}

function calculateTotal() {
    // Sum all items
    // Apply discount
    // Apply voucher
}
```

---

## 🔧 REUSABLE COMPONENTS

### From Admin Panel

- Table styles → Adapt for order history
- Modal styles → Use for order details
- Form styles → Use for checkout
- Button styles → Consistent design
- Alert styles → Success/error messages

### From User Home

- Product card design → Use in product list
- Header/Footer → Reuse everywhere
- Navigation → Update cart count

---

## 🎯 SUCCESS CRITERIA

### Product List

- ✅ Shows all active products
- ✅ Filter & sort working
- ✅ Pagination working
- ✅ Add to cart working
- ✅ Responsive design

### Product Detail

- ✅ Shows correct product info
- ✅ Gallery working
- ✅ Quantity selector working
- ✅ Add to cart working
- ✅ Related products showing

### Shopping Cart

- ✅ Shows all cart items
- ✅ Update quantity working
- ✅ Remove item working
- ✅ Total calculation correct
- ✅ Voucher application working
- ✅ Cart persists in session

### Checkout

- ✅ Form validation working
- ✅ Order creation successful
- ✅ Stock updated correctly
- ✅ Cart cleared after order
- ✅ Redirect to confirmation

### Orders

- ✅ Order history shows all orders
- ✅ Order tracking shows details
- ✅ Cancel order working
- ✅ Stock restored on cancel

---

## 📚 HELPFUL REFERENCES

- **DEVELOPER_GUIDE.md** - Code examples & patterns
- **admin_product.php** - Reference for product display
- **admin_order.php** - Reference for order structure
- **product_model.php** - Already has functions
- **order_model.php** - Already has functions
- **promotion_model.php** - For voucher validation

---

## ⚡ QUICK START

### Bắt đầu với Product List:

1. Copy structure from `admin_product.php`
2. Adapt for user-facing design
3. Remove admin-only features
4. Focus on grid layout instead of table
5. Add filter sidebar
6. Test thoroughly

### Then Product Detail:

1. Get product by ID
2. Display in attractive layout
3. Add image gallery
4. Implement add to cart
5. Show related products

### Then Cart:

1. Create cart_model.php first
2. Implement all cart functions
3. Create cart.php view
4. Test cart operations

### Finally Checkout & Orders:

1. Create checkout form
2. Implement order creation
3. Create order pages
4. Test complete flow

---

**Good luck! You're 75% done already! 💪**

Còn 25% nữa là hoàn thành project! 🎉
