-- Update category_id để test banner system

-- Sản phẩm Ván trượt (Snowboards) → Category 1
UPDATE products SET category_id = 1 WHERE product_id IN (1, 4);

-- Sản phẩm Giày (Boots) → Category 2  
UPDATE products SET category_id = 2 WHERE product_id IN (2, 5);

-- Sản phẩm Phụ kiện (Goggles/Accessories) → Category 3
UPDATE products SET category_id = 3 WHERE product_id IN (3, 6, 7);

-- Verify
SELECT product_id, name, category_id FROM products ORDER BY product_id;
