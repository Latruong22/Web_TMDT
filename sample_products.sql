-- Dữ liệu mẫu cho bảng categories
INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, 'Ván trượt tuyết'),
(2, 'Giày trượt tuyết'),
(3, 'Phụ kiện');

-- Dữ liệu mẫu cho bảng products
INSERT INTO `products` (`product_id`, `name`, `price`, `description`, `category_id`, `stock`, `image`, `status`, `manual_discount`, `created_at`) VALUES
(16, 'Burton Custom Flying V Snowboard 2024', 12500000, 'Ván trượt tuyết cao cấp từ Burton với công nghệ Flying V độc đáo, phù hợp cho mọi địa hình và phong cách trượt.', 1, 15, 'Images/product/Sp16/1.jpg', 'active', 10, NOW()),
(17, 'K2 Maysis Snowboard Boots', 8500000, 'Giày trượt tuyết K2 Maysis với hệ thống đóng Boa® H4 Coiler nhanh chóng và thoải mái tối đa.', 2, 20, 'Images/product/Sp17/1.jpg', 'active', 15, NOW()),
(18, 'Oakley Flight Deck XM Goggles', 3500000, 'Kính trượt tuyết Oakley với tầm nhìn rộng và công nghệ chống sương mù Prizm.', 3, 30, 'Images/product/Sp18/1.jpg', 'active', 0, NOW()),
(19, 'Lib Tech T.Rice Pro Snowboard', 15000000, 'Ván trượt chuyên nghiệp được thiết kế bởi Travis Rice, sử dụng công nghệ Magne-Traction độc quyền.', 1, 10, 'Images/product/Sp19/1.jpg', 'active', 5, NOW()),
(20, 'Salomon Dialogue Wide Snowboard Boots', 7200000, 'Giày trượt tuyết phù hợp với bàn chân rộng, mang lại sự thoải mái suốt cả ngày trượt.', 2, 18, 'Images/product/Sp20/1.jpg', 'active', 20, NOW()),
(21, 'Burton AK Baker Down Jacket', 6800000, 'Áo khoác lông vũ cao cấp với khả năng giữ ấm và chống nước tuyệt vời.', 3, 25, 'Images/product/Sp21/1.jpg', 'active', 10, NOW()),
(22, 'Jones Flagship Snowboard', 13500000, 'Ván trượt all-mountain với độ bền cao và khả năng kiểm soát tốt trên mọi địa hình.', 1, 12, 'Images/product/Sp22/1.jpg', 'active', 8, NOW()),
(23, 'ThirtyTwo TM-2 Snowboard Boots', 6500000, 'Giày trượt tuyết nhẹ và linh hoạt, lý tưởng cho freestyle và jibbing.', 2, 22, 'Images/product/Sp23/1.jpg', 'active', 12, NOW());

