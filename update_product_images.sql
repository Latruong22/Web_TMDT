-- Cập nhật đường dẫn hình ảnh cho các sản phẩm

-- Product ID 9: Lib Tech Men's Son of Birdman Snowboard
UPDATE products 
SET image = 'Images/product/Sp1/fw25-lib-25sn032-son-of-birdman.jpg'
WHERE product_id = 9;

-- Product ID 10: Oakley Flow M Matte Black Goggle  
UPDATE products 
SET image = 'Images/product/Sp6/fw2526_oakley_flowmmatteblackgoggle_matteblackprizmsnowsapphireiridium_1.jpg'
WHERE product_id = 10;

-- Kiểm tra kết quả
SELECT product_id, name, image FROM products;
